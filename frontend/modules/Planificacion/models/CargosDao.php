<?php


namespace app\modules\Planificacion\models;


use yii\base\BaseObject;
use yii\db\Query;

class CargosDao
{
    /*=====================================================
            Genera un nuevo codigo de Cargo
       =======================================================*/
    static public function GenerarCodigoCargo($sectorTrabajo)
    {
        $consulta = new Query();
        $arrayMaximo = $consulta->select('max(cast(substring(CodigoCargo, len(CodigoCargo)-2,len(CodigoCargo)) AS int)) AS Maximo')
            ->from('Cargos')
            ->where(['CodigoSectorTrabajo' => $sectorTrabajo])
            ->one();
        if (!$arrayMaximo) {
            $maximo = 0;
        } else {
            $maximo = $arrayMaximo['Maximo'];
        }
        $maximo = $maximo + 1;
        $ncodigo = $sectorTrabajo;
        if ($maximo <= 99) {
            $ncodigo = $ncodigo . '0';
        }
        if ($maximo <= 9) {
            $ncodigo = $ncodigo . '0';
        }
        return $ncodigo . $maximo ;
    }
}