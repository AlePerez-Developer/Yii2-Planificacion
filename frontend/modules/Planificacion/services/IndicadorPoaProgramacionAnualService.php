<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\models\IndicadorPoa;
use app\modules\Planificacion\models\LlavePresupuestaria;
use app\modules\Planificacion\models\ProgramacionIndicadorPoaGestion;
use Yii;

class IndicadorPoaProgramacionAnualService
{
    public function listarIndicadores(
        string $idObjEspecifico,
        string $idLlavePresupuestaria,
        int $gestion
    ): array {
        $data = IndicadorPoa::listAll($idLlavePresupuestaria, $gestion)
            ->andWhere(['IP.IdObjEspecifico' => $idObjEspecifico])
            ->orderBy(['IP.Codigo' => SORT_ASC])
            ->asArray()
            ->all();

        return ResponseHelper::success($data, 'Indicadores POA obtenidos.');
    }

    public function listarProgramacion(
        string $idIndicadorPoa,
        string $idGestion,
        string $idLlavePresupuestaria
    ): array {
        $data = LlavePresupuestaria::find()->alias('LP')
            ->select([
                'LP.IdLlavePresupuestaria',
                'LP.Llave AS CodigoCompuesto',
                'LP.Descripcion',
                'ISNULL(PG.IdProgramacionIndicadorPoaGestion, \'\') AS IdProgramacionIndicadorPoaGestion',
                'ISNULL(PG.MetaProgramada, 0) AS MetaProgramada',
            ])
            ->leftJoin(
                ['PG' => ProgramacionIndicadorPoaGestion::tableName()],
                'PG.IdLlavePresupuestaria = LP.IdLlavePresupuestaria
                 AND PG.IdIndicadorPoa = :idIndicadorPoa
                 AND PG.IdGestion = :idGestion',
                [':idIndicadorPoa' => $idIndicadorPoa, ':idGestion' => $idGestion]
            )
            ->where(['LP.IdLlavePresupuestaria' => $idLlavePresupuestaria])
            ->asArray()
            ->all();

        return ResponseHelper::success($data, 'Programación anual obtenida.');
    }

    public function guardarMeta(
        string $idIndicadorPoa,
        string $idGestion,
        string $idLlavePresupuestaria,
        int $meta
    ): array {
        $modelo = ProgramacionIndicadorPoaGestion::findOne([
            'IdIndicadorPoa' => $idIndicadorPoa,
            'IdGestion' => $idGestion,
            'IdLlavePresupuestaria' => $idLlavePresupuestaria,
        ]);

        if ($modelo === null) {
            $modelo = new ProgramacionIndicadorPoaGestion([
                'IdIndicadorPoa' => $idIndicadorPoa,
                'IdGestion' => $idGestion,
                'IdLlavePresupuestaria' => $idLlavePresupuestaria,
                'CodigoUsuario' => Yii::$app->user->identity->CodigoUsuario,
            ]);
        }

        $modelo->MetaProgramada = $meta;

        if (!$modelo->save()) {
            throw new ValidationException(
                Yii::$app->params['ERROR_VALIDACION_MODELO'],
                $modelo->getErrors(),
                422
            );
        }

        return ResponseHelper::success([
            'IdProgramacionIndicadorPoaGestion' => $modelo->IdProgramacionIndicadorPoaGestion,
            'MetaProgramada' => $modelo->MetaProgramada,
        ], 'Meta programada actualizada.');
    }
}
