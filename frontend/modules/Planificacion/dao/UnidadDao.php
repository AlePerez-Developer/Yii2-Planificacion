<?php
namespace app\modules\Planificacion\dao;

use yii\base\BaseObject;
use yii\db\Query;

class UnidadDao
{
    /*=====================================================
                 Genera un nuevo codigo de unidad
    =======================================================*/
    static public function GenerarCodigoUnidad()
    {
        $consulta = new Query();
        $codigo = $consulta->select('max(CodigoUnidad) as Codigo')
            ->from('Unidades')
            ->one();
        if ($codigo['Codigo']){
            return  $codigo['Codigo'] + 1;
        } else {
            return 1;
        }
    }
}