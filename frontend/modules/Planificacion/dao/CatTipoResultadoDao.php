<?php

namespace app\modules\Planificacion\dao;

use app\modules\Planificacion\models\CatTipoResultado;
use common\models\Estado;

class CatTipoResultadoDao
{
    static function validarId(string $id): bool
    {
        return  CatTipoResultado::find()->where(['IdTipoResultado' => $id, 'CodigoEstado' => Estado::ESTADO_VIGENTE])->exists();
    }
}