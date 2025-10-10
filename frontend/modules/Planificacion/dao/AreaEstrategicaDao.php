<?php
namespace app\modules\Planificacion\dao;

use app\modules\Planificacion\models\AreaEstrategica;

class AreaEstrategicaDao
{
    static function enUso(AreaEstrategica $model): bool
    {
        return $model->getPoliticasEstrategicas()->exists();
    }

    static function validarCodigoArea(int $codigo): bool
    {
        return $existe = AreaEstrategica::find()->where(['CodigoAreaEstrategica' => $codigo])->exists();
    }
}
