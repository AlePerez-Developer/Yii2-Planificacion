<?php
namespace app\modules\Planificacion\dao;

use app\modules\Planificacion\models\AreaEstrategica;
use common\models\Estado;
use Yii;

class AreaEstrategicaDao
{
    static function enUso(AreaEstrategica $model): bool
    {
        return $model->getPoliticasEstrategicas()->exists();
    }

    static function verificarCodigo(string $id, int $codigo): bool
    {
        $model = AreaEstrategica::find()
            ->where(['codigo' => $codigo, 'CodigoEstado' => Estado::ESTADO_VIGENTE])
            ->andWhere(['!=','IdAreaEstrategica',$id])
            ->andWhere(['IdPei' => yii::$app->contexto->getPei()])
            ->andWhere(['CodigoEstado' => Estado::ESTADO_VIGENTE])
            ->exists();

        return !$model;
    }

    static function validarId(string $id): bool
    {
        return AreaEstrategica::find()->where(['IdPei'=> yii::$app->contexto->getPei() ,'IdAreaEstrategica' => $id, 'CodigoEstado' => Estado::ESTADO_VIGENTE])->exists();
    }
}
