<?php

namespace app\modules\Planificacion\dao;

use app\modules\Planificacion\models\ItemCatalogado;
use app\modules\Planificacion\models\ItemDescatalogado;
use app\modules\Planificacion\models\Operacion;
use common\models\Estado;
use yii\db\Expression;

class PresupuestoConsumoDao
{
    public static function totalPorLlave(
        string $idLlavePresupuestaria,
        string $idGestion,
        string $idEstadoPoa,
        ?string $excluirCatalogado = null,
        ?string $excluirDescatalogado = null
    ): float {
        $catalogado = ItemCatalogado::find()->alias('IC')
            ->innerJoin(['O' => Operacion::tableName()], 'O.IdOperacion = IC.IdOperacion')
            ->where([
                'O.IdLlavePresupuestaria' => $idLlavePresupuestaria,
                'IC.IdGestion' => $idGestion,
                'IC.IdEstadoPoa' => $idEstadoPoa,
                'IC.CodigoEstado' => Estado::ESTADO_VIGENTE,
            ])
            ->andFilterWhere(['<>', 'IC.IdItemCatalogado', $excluirCatalogado])
            ->sum(new Expression('CAST(IC.cantidad AS decimal(18,2)) * CAST(IC.Precio AS decimal(18,2))'));

        $descatalogado = ItemDescatalogado::find()->alias('ID')
            ->innerJoin(['O' => Operacion::tableName()], 'O.IdOperacion = ID.IdOperacion')
            ->where([
                'O.IdLlavePresupuestaria' => $idLlavePresupuestaria,
                'ID.IdGestion' => $idGestion,
                'ID.IdEstadoPoa' => $idEstadoPoa,
                'ID.CodigoEstado' => Estado::ESTADO_VIGENTE,
            ])
            ->andFilterWhere(['<>', 'ID.IdItemDescatalogado', $excluirDescatalogado])
            ->sum(new Expression('CAST(ID.cantidad AS decimal(18,2)) * CAST(ID.Precio AS decimal(18,2))'));

        return (float)$catalogado + (float)$descatalogado;
    }

    public static function totalPorUnidad(
        string $idUnidadEjecutora,
        string $idGestion,
        string $idEstadoPoa
    ): float {
        $llaves = Operacion::find()
            ->select('IdLlavePresupuestaria')
            ->where(['IdUnidadEjecutora' => $idUnidadEjecutora])
            ->distinct()
            ->column();

        return array_sum(array_map(
            static fn(string $idLlave): float => self::totalPorLlave(
                $idLlave,
                $idGestion,
                $idEstadoPoa
            ),
            $llaves
        ));
    }
}
