<?php

namespace app\modules\Planificacion\dao;

use app\modules\Planificacion\models\Gasto;
use common\models\Estado;

class GastoDao
{
    public static function enUso(Gasto $modelo): bool
    {
        return $modelo->getItemsDescatalogados()
            ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->exists();
    }

    public static function verificarCodigo(string $id, string $codigo): bool
    {
        $existe = Gasto::find()
            ->where([
                'CodigoGasto' => $codigo,
                'CodigoEstado' => Estado::ESTADO_VIGENTE,
            ])
            ->andWhere(['<>', 'IdGasto', $id])
            ->exists();

        return !$existe;
    }
}
