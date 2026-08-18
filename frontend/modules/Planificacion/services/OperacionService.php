<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\dao\OperacionDao;
use app\modules\Planificacion\formModels\OperacionForm;
use app\modules\Planificacion\models\Indicador;
use app\modules\Planificacion\models\IndicadorEstrategico;
use app\modules\Planificacion\models\IndicadorPoa;
use app\modules\Planificacion\models\LlavePresupuestaria;
use app\modules\Planificacion\models\ObjetivoEspecifico;
use app\modules\Planificacion\models\Operacion;
use app\modules\Planificacion\models\ProgramacionIndicadorGestion;
use app\modules\Planificacion\models\ProgramacionIndicadorPoaGestion;
use common\models\Estado;
use Yii;

class OperacionService
{
    private const CAMPOS_TRIMESTRE = [
        1 => 'PrimerTrimestre',
        2 => 'SegundoTrimestre',
        3 => 'TercerTrimestre',
        4 => 'CuartoTrimestre',
    ];

    public function listarTodo(
        string $idUnidadEjecutora,
        string $idGestion,
        int $idEstadoPoa
    ): array {
        $data = Operacion::listAll($idUnidadEjecutora, $idGestion, $idEstadoPoa)
            ->orderBy(['O.Codigo' => SORT_ASC])
            ->asArray()
            ->all();

        return ResponseHelper::success($data, 'Operaciones POA obtenidas.');
    }

    public function listarIndicadoresProgramados(
        string $idUnidadEjecutora,
        string $idGestion
    ): array {
        $poa = ProgramacionIndicadorPoaGestion::find()->alias('PG')
            ->select([
                'id' => 'P.IdIndicador',
                'codigo' => 'P.Codigo',
                'text' => 'I.Descripcion',
            ])
            ->innerJoin(['LP' => LlavePresupuestaria::tableName()], 'LP.IdLlavePresupuestaria = PG.IdLlavePresupuestaria')
            ->innerJoin(['P' => IndicadorPoa::tableName()], 'P.IdIndicador = PG.IdIndicadorPoa')
            ->innerJoin(['I' => Indicador::tableName()], 'I.IdIndicador = P.IdIndicador')
            ->where([
                'PG.IdGestion' => $idGestion,
                'LP.IdUnidadEjecutora' => $idUnidadEjecutora,
                'LP.CodigoEstado' => Estado::ESTADO_VIGENTE,
                'P.CodigoEstado' => Estado::ESTADO_VIGENTE,
                'I.CodigoEstado' => Estado::ESTADO_VIGENTE,
            ])
            ->groupBy(['P.IdIndicador', 'P.Codigo', 'I.Descripcion'])
            ->asArray()
            ->all();

        $estrategicos = ProgramacionIndicadorGestion::find()->alias('PG')
            ->select([
                'id' => 'IE.IdIndicador',
                'codigo' => 'IE.Codigo',
                'text' => 'I.Descripcion',
            ])
            ->innerJoin(['LP' => LlavePresupuestaria::tableName()], 'LP.IdLlavePresupuestaria = PG.IdLlavePresupuestaria')
            ->innerJoin(['IE' => IndicadorEstrategico::tableName()], 'IE.IdIndicador = PG.IdIndicadorEstrategico')
            ->innerJoin(['I' => Indicador::tableName()], 'I.IdIndicador = IE.IdIndicador')
            ->where([
                'PG.IdGestion' => $idGestion,
                'LP.IdUnidadEjecutora' => $idUnidadEjecutora,
                'LP.CodigoEstado' => Estado::ESTADO_VIGENTE,
                'IE.CodigoEstado' => Estado::ESTADO_VIGENTE,
                'I.CodigoEstado' => Estado::ESTADO_VIGENTE,
            ])
            ->groupBy(['IE.IdIndicador', 'IE.Codigo', 'I.Descripcion'])
            ->asArray()
            ->all();

        $data = [];
        foreach ($poa as $indicador) {
            $indicador['tipoIndicador'] = 'POA';
            $data[$indicador['id']] = $indicador;
        }
        foreach ($estrategicos as $indicador) {
            $indicador['tipoIndicador'] = 'Estratégico';
            $data[$indicador['id']] = $indicador;
        }

        $data = array_values($data);
        usort($data, static fn(array $a, array $b): int => [
            $a['tipoIndicador'],
            (int)$a['codigo'],
        ] <=> [
            $b['tipoIndicador'],
            (int)$b['codigo'],
        ]);

        return ResponseHelper::success($data);
    }

    public function listarLlaves(string $idUnidadEjecutora): array
    {
        $data = LlavePresupuestaria::find()->alias('LP')
            ->select([
                'id' => 'LP.IdLlavePresupuestaria',
                'text' => 'LP.Llave',
                'descripcion' => 'LP.Descripcion',
            ])
            ->where([
                'LP.IdUnidadEjecutora' => $idUnidadEjecutora,
                'LP.CodigoEstado' => Estado::ESTADO_VIGENTE,
            ])
            ->orderBy(['LP.Llave' => SORT_ASC])
            ->asArray()
            ->all();

        return ResponseHelper::success($data);
    }

    public function guardar(
        OperacionForm $form,
        string $idUnidadEjecutora,
        string $idGestion,
        int $idEstadoPoa
    ): array {
        $this->validarRelaciones($form, $idUnidadEjecutora, $idGestion);

        $modelo = new Operacion([
            'Codigo' => $form->codigo,
            'IdObjEspecifico' => $form->idObjEspecifico,
            'IdUnidadEjecutora' => $idUnidadEjecutora,
            'IdGestion' => $idGestion,
            'IdIndicador' => $form->idIndicador,
            'IdLlavePresupuestaria' => $form->idLlavePresupuestaria,
            'Descripcion' => $this->normalizarDescripcion($form->descripcion),
            'PrimerTrimestre' => 0,
            'SegundoTrimestre' => 0,
            'TercerTrimestre' => 0,
            'CuartoTrimestre' => 0,
            'TipoOperacion' => $form->tipoOperacion,
            'IdEstadoPoa' => $idEstadoPoa,
            'CodigoEstado' => Estado::ESTADO_VIGENTE,
            'CodigoUsuario' => Yii::$app->user->identity->CodigoUsuario,
        ]);

        return $this->procesar($modelo);
    }

    public function actualizar(
        string $id,
        OperacionForm $form,
        string $idUnidadEjecutora,
        string $idGestion,
        int $idEstadoPoa
    ): array {
        $modelo = $this->obtenerModeloValidado($id, $idUnidadEjecutora, $idGestion, $idEstadoPoa);
        $this->validarRelaciones($form, $idUnidadEjecutora, $idGestion);

        $modelo->setAttributes([
            'Codigo' => $form->codigo,
            'IdObjEspecifico' => $form->idObjEspecifico,
            'IdIndicador' => $form->idIndicador,
            'IdGestion' => $idGestion,
            'IdLlavePresupuestaria' => $form->idLlavePresupuestaria,
            'Descripcion' => $this->normalizarDescripcion($form->descripcion),
            'TipoOperacion' => $form->tipoOperacion,
            'CodigoUsuario' => Yii::$app->user->identity->CodigoUsuario,
        ]);

        return $this->procesar($modelo);
    }

    public function guardarMetaTrimestral(
        string $id,
        int $trimestre,
        int $meta,
        string $idUnidadEjecutora,
        string $idGestion,
        int $idEstadoPoa
    ): array {
        if (!isset(self::CAMPOS_TRIMESTRE[$trimestre]) || $meta < 0) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'El trimestre o la meta no son válidos.',
                400
            );
        }

        $modelo = $this->obtenerModeloValidado($id, $idUnidadEjecutora, $idGestion, $idEstadoPoa);
        $campo = self::CAMPOS_TRIMESTRE[$trimestre];
        $metas = [
            1 => (int)$modelo->PrimerTrimestre,
            2 => (int)$modelo->SegundoTrimestre,
            3 => (int)$modelo->TercerTrimestre,
            4 => (int)$modelo->CuartoTrimestre,
        ];
        $metas[$trimestre] = $meta;

        if (array_sum($metas) > 100) {
            throw new ValidationException(
                Yii::$app->params['ERROR_VALIDACION_MODELO'],
                'La programación trimestral acumulada no puede superar 100.',
                422
            );
        }

        $modelo->$campo = $meta;
        $modelo->CodigoUsuario = Yii::$app->user->identity->CodigoUsuario;
        $this->guardarModelo($modelo);

        return ResponseHelper::success([
            'PrimerTrimestre' => (int)$modelo->PrimerTrimestre,
            'SegundoTrimestre' => (int)$modelo->SegundoTrimestre,
            'TercerTrimestre' => (int)$modelo->TercerTrimestre,
            'CuartoTrimestre' => (int)$modelo->CuartoTrimestre,
            'TotalTrimestral' => (int)$modelo->PrimerTrimestre
                + (int)$modelo->SegundoTrimestre
                + (int)$modelo->TercerTrimestre
                + (int)$modelo->CuartoTrimestre,
        ], 'Programación trimestral actualizada.');
    }

    public function obtenerModelo(
        string $id,
        string $idUnidadEjecutora,
        string $idGestion,
        int $idEstadoPoa
    ): array {
        $modelo = $this->obtenerModeloValidado($id, $idUnidadEjecutora, $idGestion, $idEstadoPoa);

        return ResponseHelper::success([
            'IdOperacion' => $modelo->IdOperacion,
            'Codigo' => $modelo->Codigo,
            'IdObjEspecifico' => $modelo->IdObjEspecifico,
            'IdIndicador' => $modelo->IdIndicador,
            'IdLlavePresupuestaria' => $modelo->IdLlavePresupuestaria,
            'Descripcion' => $modelo->Descripcion,
            'TipoOperacion' => $modelo->TipoOperacion,
        ]);
    }

    public function cambiarEstado(
        string $id,
        string $idUnidadEjecutora,
        string $idGestion,
        int $idEstadoPoa
    ): array {
        $modelo = $this->obtenerModeloValidado($id, $idUnidadEjecutora, $idGestion, $idEstadoPoa);
        $modelo->cambiarEstado();
        return $this->procesar($modelo);
    }

    public function eliminar(
        string $id,
        string $idUnidadEjecutora,
        string $idGestion,
        int $idEstadoPoa
    ): array {
        $modelo = $this->obtenerModeloValidado($id, $idUnidadEjecutora, $idGestion, $idEstadoPoa);
        $modelo->eliminar();
        return $this->procesar($modelo);
    }

    public function verificarCodigo(
        string $id,
        string $idObjEspecifico,
        string $codigo,
        string $idUnidadEjecutora,
        string $idGestion,
        int $idEstadoPoa
    ): bool {
        return OperacionDao::verificarCodigo(
            $id,
            $idObjEspecifico,
            $idUnidadEjecutora,
            $idGestion,
            $idEstadoPoa,
            $codigo
        );
    }

    private function validarRelaciones(
        OperacionForm $form,
        string $idUnidadEjecutora,
        string $idGestion
    ): void {
        $idDa = ObjetivoEspecifico::obtenerIdDaDesdeUnidad($idUnidadEjecutora);
        $objetivoValido = ObjetivoEspecifico::find()
            ->where([
                'IdObjEspecifico' => $form->idObjEspecifico,
                'IdDa' => $idDa,
                'IdGestion' => $idGestion,
            ])
            ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->exists();

        if (!$objetivoValido) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'El objetivo específico no pertenece al contexto activo.',
                400
            );
        }

        $llaveValida = LlavePresupuestaria::find()
            ->where([
                'IdLlavePresupuestaria' => $form->idLlavePresupuestaria,
                'IdUnidadEjecutora' => $idUnidadEjecutora,
                'CodigoEstado' => Estado::ESTADO_VIGENTE,
            ])
            ->exists();

        if (!$llaveValida) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'La llave presupuestaria no pertenece a la unidad ejecutora activa.',
                400
            );
        }

        if (!$this->indicadorEstaProgramado(
            $form->idIndicador,
            $idUnidadEjecutora,
            $idGestion
        )) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'El indicador no tiene programación anual para la unidad ejecutora activa.',
                400
            );
        }
    }

    private function indicadorEstaProgramado(
        string $idIndicador,
        string $idUnidadEjecutora,
        string $idGestion
    ): bool {
        $programadoPoa = ProgramacionIndicadorPoaGestion::find()->alias('PG')
            ->innerJoin(['LP' => LlavePresupuestaria::tableName()], 'LP.IdLlavePresupuestaria = PG.IdLlavePresupuestaria')
            ->where([
                'PG.IdIndicadorPoa' => $idIndicador,
                'PG.IdGestion' => $idGestion,
                'LP.IdUnidadEjecutora' => $idUnidadEjecutora,
            ])
            ->exists();

        if ($programadoPoa) {
            return true;
        }

        return ProgramacionIndicadorGestion::find()->alias('PG')
            ->innerJoin(['LP' => LlavePresupuestaria::tableName()], 'LP.IdLlavePresupuestaria = PG.IdLlavePresupuestaria')
            ->where([
                'PG.IdIndicadorEstrategico' => $idIndicador,
                'PG.IdGestion' => $idGestion,
                'LP.IdUnidadEjecutora' => $idUnidadEjecutora,
            ])
            ->exists();
    }

    private function obtenerModeloValidado(
        string $id,
        string $idUnidadEjecutora,
        string $idGestion,
        int $idEstadoPoa
    ): Operacion {
        $modelo = Operacion::find()
            ->where([
                'IdOperacion' => $id,
                'IdUnidadEjecutora' => $idUnidadEjecutora,
                'IdGestion' => $idGestion,
                'IdEstadoPoa' => $idEstadoPoa,
            ])
            ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->one();

        if ($modelo === null) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],
                'No se encontró la operación solicitada.',
                404
            );
        }

        return $modelo;
    }

    private function procesar(Operacion $modelo): array
    {
        $this->guardarModelo($modelo);
        return ResponseHelper::success($modelo, 'Operación procesada correctamente.');
    }

    private function guardarModelo(Operacion $modelo): void
    {
        if (!$modelo->validate()) {
            throw new ValidationException(
                Yii::$app->params['ERROR_VALIDACION_MODELO'],
                $modelo->getErrors(),
                422
            );
        }

        if (!$modelo->save(false)) {
            throw new ValidationException(
                Yii::$app->params['ERROR_EJECUCION_SQL'],
                $modelo->getErrors(),
                500
            );
        }
    }

    private function normalizarDescripcion(?string $descripcion): ?string
    {
        $descripcion = trim((string)$descripcion);
        return $descripcion === '' ? null : mb_strtoupper($descripcion, 'UTF-8');
    }
}
