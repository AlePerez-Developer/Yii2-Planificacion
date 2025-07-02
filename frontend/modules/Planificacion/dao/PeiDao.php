<?php

namespace app\modules\Planificacion\dao;

use app\modules\Planificacion\models\Pei;
use common\models\Estado;
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

    static function existePei(int $codigoPei, int $gestionInicio, int $gestionFin, $fechaAprobacion): bool
    {
        $pei = Pei::find()
            ->where('(FechaAprobacion = :FechaAprobacion) or (GestionInicio = :GestionInicio) or (GestionFin = :GestionFin)',
                [':FechaAprobacion' => $fechaAprobacion, ':GestionInicio' => $gestionInicio, ':GestionFin' => $gestionFin]
            )
            ->andWhere(['!=','CodigoPei', $codigoPei])
            ->andWhere(["CodigoEstado"=> Estado::ESTADO_VIGENTE])->all();
        if(!empty($pei)){
            return true;
        }else{
            return false;
        }
    }


}