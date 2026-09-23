<?php

namespace app\modules\Planificacion\dao;

use app\modules\Planificacion\models\ItemGestion;
use app\modules\Planificacion\models\ItemGestionOperacion;
use app\modules\Planificacion\models\Operacion;
use common\models\Estado;
use yii\db\Expression;

class PresupuestoConsumoDao
{
    public static function totalPorLlave(
        string $idLlavePresupuestaria,
        string $idGestion,
        string $idEstadoPoa,
        ?string $excluirItem = null,
        ?string $excluirDescatalogado = null
    ): float {
        $query = ItemGestionOperacion::find()->alias('IGO')
            ->innerJoin(['IG' => ItemGestion::tableName()], 'IG.IdItem_Gestion = IGO.IdItem_Gestion')
            ->innerJoin(['O' => Operacion::tableName()], 'O.IdOperacion = IGO.IdOperacion')
            ->where([
                'O.IdLlavePresupuestaria' => $idLlavePresupuestaria,
                'IG.IdGestion' => $idGestion,
                'IGO.IdEstadoPoa' => $idEstadoPoa,
                'IGO.CodigoEstado' => Estado::ESTADO_VIGENTE,
                'IG.CodigoEstado' => Estado::ESTADO_VIGENTE,
                'O.CodigoEstado' => Estado::ESTADO_VIGENTE,
            ]);

        if ($excluirItem) {
            $query->andWhere(['<>', 'IG.IdItem', $excluirItem]);
        }

        return (float)$query->sum(new Expression(
            'CAST(IGO.Cantidad AS decimal(18,2)) * CAST(IG.PrecioUnitario AS decimal(18,2))'
        ));
    }

    public static function totalPorUnidad(
        string $idUnidadEjecutora,
        string $idGestion,
        string $idEstadoPoa
    ): float {
        return (float)ItemGestionOperacion::find()->alias('IGO')
            ->innerJoin(['IG' => ItemGestion::tableName()], 'IG.IdItem_Gestion = IGO.IdItem_Gestion')
            ->innerJoin(['O' => Operacion::tableName()], 'O.IdOperacion = IGO.IdOperacion')
            ->where([
                'O.IdUnidadEjecutora' => $idUnidadEjecutora,
                'IG.IdGestion' => $idGestion,
                'IGO.IdEstadoPoa' => $idEstadoPoa,
                'IGO.CodigoEstado' => Estado::ESTADO_VIGENTE,
                'IG.CodigoEstado' => Estado::ESTADO_VIGENTE,
                'O.CodigoEstado' => Estado::ESTADO_VIGENTE,
            ])
            ->sum(new Expression(
                'CAST(IGO.Cantidad AS decimal(18,2)) * CAST(IG.PrecioUnitario AS decimal(18,2))'
            ));
    }

    public static function totalPorDa(
        string $idDa,
        string $idGestion,
        string $idEstadoPoa
    ): float {
        return (float)ItemGestionOperacion::find()->alias('IGO')
            ->innerJoin(['IG' => ItemGestion::tableName()], 'IG.IdItem_Gestion = IGO.IdItem_Gestion')
            ->innerJoin(['O' => Operacion::tableName()], 'O.IdOperacion = IGO.IdOperacion')
            ->innerJoin(
                ['UE' => \app\modules\Planificacion\models\UnidadEjecutora::tableName()],
                'UE.IdUnidadEjecutora = O.IdUnidadEjecutora'
            )
            ->where([
                'UE.IdDa' => $idDa,
                'IG.IdGestion' => $idGestion,
                'IGO.IdEstadoPoa' => $idEstadoPoa,
                'IGO.CodigoEstado' => Estado::ESTADO_VIGENTE,
                'IG.CodigoEstado' => Estado::ESTADO_VIGENTE,
                'O.CodigoEstado' => Estado::ESTADO_VIGENTE,
                'UE.CodigoEstado' => Estado::ESTADO_VIGENTE,
            ])
            ->sum(new Expression(
                'CAST(IGO.Cantidad AS decimal(18,2)) * CAST(IG.PrecioUnitario AS decimal(18,2))'
            ));
    }
}
