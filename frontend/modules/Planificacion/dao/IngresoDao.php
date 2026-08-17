<?php

namespace app\modules\Planificacion\dao;

use app\modules\Planificacion\models\Ingreso;
use common\models\Estado;
use yii\db\Expression;

class IngresoDao
{
    public static function existen(
        string $idUnidadEjecutora,
        string $idGestion,
        string $idEstadoPoa,
        ?string $excluirId = null
    ): bool {
        $query = Ingreso::find()
            ->where([
                'IdUnidadEjecutora' => $idUnidadEjecutora,
                'IdGestion' => $idGestion,
                'IdEstadoPoa' => $idEstadoPoa,
                'CodigoEstado' => Estado::ESTADO_VIGENTE,
            ]);

        if ($excluirId !== null) {
            $query->andWhere(['<>', 'IdIngreso', $excluirId]);
        }

        return $query->exists();
    }

    public static function total(
        string $idUnidadEjecutora,
        string $idGestion,
        string $idEstadoPoa,
        ?string $excluirId = null
    ): float {
        $query = Ingreso::find()
            ->where([
                'IdUnidadEjecutora' => $idUnidadEjecutora,
                'IdGestion' => $idGestion,
                'IdEstadoPoa' => $idEstadoPoa,
                'CodigoEstado' => Estado::ESTADO_VIGENTE,
            ]);

        if ($excluirId !== null) {
            $query->andWhere(['<>', 'IdIngreso', $excluirId]);
        }

        return (float)$query->sum(
            new Expression('CAST(Cantidad AS decimal(18,2)) * CAST(Precio AS decimal(18,2))')
        );
    }
}
