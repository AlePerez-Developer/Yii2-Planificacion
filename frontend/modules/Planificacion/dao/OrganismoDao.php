<?php

namespace app\modules\Planificacion\dao;

use app\modules\Planificacion\models\Organismo;
use common\models\Estado;

class OrganismoDao
{
    public static function enUso(Organismo $modelo): bool
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
