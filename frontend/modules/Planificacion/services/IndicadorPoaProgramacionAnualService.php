<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\dao\ProgramacionPoaAnualDao;
use app\modules\Planificacion\formModels\ProgramacionIndicadorPoaGestionForm;
use app\modules\Planificacion\models\Indicador;
use app\modules\Planificacion\models\IndicadorPoa;
use app\modules\Planificacion\models\LlavePresupuestaria;
use app\modules\Planificacion\models\ObjetivoEspecifico;
use app\modules\Planificacion\models\PeiGestion;
use app\modules\Planificacion\models\ProgramacionIndicadorPoaGestion;
use common\models\Estado;
use Yii;

class IndicadorPoaProgramacionAnualService
{
    public function listarRelaciones(
        string $idObjEspecifico,
        string $idUnidadEjecutora,
        string $idGestion
    ): array {
        $this->validarObjetivo($idObjEspecifico, $idUnidadEjecutora, $idGestion);

        $data = ProgramacionIndicadorPoaGestion::find()->alias('PG')
            ->select([
                'PG.IdProgramacionIndicadorPoaGestion',
                'PG.IdIndicadorPoa',
                'PG.IdObjEspecifico',
                'PG.IdLlavePresupuestaria',
                'PG.IdGestion',
                'PG.MetaProgramada',
                'Gestion' => 'G.Gestion',
                'Llave' => 'LP.Llave',
                'LlaveDescripcion' => 'LP.Descripcion',
                'IndicadorCodigo' => 'P.Codigo',
                'IndicadorDescripcion' => 'I.Descripcion',
            ])
            ->innerJoin(['G' => PeiGestion::tableName()], 'G.IdGestion = PG.IdGestion')
            ->innerJoin(['LP' => LlavePresupuestaria::tableName()], 'LP.IdLlavePresupuestaria = PG.IdLlavePresupuestaria')
            ->innerJoin(['P' => IndicadorPoa::tableName()], 'P.IdIndicador = PG.IdIndicadorPoa')
            ->innerJoin(['I' => Indicador::tableName()], 'I.IdIndicador = P.IdIndicador')
            ->where([
                'PG.IdObjEspecifico' => $idObjEspecifico,
                'PG.IdGestion' => $idGestion,
                'LP.IdUnidadEjecutora' => $idUnidadEjecutora,
            ])
            ->orderBy(['LP.Llave' => SORT_ASC, 'P.Codigo' => SORT_ASC])
            ->asArray()
            ->all();

        return ResponseHelper::success($data, 'Programación anual obtenida.');
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

    public function listarIndicadores(string $idGestion): array
    {
        $data = IndicadorPoa::find()->alias('P')
            ->select([
                'id' => 'P.IdIndicador',
                'codigo' => 'P.Codigo',
                'text' => 'I.Descripcion',
                'meta' => 'I.Meta',
            ])
            ->innerJoin(['I' => Indicador::tableName()], 'I.IdIndicador = P.IdIndicador')
            ->where([
                'P.IdGestion' => $idGestion,
                'P.CodigoEstado' => Estado::ESTADO_VIGENTE,
                'I.CodigoEstado' => Estado::ESTADO_VIGENTE,
            ])
            ->orderBy(['P.Codigo' => SORT_ASC])
            ->asArray()
            ->all();

        return ResponseHelper::success($data);
    }

    public function guardar(
        ProgramacionIndicadorPoaGestionForm $form,
        string $idUnidadEjecutora,
        string $idGestion
    ): array {
        $this->validarObjetivo($form->idObjEspecifico, $idUnidadEjecutora, $idGestion);
        $this->validarLlave($form->idLlavePresupuestaria, $idUnidadEjecutora);
        $this->validarIndicador($form->idIndicadorPoa, $idGestion);

        $existe = ProgramacionIndicadorPoaGestion::find()
            ->where([
                'IdIndicadorPoa' => $form->idIndicadorPoa,
                'IdObjEspecifico' => $form->idObjEspecifico,
                'IdLlavePresupuestaria' => $form->idLlavePresupuestaria,
                'IdGestion' => $idGestion,
            ])
            ->exists();

        if ($existe) {
            throw new ValidationException(
                Yii::$app->params['ERROR_VALIDACION_MODELO'],
                'La relación seleccionada ya se encuentra programada.',
                409
            );
        }

        $modelo = new ProgramacionIndicadorPoaGestion([
            'IdIndicadorPoa' => $form->idIndicadorPoa,
            'IdObjEspecifico' => $form->idObjEspecifico,
            'IdLlavePresupuestaria' => $form->idLlavePresupuestaria,
            'IdGestion' => $idGestion,
            'MetaProgramada' => $form->metaProgramada,
            'CodigoUsuario' => Yii::$app->user->identity->CodigoUsuario,
        ]);
        $this->guardarModelo($modelo);

        return ResponseHelper::success(null, 'Relación agregada correctamente.');
    }

    public function actualizarMeta(
        string $idProgramacion,
        string $idObjEspecifico,
        int $metaProgramada,
        string $idUnidadEjecutora,
        string $idGestion
    ): array {
        $this->validarObjetivo($idObjEspecifico, $idUnidadEjecutora, $idGestion);
        $modelo = $this->obtenerProgramacionValidada(
            $idProgramacion,
            $idObjEspecifico,
            $idUnidadEjecutora,
            $idGestion
        );

        $modelo->MetaProgramada = $metaProgramada;
        $modelo->CodigoUsuario = Yii::$app->user->identity->CodigoUsuario;
        $this->guardarModelo($modelo);

        return ResponseHelper::success([
            'MetaProgramada' => (int)$modelo->MetaProgramada,
        ], 'Meta programada actualizada.');
    }

    public function eliminar(
        string $idProgramacion,
        string $idObjEspecifico,
        string $idUnidadEjecutora,
        string $idGestion
    ): array {
        $this->validarObjetivo($idObjEspecifico, $idUnidadEjecutora, $idGestion);
        $modelo = $this->obtenerProgramacionValidada(
            $idProgramacion,
            $idObjEspecifico,
            $idUnidadEjecutora,
            $idGestion
        );

        if (ProgramacionPoaAnualDao::enUso($modelo)) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_EN_USO'],
                'La programación tiene metas trimestrales registradas.',
                409
            );
        }

        if ($modelo->delete() === false) {
            throw new ValidationException(
                Yii::$app->params['ERROR_EJECUCION_SQL'],
                $modelo->getErrors(),
                500
            );
        }

        return ResponseHelper::success(null, 'Programación eliminada correctamente.');
    }

    private function obtenerProgramacionValidada(
        string $idProgramacion,
        string $idObjEspecifico,
        string $idUnidadEjecutora,
        string $idGestion
    ): ProgramacionIndicadorPoaGestion {
        $modelo = ProgramacionIndicadorPoaGestion::find()->alias('PG')
            ->innerJoin(['LP' => LlavePresupuestaria::tableName()], 'LP.IdLlavePresupuestaria = PG.IdLlavePresupuestaria')
            ->where([
                'PG.IdProgramacionIndicadorPoaGestion' => $idProgramacion,
                'PG.IdObjEspecifico' => $idObjEspecifico,
                'PG.IdGestion' => $idGestion,
                'LP.IdUnidadEjecutora' => $idUnidadEjecutora,
            ])
            ->one();

        if ($modelo === null) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],
                'No se encontró la programación solicitada.',
                404
            );
        }

        return $modelo;
    }

    private function validarObjetivo(
        string $idObjEspecifico,
        string $idUnidadEjecutora,
        string $idGestion
    ): void {
        $idDa = ObjetivoEspecifico::obtenerIdDaDesdeUnidad($idUnidadEjecutora);
        $valido = ObjetivoEspecifico::find()
            ->where([
                'IdObjEspecifico' => $idObjEspecifico,
                'IdDa' => $idDa,
                'IdGestion' => $idGestion,
            ])
            ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->exists();

        if (!$valido) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'El objetivo específico no pertenece al contexto activo.',
                400
            );
        }
    }

    private function validarLlave(string $idLlave, string $idUnidadEjecutora): void
    {
        $valida = LlavePresupuestaria::find()
            ->where([
                'IdLlavePresupuestaria' => $idLlave,
                'IdUnidadEjecutora' => $idUnidadEjecutora,
                'CodigoEstado' => Estado::ESTADO_VIGENTE,
            ])
            ->exists();

        if (!$valida) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'La llave presupuestaria no pertenece a la unidad ejecutora activa.',
                400
            );
        }
    }

    private function validarIndicador(string $idIndicador, string $idGestion): void
    {
        $valido = IndicadorPoa::find()
            ->where([
                'IdIndicador' => $idIndicador,
                'IdGestion' => $idGestion,
                'CodigoEstado' => Estado::ESTADO_VIGENTE,
            ])
            ->exists();

        if (!$valido) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'El indicador POA no pertenece a la gestión activa.',
                400
            );
        }
    }

    private function guardarModelo(ProgramacionIndicadorPoaGestion $modelo): void
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
}
