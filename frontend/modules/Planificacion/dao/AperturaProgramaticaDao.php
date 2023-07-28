<?php
namespace app\modules\Planificacion\dao;

use yii\base\BaseObject;
use yii\db\Query;

class AperturaProgramaticaDao
{
    /*=====================================================
             Genera un nuevo codigo de apertura programatica
    =======================================================*/
    static public function GenerarCodigoAperturaProgramatica()
    {
        $consulta = new Query();
        $codigo = $consulta->select('max(CodigoAperturaProgramatica) as Codigo')
            ->from('AperturasProgramticas')
            ->one();
        if ($codigo['Codigo']){
            return  $codigo['Codigo'] + 1;
        } else {
            return 1;
        }
    }

}