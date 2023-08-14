<?php


namespace app\modules\Planificacion\dao;

use yii\base\BaseObject;
use yii\db\Query;

class IndicadorAperturaDao
{
    /*=====================================================
        Genera un nuevo codigo de Indicador Apertura
    =======================================================*/
    static public function GenerarCodigoIndicadorApertura()
    {
        $consulta = new Query();
        $codigo = $consulta->select('max(CodigoIndicadorApertura) as Codigo')
            ->from('IndicadoresAperturas')
            ->one();
        if ($codigo['Codigo']){
            return  $codigo['Codigo'] + 1;
        } else {
            return 1;
        }
    }
}