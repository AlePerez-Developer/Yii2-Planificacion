<?php

namespace app\modules\Planificacion\dao;


use app\modules\Planificacion\models\CatCategoriaIndicador;
use common\models\Estado;

class CatCategoriaIndicadorDao
{
    static function validarId(string $id): bool
    {
        return CatCategoriaIndicador::find()->where(['IdCategoriaIndicador'=> $id, 'CodigoEstado' => Estado::ESTADO_VIGENTE])->exists();
    }

}