<?php
namespace app\modules\Planificacion\dao;

use yii\db\Query;

class IndicadorDao
{
    /*=====================================================
             Genera un nuevo codigo de Indicador
    =======================================================*/
    static public function GenerarCodigoIndicador()
    {
        $consulta = new Query();
        $codigo = $consulta->select('max(CodigoIndicador) as CodigoObj')
            ->from('Indicadores')
            ->one();
        if ($codigo['CodigoObj']){
            return  $codigo['CodigoObj'] + 1;
        } else {
            return 1;
        }
    }
}