<?php
namespace app\modules\Planificacion\dao;

use app\modules\Planificacion\models\ObjetivoEstrategico;
use common\models\Estado;

class ObjEstrategicoDao
{

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