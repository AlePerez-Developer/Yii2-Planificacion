<?php
namespace app\modules\Planificacion\dao;


use app\modules\Planificacion\models\Ue;
use common\models\Estado;

class UeDao
{
    static function enUso(Ue $modelo): bool
    {
        return $modelo->getLlavesPresupuestarias()->exists();
    }

    /**
     * @param string $id
     * @param string $codigo
     * @return bool
     */
    static function verificarCodigo(string $id, string $codigo): bool
    {
        $model = Ue::find()
            ->where(['Ue' => $codigo, 'CodigoEstado' => Estado::ESTADO_VIGENTE])
            ->andWhere(['!=','IdUe',$id])
            ->one();

        return !$model;
    }

    /**
     * @param string $id
     * @return bool
     */
    static function validarId(string $id): bool
    {
        return Ue::find()->where(['IdUe' => $id, 'CodigoEstado' => Estado::ESTADO_VIGENTE])->exists();
    }
}
