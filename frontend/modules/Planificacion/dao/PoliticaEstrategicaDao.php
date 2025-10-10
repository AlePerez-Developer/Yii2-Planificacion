<?php
namespace app\modules\Planificacion\dao;

use app\modules\Planificacion\models\PoliticaEstrategica;

class PoliticaEstrategicaDao
{
    static function enUso(PoliticaEstrategica $model): bool
    {
        return $model->getObjetivoEstrategico()->exists();
    }

    static function verificarCodigoPolitica(int $codigoArea, int $codigoPolitica): bool
    {
        return PoliticaEstrategica::find()->where(['CodigoAreaEstrategica' => $codigoArea, 'CodigoPoliticaEstrategica' => $codigoPolitica])->exists();
    }
}
