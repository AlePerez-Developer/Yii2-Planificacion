<?php
namespace app\modules\Planificacion\dao;

use yii\base\BaseObject;
use yii\db\Query;

class GastoDao
{
    /*=====================================================
                 Genera un nuevo codigo de gasto
    =======================================================*/
    static public function GenerarCodigoGasto()
    {
        $consulta = new Query();
        $codigo = $consulta->select('max(CodigoGasto) as Codigo')
            ->from('Gastos')
            ->one();
        if ($codigo['Codigo']){
            return  $codigo['Codigo'] + 1;
        } else {
            return 1;
        }
    }
}