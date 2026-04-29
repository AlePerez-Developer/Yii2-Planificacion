<?php
namespace app\modules\Planificacion\dao;

use app\modules\Planificacion\models\ObjetivoEstrategico;
use app\modules\Planificacion\models\ObjetivoInstitucional;
use common\models\Estado;
use Yii;

class ObjInstitucionalDao
{

    static function enUso(ObjetivoInstitucional $objetivo): bool
    {
        return $objetivo->getObjetivosEspecificos()->exists() ;
    }

    /**
     * @param string $id
     * @param string $idObjEstrategico
     * @param int $codigo
     * @return bool
     */
    static function verificarCodigo(string $id, string $idObjEstrategico, int $codigo): bool
    {
        $model = ObjetivoInstitucional::find()
            ->where(['Codigo' => $codigo, 'CodigoEstado' => Estado::ESTADO_VIGENTE])
            ->andWhere(['!=','IdObjInstitucional',$id])
            ->andWhere(['IdPei' => yii::$app->contexto->getPei()])
            ->andWhere(['IdObjInstitucional' => $idObjEstrategico])
            ->one();

        return !$model;
    }

    static function validarId(string $id): bool
    {
        return ObjetivoEstrategico::find()->where(['IdPei'=> yii::$app->contexto->getPei() ,'IdObjEstrategico' => $id, 'CodigoEstado' => Estado::ESTADO_VIGENTE])->exists();
    }
}