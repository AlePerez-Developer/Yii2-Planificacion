<?php
namespace app\modules\Planificacion\dao;

use yii\base\BaseObject;
use yii\db\Query;

class ActividadDao
{
    /*=====================================================
             Genera un nuevo codigo de actividad
        =======================================================*/
    static public function GenerarCodigoActividad()
    {
        $consulta = new Query();
        $codigo = $consulta->select('max(CodigoActividad) as Codigo')
            ->from('Actividades')
            ->one();
        if ($codigo['Codigo']){
            return  $codigo['Codigo'] + 1;
        } else {
            return 1;
        }
    }
}