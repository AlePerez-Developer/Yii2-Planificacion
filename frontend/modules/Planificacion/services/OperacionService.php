<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\PoaEdicionHelper;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\dao\OperacionDao;
use app\modules\Planificacion\formModels\OperacionForm;
use app\modules\Planificacion\models\Indicador;
use app\modules\Planificacion\models\IndicadorEstrategico;
use app\modules\Planificacion\models\IndicadorPoa;
use app\modules\Planificacion\models\LlavePresupuestaria;
use app\modules\Planificacion\models\ObjetivoEspecifico;
use app\modules\Planificacion\models\Operacion;
use app\modules\Planificacion\models\Actividad;
use app\modules\Planificacion\models\Da;
use app\modules\Planificacion\models\Programa;
use app\modules\Planificacion\models\ProgramacionIndicadorGestion;
use app\modules\Planificacion\models\ProgramacionIndicadorPoaGestion;
use app\modules\Planificacion\models\ProgramacionIndicadorPoaTrimestre;
use app\modules\Planificacion\models\ProgramacionIndicadorTrimestre;
use app\modules\Planificacion\models\Proyecto;
use app\modules\Planificacion\models\UnidadEjecutora;
use common\models\Estado;
use Yii;
use yii\db\Expression;

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
        string $idEstadoPoa
    ): array {
        $data = Operacion::listAll($idUnidadEjecutora, $idGestion, $idEstadoPoa)
            ->orderBy(['LP.Llave' => SORT_ASC, 'O.Codigo' => SORT_ASC])
            ->asArray()
            ->all();

        return ResponseHelper::success($data, 'Operaciones POA obtenidas.');
    }

    public function listarIndicadoresProgramados(
        string $idUnidadEjecutora,
        string $idGestion,
        string $idEstadoPoa,
        string $idLlavePresupuestaria,
        string $tipoIndicador
    ): array {
        if ($idLlavePresupuestaria === '') {
            return ResponseHelper::success([]);
        }

        $this->asegurarLlaveDeUnidad($idLlavePresupuestaria, $idUnidadEjecutora);

        $usados = $this->indicadoresUsados(
            $idUnidadEjecutora,
            $idGestion,
            $idEstadoPoa
        );

        $data = $tipoIndicador === 'poa'
            ? $this->indicadoresPoaProgramados($idLlavePresupuestaria, $idGestion)
            : $this->indicadoresEstrategicosProgramados($idLlavePresupuestaria, $idGestion);

        foreach ($data as &$indicador) {
            $indicador['usado'] = in_array($indicador['id'], $usados, true) ? 1 : 0;
            $indicador['tipoIndicador'] = $tipoIndicador === 'poa' ? 'POA' : 'Estratégico';
        }
        unset($indicador);

        usort($data, static fn(array $a, array $b): int => [
            (int)$a['usado'],
            (int)$a['codigo'],
        ] <=> [
            (int)$b['usado'],
            (int)$b['codigo'],
        ]);

        return ResponseHelper::success($data);
    }

    public function listarLlaves(string $idUnidadEjecutora, string $idGestion): array
    {
        $idsPoa = ProgramacionIndicadorPoaGestion::find()->alias('PG')
            ->select('PG.IdLlavePresupuestaria')
            ->innerJoin(['LP' => LlavePresupuestaria::tableName()], 'LP.IdLlavePresupuestaria = PG.IdLlavePresupuestaria')
            ->where([
                'PG.IdGestion' => $idGestion,
                'LP.IdUnidadEjecutora' => $idUnidadEjecutora,
                'LP.CodigoEstado' => Estado::ESTADO_VIGENTE,
            ])
            ->column();

        $idsEstrategicos = ProgramacionIndicadorGestion::find()->alias('PG')
            ->select('PG.IdLlavePresupuestaria')
            ->innerJoin(['LP' => LlavePresupuestaria::tableName()], 'LP.IdLlavePresupuestaria = PG.IdLlavePresupuestaria')
            ->where([
                'PG.IdGestion' => $idGestion,
                'LP.IdUnidadEjecutora' => $idUnidadEjecutora,
                'LP.CodigoEstado' => Estado::ESTADO_VIGENTE,
            ])
            ->column();

        $ids = array_values(array_unique(array_merge($idsPoa, $idsEstrategicos)));
        if ($ids === []) {
            return ResponseHelper::success([]);
        }

        $data = LlavePresupuestaria::find()->alias('LP')
            ->select([
                'id' => 'LP.IdLlavePresupuestaria',
                'text' => 'LP.Llave',
                'unidad' => new Expression("CONCAT(Da.Da, '-', Un.Ue, ' ', Un.Descripcion)"),
                'programa' => new Expression("CONCAT(Pr.Codigo, ' - ', Pr.Descripcion)"),
                'proyecto' => new Expression("CONCAT(Py.Codigo, ' - ', Py.Descripcion)"),
                'actividad' => new Expression("CONCAT(Ac.Codigo, ' - ', Ac.Descripcion)"),
            ])
            ->innerJoin(['Un' => UnidadEjecutora::tableName()], 'Un.IdUnidadEjecutora = LP.IdUnidadEjecutora')
            ->innerJoin(['Da' => Da::tableName()], 'Da.IdDa = Un.IdDa')
            ->innerJoin(['Py' => Proyecto::tableName()], 'Py.IdProyecto = LP.IdProyecto')
            ->innerJoin(['Pr' => Programa::tableName()], 'Pr.IdPrograma = Py.IdPrograma')
            ->innerJoin(['Ac' => Actividad::tableName()], 'Ac.IdActividad = LP.IdActividad')
            ->where([
                'LP.IdLlavePresupuestaria' => $ids,
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
        string $idEstadoPoa
    ): array {
        PoaEdicionHelper::asegurarEdicion($idUnidadEjecutora);
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
        string $idEstadoPoa
    ): array {
        PoaEdicionHelper::asegurarEdicion($idUnidadEjecutora);
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
        string $idEstadoPoa
    ): array {
        PoaEdicionHelper::asegurarEdicion($idUnidadEjecutora);
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
        string $idEstadoPoa
    ): array {
        $modelo = $this->obtenerModeloValidado($id, $idUnidadEjecutora, $idGestion, $idEstadoPoa);

        $tipoIndicador = IndicadorPoa::find()
            ->where(['IdIndicador' => $modelo->IdIndicador])
            ->exists()
            ? 'poa'
            : 'estrategico';

        return ResponseHelper::success([
            'IdOperacion' => $modelo->IdOperacion,
            'Codigo' => $modelo->Codigo,
            'IdObjEspecifico' => $modelo->IdObjEspecifico,
            'IdIndicador' => $modelo->IdIndicador,
            'tipoIndicador' => $tipoIndicador,
            'IdLlavePresupuestaria' => $modelo->IdLlavePresupuestaria,
            'Descripcion' => $modelo->Descripcion,
            'TipoOperacion' => $modelo->TipoOperacion,
        ]);
    }

    public function cambiarEstado(
        string $id,
        string $idUnidadEjecutora,
        string $idGestion,
        string $idEstadoPoa
    ): array {
        PoaEdicionHelper::asegurarEdicion($idUnidadEjecutora);
        $modelo = $this->obtenerModeloValidado($id, $idUnidadEjecutora, $idGestion, $idEstadoPoa);
        $modelo->cambiarEstado();
        return $this->procesar($modelo);
    }

    public function eliminar(
        string $id,
        string $idUnidadEjecutora,
        string $idGestion,
        string $idEstadoPoa
    ): array {
        PoaEdicionHelper::asegurarEdicion($idUnidadEjecutora);
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
        string $idEstadoPoa
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

        $this->asegurarLlaveDeUnidad($form->idLlavePresupuestaria, $idUnidadEjecutora);

        if (!$this->indicadorEstaProgramado(
            $form->idIndicador,
            $form->idLlavePresupuestaria,
            $idGestion
        )) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'El indicador no tiene programación anual para la llave presupuestaria seleccionada.',
                400
            );
        }
    }

    private function indicadoresEstrategicosProgramados(
        string $idLlavePresupuestaria,
        string $idGestion
    ): array {
        return ProgramacionIndicadorGestion::find()->alias('PG')
            ->select([
                'id' => 'IE.IdIndicador',
                'codigo' => 'IE.Codigo',
                'text' => 'I.Descripcion',
                'meta' => 'PG.MetaProgramada',
                't1' => new Expression('ISNULL(PT.MetaPrimerTrimestre, 0)'),
                't2' => new Expression('ISNULL(PT.MetaSegundoTrimestre, 0)'),
                't3' => new Expression('ISNULL(PT.MetaTercerTrimestre, 0)'),
                't4' => new Expression('ISNULL(PT.MetaCuartoTrimestre, 0)'),
            ])
            ->innerJoin(['IE' => IndicadorEstrategico::tableName()], 'IE.IdIndicador = PG.IdIndicadorEstrategico')
            ->innerJoin(['I' => Indicador::tableName()], 'I.IdIndicador = IE.IdIndicador')
            ->leftJoin(
                ['PT' => ProgramacionIndicadorTrimestre::tableName()],
                'PT.IdProgramacionIndicadorGestion = PG.IdProgramacionIndicadorGestion'
            )
            ->where([
                'PG.IdLlavePresupuestaria' => $idLlavePresupuestaria,
                'PG.IdGestion' => $idGestion,
                'IE.CodigoEstado' => Estado::ESTADO_VIGENTE,
                'I.CodigoEstado' => Estado::ESTADO_VIGENTE,
            ])
            ->asArray()
            ->all();
    }

    private function indicadoresPoaProgramados(
        string $idLlavePresupuestaria,
        string $idGestion
    ): array {
        return ProgramacionIndicadorPoaGestion::find()->alias('PG')
            ->select([
                'id' => 'P.IdIndicador',
                'codigo' => 'P.Codigo',
                'text' => 'I.Descripcion',
                'meta' => 'PG.MetaProgramada',
                't1' => new Expression('ISNULL(PT.MetaPrimerTrimestre, 0)'),
                't2' => new Expression('ISNULL(PT.MetaSegundoTrimestre, 0)'),
                't3' => new Expression('ISNULL(PT.MetaTercerTrimestre, 0)'),
                't4' => new Expression('ISNULL(PT.MetaCuartoTrimestre, 0)'),
            ])
            ->innerJoin(['P' => IndicadorPoa::tableName()], 'P.IdIndicador = PG.IdIndicadorPoa')
            ->innerJoin(['I' => Indicador::tableName()], 'I.IdIndicador = P.IdIndicador')
            ->leftJoin(
                ['PT' => ProgramacionIndicadorPoaTrimestre::tableName()],
                'PT.IdProgramacionIndicadorPoaGestion = PG.IdProgramacionIndicadorPoaGestion'
            )
            ->where([
                'PG.IdLlavePresupuestaria' => $idLlavePresupuestaria,
                'PG.IdGestion' => $idGestion,
                'P.CodigoEstado' => Estado::ESTADO_VIGENTE,
                'I.CodigoEstado' => Estado::ESTADO_VIGENTE,
            ])
            ->asArray()
            ->all();
    }

    private function indicadoresUsados(
        string $idUnidadEjecutora,
        string $idGestion,
        string $idEstadoPoa
    ): array {
        return Operacion::find()
            ->select('IdIndicador')
            ->where([
                'IdUnidadEjecutora' => $idUnidadEjecutora,
                'IdGestion' => $idGestion,
                'IdEstadoPoa' => $idEstadoPoa,
            ])
            ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->column();
    }

    private function asegurarLlaveDeUnidad(
        string $idLlavePresupuestaria,
        string $idUnidadEjecutora
    ): void {
        $llaveValida = LlavePresupuestaria::find()
            ->where([
                'IdLlavePresupuestaria' => $idLlavePresupuestaria,
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
    }

    private function indicadorEstaProgramado(
        string $idIndicador,
        string $idLlavePresupuestaria,
        string $idGestion
    ): bool {
        $programadoPoa = ProgramacionIndicadorPoaGestion::find()
            ->where([
                'IdIndicadorPoa' => $idIndicador,
                'IdGestion' => $idGestion,
                'IdLlavePresupuestaria' => $idLlavePresupuestaria,
            ])
            ->exists();

        if ($programadoPoa) {
            return true;
        }

        return ProgramacionIndicadorGestion::find()
            ->where([
                'IdIndicadorEstrategico' => $idIndicador,
                'IdGestion' => $idGestion,
                'IdLlavePresupuestaria' => $idLlavePresupuestaria,
            ])
            ->exists();
    }

    private function obtenerModeloValidado(
        string $id,
        string $idUnidadEjecutora,
        string $idGestion,
        string $idEstadoPoa
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
