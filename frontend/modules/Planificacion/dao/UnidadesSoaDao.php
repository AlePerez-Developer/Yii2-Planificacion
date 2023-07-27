<?php


namespace app\modules\Planificacion\dao;


use yii\base\BaseObject;
use yii\db\Query;

class UnidadesSoaDao
{
    /*=====================================================
         Genera un nuevo codigo de Objetivo Estrategico
    =======================================================*/
    static public function GenerarCodigoUnidad()
    {
        $consulta = new Query();
        $arrayMaximo = $consulta->select('max(cast(substring(CodigoUnidad, len(CodigoUnidad)-2,len(CodigoUnidad)) AS int)) AS Maximo')
            ->from('UnidadesSoa')
            ->one();
        if (!$arrayMaximo) {
            $maximo = 0;
        } else {
            $maximo = $arrayMaximo['Maximo'];
        }
        $maximo = $maximo + 1;
        $ncodigo = 'UND';
        if ($maximo <= 99) {
            $ncodigo = $ncodigo . '0';
        }
        if ($maximo <= 9) {
            $ncodigo = $ncodigo . '0';
        }
        return $ncodigo . $maximo ;
    }

}