<?php

namespace app\modules\Planificacion\dao;

use app\modules\Planificacion\models\AccionEstrategica;
use common\models\Estado;

class AccionEstrategicaDao
{
    static function enUso(AccionEstrategica $modelo): bool
    {
        return $modelo->getIndicadoresEstrategicos()->exists();
    }

    static function validarId(string $id): bool
    {
        return AccionEstrategica::find()->where(['IdAccionEstrategica' => $id, 'CodigoEstado' => Estado::ESTADO_VIGENTE])->exists();
    }

}