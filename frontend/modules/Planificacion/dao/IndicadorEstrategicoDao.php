<?php
namespace app\modules\Planificacion\dao;

use yii\db\Query;

class IndicadorEstrategicoDao
{
    /*=====================================================
        Genera un nuevo codigo de Indicador estrategico
    =======================================================*/
    static public function GenerarCodigoIndicadorEstrategico()
    {
        $consulta = new Query();
        $codigo = $consulta->select('max(CodigoIndicador) as Codigo')
            ->from('IndicadoresEstrategicos')
            ->one();
        if ($codigo['Codigo']){
            return  $codigo['Codigo'] + 1;
        } else {
            return 1;
        }
    }
}