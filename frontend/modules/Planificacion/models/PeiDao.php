<?php

namespace app\modules\Planificacion\models;

use yii\db\Query;


class PeiDao
{
    /*=============================================
     Genera un nuevo codigo de pei
     =============================================*/
    static public function generarCodigoPei()
    {
        $consulta = new Query();
        $codigo = $consulta->select('max(CodigoPei) as CodigoPei')
            ->from('PEIs')
            ->one();
        if ($codigo['CodigoPei']){
            return  $codigo['CodigoPei'] + 1;
        } else {
            return 1;
        }
    }


}