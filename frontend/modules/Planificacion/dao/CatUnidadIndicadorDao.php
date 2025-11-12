<?php

namespace app\modules\Planificacion\dao;

use app\modules\Planificacion\models\CatUnidadIndicador;
use common\models\Estado;

class CatUnidadIndicadorDao
{
    static function validarId(string $id): bool
    {
        return CatUnidadIndicador::find()->where(['IdUnidadIndicador'=> $id, 'CodigoEstado' => Estado::ESTADO_VIGENTE])->exists();
    }

}