<?php
namespace app\modules\Planificacion\dao;

use yii\db\Query;

class ObjEspecificoDao
{
    /*=====================================================
         Genera un nuevo codigo de Objetivo Especifico
    =======================================================*/
    static public function GenerarCodigoObjEspecifico()
    {
        $consulta = new Query();
        $codigo = $consulta->select('max(CodigoObjEspecifico) as CodigoObj')
            ->from('ObjetivosEspecificos')
            ->one();
        if ($codigo['CodigoObj']){
            return  $codigo['CodigoObj'] + 1;
        } else {
            return 1;
        }
    }
}
