<?php
namespace app\modules\Planificacion\dao;

use app\modules\Planificacion\models\ObjetivoEstrategico;
use common\models\Estado;
use Yii;

class ObjEstrategicoDao
{

    static function enUso(ObjetivoEstrategico $objetivo): bool
    {
        return $objetivo->getObjetivosInstitucionales()->exists();
    }

    /**
     * @param string $id
     * @param string $idAreaEstrategica
     * @param string $idPoliticaEstrategica
     * @param int $codigo
     * @return bool
     */
    static function verificarCodigo(string $id, string $idAreaEstrategica, string $idPoliticaEstrategica, int $codigo): bool
    {
        $model = ObjetivoEstrategico::find()
            ->where(['Codigo' => $codigo, 'CodigoEstado' => Estado::ESTADO_VIGENTE])
            ->andWhere(['!=','IdObjEstrategico',$id])
            ->andWhere(['IdPei' => yii::$app->contexto->getPei()])
            ->andWhere(['IdAreaEstrategica' => $idAreaEstrategica])
            ->andWhere(['IdPoliticaEstrategica' => $idPoliticaEstrategica])
            ->one();

        return !$model;
    }

    static function validarId(string $id): bool
    {
        return ObjetivoEstrategico::find()->where(['IdPei'=> yii::$app->contexto->getPei() ,'IdObjEstrategico' => $id, 'CodigoEstado' => Estado::ESTADO_VIGENTE])->exists();
    }
}