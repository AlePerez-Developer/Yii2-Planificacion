<?php
namespace app\modules\Planificacion\dao;

use yii\base\BaseObject;
use yii\db\Query;

class ObjInstitucionalDao
{
    /*=====================================================
         Genera un nuevo codigo de Objetivo Institucional
    =======================================================*/
    static public function GenerarCodigoObjInstitucional()
    {
        $consulta = new Query();
        $codigo = $consulta->select('max(CodigoObjInstitucional) as CodigoObj')
            ->from('ObjetivosInstitucionales')
            ->one();
        if ($codigo['CodigoObj']){
            return  $codigo['CodigoObj'] + 1;
        } else {
            return 1;
        }
    }
}
