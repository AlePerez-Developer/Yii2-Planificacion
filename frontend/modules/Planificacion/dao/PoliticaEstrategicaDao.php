<?php
namespace app\modules\Planificacion\dao;

use app\modules\Planificacion\models\PoliticaEstrategica;

class PoliticaEstrategicaDao
{
    static function enUso(PoliticaEstrategica $model): bool
    {
        return false;
        return $model->getObjetivoEstrategico()->exists();
    }
}
