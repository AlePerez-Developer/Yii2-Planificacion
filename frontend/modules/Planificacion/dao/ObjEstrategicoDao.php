<?php
namespace app\modules\Planificacion\dao;

use app\modules\Planificacion\models\ObjetivoEstrategico;
use common\models\Estado;
use yii\db\Query;

class ObjEstrategicoDao
{
    /*=====================================================
         Genera un nuevo codigo de Objetivo Estrategico
    =======================================================*/
    static public function GenerarCodigoObjEstrategico()
    {
        $consulta = new Query();
        $codigo = $consulta->select('max(CodigoObjEstrategico) as CodigoObj')
            ->from('ObjetivosEstrategicos')
            ->one();
        if ($codigo['CodigoObj']){
            return  $codigo['CodigoObj'] + 1;
        } else {
            return 1;
        }
    }

    static function enUso(ObjetivoEstrategico $objetivo): bool
    {
        return $objetivo->getObjetivosInstitucionales()->exists();
    }

    /**
     * @param int $codigoPei
     * @param int $codigoObjEstrategico
     * @param string $codigoObjetivo
     * @return bool
     */
    static function verificarCodigo(int $codigoPei, int $codigoObjEstrategico, string $codigoObjetivo): bool
    {
        $objetivoEstrategico = ObjetivoEstrategico::find()
            ->where(['CodigoObjetivo' => $codigoObjetivo, 'CodigoEstado' => Estado::ESTADO_VIGENTE])
            ->andWhere(['!=','CodigoObjEstrategico',$codigoObjEstrategico])
            ->andWhere(['CodigoPei' => $codigoPei])
            ->one();

        if ($objetivoEstrategico) {
            return false;
        }

        return true;
    }
}