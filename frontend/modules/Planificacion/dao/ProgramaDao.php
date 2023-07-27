<?php
namespace app\modules\Planificacion\dao;

use yii\base\BaseObject;
use yii\db\Query;

class ProgramaDao
{
    /*=====================================================
                 Genera un nuevo codigo de programa
    =======================================================*/
    static public function GenerarCodigoPrograma()
    {
        $consulta = new Query();
        $codigo = $consulta->select('max(CodigoPrograma) as Codigo')
            ->from('Programas')
            ->one();
        if ($codigo['Codigo']){
            return  $codigo['Codigo'] + 1;
        } else {
            return 1;
        }
    }
}