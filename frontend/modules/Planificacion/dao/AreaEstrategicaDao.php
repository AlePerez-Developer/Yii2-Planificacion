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
            ->one();

        if ($model) {
            return false;
        }

        return true;
    }

    /*
    static function validarCodigoArea(string $id): bool
    {
        return AreaEstrategica::find()->where(['IdAreaEstrategica' => $id])->exists();
    }

    static function obtenerPei(AreaEstrategica $model): array
    {
        return $model->getPei()->asArray()->all();
    }*/
}
