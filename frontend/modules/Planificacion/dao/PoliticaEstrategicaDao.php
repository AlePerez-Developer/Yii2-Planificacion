<?php
namespace app\modules\Planificacion\dao;

use app\modules\Planificacion\models\PoliticaEstrategica;
use common\models\Estado;

class PoliticaEstrategicaDao
{
    static function enUso(PoliticaEstrategica $model): bool
    {
        return $model->getObjetivosEstrategicos()->exists();
    }

    static function verificarCodigo(string $id, string $idAreaEstrategica, int $codigo): bool
    {
        $model = PoliticaEstrategica::find()
            ->where(['codigo' => $codigo, 'CodigoEstado' => Estado::ESTADO_VIGENTE])
            ->andWhere(['!=','IdPoliticaEstrategica',$id])
            ->andWhere(['IdAreaEstrategica' => $idAreaEstrategica])
            ->exists();

        return !$model;
    }

    static function validarId(string $id, string $idAreaEstrategica): bool
    {
        return PoliticaEstrategica::find()->where(['IdAreaEstrategica'=> $idAreaEstrategica ,'IdPoliticaEstrategica' => $id, 'CodigoEstado' => Estado::ESTADO_VIGENTE])->exists();
    }
}
