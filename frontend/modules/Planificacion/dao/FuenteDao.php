<?php

namespace app\modules\Planificacion\dao;

use app\modules\Planificacion\models\Fuente;
use common\models\Estado;

class FuenteDao
{
    public static function enUso(Fuente $modelo): bool
    {
        return $modelo->getPartidas()
                ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
                ->exists()
            || $modelo->getItemCatalogados()
                ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
                ->exists()
            || $modelo->getItemDescatalogados()
                ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
                ->exists();
    }
}
