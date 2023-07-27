<?php
namespace app\modules\Planificacion\dao;

use yii\base\BaseObject;
use yii\db\Query;

class ProyectoDao
{
    /*=====================================================
                 Genera un nuevo codigo de actividad
            =======================================================*/
    static public function GenerarCodigoProyecto()
    {
        $consulta = new Query();
        $codigo = $consulta->select('max(CodigoProyecto) as Codigo')
            ->from('Proyectos')
            ->one();
        if ($codigo['Codigo']){
            return  $codigo['Codigo'] + 1;
        } else {
            return 1;
        }
    }
}