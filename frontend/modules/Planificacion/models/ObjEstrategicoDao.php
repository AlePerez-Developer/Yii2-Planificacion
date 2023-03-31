<?php
namespace app\modules\Planificacion\models;

use yii\db\Query;

class ObjEstrategicoDao
{
    /*=====================================================
         Genera un nuevo codigo de Objetivo Estrategico
    =======================================================*/
    static public function GenerarCodigoObjEstrategico()
    {
        $consulta = new Query();
        $codigo = $consulta->select('max(CodigoObjEstrategico) as CodigoObj')
            ->from('ObjetivosEstrategicos')
            ->one();
        if ($codigo['CodigoObj']){
            return  $codigo['CodigoObj'] + 1;
        } else {
            return 1;
        }
    }
}