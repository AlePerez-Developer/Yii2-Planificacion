<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\models\IndicadorPoa;
use app\modules\Planificacion\models\LlavePresupuestaria;
use app\modules\Planificacion\models\ProgramacionIndicadorPoaGestion;
use app\modules\Planificacion\models\ProgramacionIndicadorPoaTrimestre;
use Yii;

class IndicadorPoaProgramacionTrimestralService
{
    public function listarIndicadores(
        string $idObjEspecifico,
        string $idLlavePresupuestaria,
        int $gestion,
        string $idGestion
    ): array {
        $data = IndicadorPoa::listAll($idLlavePresupuestaria, $gestion)
            ->andWhere(['IP.IdObjEspecifico' => $idObjEspecifico])
            ->innerJoin(
                ['PG' => ProgramacionIndicadorPoaGestion::tableName()],
                'PG.IdIndicadorPoa = IP.IdIndicadorPoa
                 AND PG.IdGestion = :idGestion
                 AND PG.IdLlavePresupuestaria = :idLlave',
                [':idGestion' => $idGestion, ':idLlave' => $idLlavePresupuestaria]
            )
            ->andWhere(['>', 'PG.MetaProgramada', 0])
            ->orderBy(['IP.Codigo' => SORT_ASC])
            ->asArray()
            ->all();

        return ResponseHelper::success($data, 'Indicadores POA programados obtenidos.');
    }

    public function listarProgramacion(
        string $idIndicadorPoa,
        string $idGestion,
        string $idLlavePresupuestaria
    ): array {
        $data = ProgramacionIndicadorPoaGestion::find()->alias('PG')
            ->select([
                'PG.IdProgramacionIndicadorPoaGestion',
                'PG.IdLlavePresupuestaria',
                'LP.Llave AS CodigoCompuesto',
                'LP.Descripcion',
                'PG.MetaProgramada',
                'ISNULL(PT.MetaPrimerTrimestre, 0) AS MetaPrimerTrimestre',
                'ISNULL(PT.MetaSegundoTrimestre, 0) AS MetaSegundoTrimestre',
                'ISNULL(PT.MetaTercerTrimestre, 0) AS MetaTercerTrimestre',
                'ISNULL(PT.MetaCuartoTrimestre, 0) AS MetaCuartoTrimestre',
                '(ISNULL(PT.MetaPrimerTrimestre, 0) + ISNULL(PT.MetaSegundoTrimestre, 0)
                  + ISNULL(PT.MetaTercerTrimestre, 0) + ISNULL(PT.MetaCuartoTrimestre, 0)) AS TotalTrimestral',
                'CASE WHEN
                    (ISNULL(PT.MetaPrimerTrimestre, 0) + ISNULL(PT.MetaSegundoTrimestre, 0)
                    + ISNULL(PT.MetaTercerTrimestre, 0) + ISNULL(PT.MetaCuartoTrimestre, 0))
                    >= PG.MetaProgramada THEN 1 ELSE 0 END AS ProgramacionCompleta',
            ])
            ->innerJoin(['LP' => LlavePresupuestaria::tableName()], 'LP.IdLlavePresupuestaria = PG.IdLlavePresupuestaria')
            ->leftJoin(
                ['PT' => ProgramacionIndicadorPoaTrimestre::tableName()],
                'PT.IdProgramacionIndicadorPoaGestion = PG.IdProgramacionIndicadorPoaGestion'
            )
            ->where([
                'PG.IdIndicadorPoa' => $idIndicadorPoa,
                'PG.IdGestion' => $idGestion,
                'PG.IdLlavePresupuestaria' => $idLlavePresupuestaria,
            ])
            ->asArray()
            ->all();

        return ResponseHelper::success($data, 'Programación trimestral obtenida.');
    }

    public function guardarMeta(string $idProgramacion, int $trimestre, int $meta): array
    {
        $programacion = ProgramacionIndicadorPoaGestion::findOne($idProgramacion);

        if ($programacion === null) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],
                'No se encontró la programación anual.',
                404
            );
        }

        $modelo = ProgramacionIndicadorPoaTrimestre::findOne([
            'IdProgramacionIndicadorPoaGestion' => $idProgramacion,
        ]);

        if ($modelo === null) {
            $modelo = new ProgramacionIndicadorPoaTrimestre([
                'IdProgramacionIndicadorPoaGestion' => $idProgramacion,
                'CodigoUsuario' => Yii::$app->user->identity->CodigoUsuario,
            ]);
        }

        $campos = [
            1 => 'MetaPrimerTrimestre',
            2 => 'MetaSegundoTrimestre',
            3 => 'MetaTercerTrimestre',
            4 => 'MetaCuartoTrimestre',
        ];

        if (!isset($campos[$trimestre])) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                ['trimestre' => ['El trimestre seleccionado no es válido.']],
                400
            );
        }

        $modelo->{$campos[$trimestre]} = $meta;

        if (!$modelo->save()) {
            throw new ValidationException(
                Yii::$app->params['ERROR_VALIDACION_MODELO'],
                $modelo->getErrors(),
                422
            );
        }

        $total = (int)$modelo->MetaPrimerTrimestre
            + (int)$modelo->MetaSegundoTrimestre
            + (int)$modelo->MetaTercerTrimestre
            + (int)$modelo->MetaCuartoTrimestre;

        return ResponseHelper::success([
            'MetaPrimerTrimestre' => (int)$modelo->MetaPrimerTrimestre,
            'MetaSegundoTrimestre' => (int)$modelo->MetaSegundoTrimestre,
            'MetaTercerTrimestre' => (int)$modelo->MetaTercerTrimestre,
            'MetaCuartoTrimestre' => (int)$modelo->MetaCuartoTrimestre,
            'TotalTrimestral' => $total,
            'ProgramacionCompleta' => $total >= (int)$programacion->MetaProgramada ? 1 : 0,
        ], 'Meta trimestral actualizada.');
    }
}
