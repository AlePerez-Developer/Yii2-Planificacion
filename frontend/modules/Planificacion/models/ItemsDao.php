<?php


namespace app\modules\Planificacion\models;


use yii\base\BaseObject;
use yii\db\Query;

class ItemsDao
{
    /*=====================================================
         Genera un nuevo codigo de Item
    =======================================================*/
    static public function GenerarCodigoItem()
    {
        $consulta = new Query();
        $arrayMaximo = $consulta->select('max(NroItem) AS Maximo')
            ->from('Items')
            ->one();
        if (!$arrayMaximo) {
            $maximo = 0;
        } else {
            $maximo = $arrayMaximo['Maximo'];
        }
        return $maximo + 1;
    }

}