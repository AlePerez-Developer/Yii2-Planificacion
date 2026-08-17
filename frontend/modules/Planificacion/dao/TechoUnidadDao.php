<?php

namespace app\modules\Planificacion\dao;

use app\modules\Planificacion\models\LlavePresupuestaria;
use app\modules\Planificacion\models\TechoUnidad;
use common\models\Estado;

class TechoUnidadDao
{
    public static function total(
        string $idUnidadEjecutora,
        string $idGestion,
        ?string $excluirAsignacion = null
    ): int {
        $query = TechoUnidad::find()->alias('TU')
            ->innerJoin(
                ['LP' => LlavePresupuestaria::tableName()],
                'LP.IdLlavePresupuestaria = TU.IdLlavePresupuestaria'
            )
            ->where([
                'TU.IdGestion' => $idGestion,
                'TU.CodigoEstado' => Estado::ESTADO_VIGENTE,
                'LP.IdUnidadEjecutora' => $idUnidadEjecutora,
            ]);

        if ($excluirAsignacion !== null) {
            $query->andWhere(['<>', 'TU.IdAsignacion', $excluirAsignacion]);
        }

        return (int)$query->sum('TU.Techo');
    }
}
