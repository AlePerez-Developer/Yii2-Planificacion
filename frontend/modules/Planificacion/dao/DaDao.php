<?php
namespace app\modules\Planificacion\dao;

use app\modules\Planificacion\models\Da;
use common\models\Estado;

class DaDao
{
    static function enUso(Da $modelo): bool
    {
        return $modelo->getLlavesPresupuestarias()->exists() ;
    }

    /**
     * @param string $id
     * @param string $codigo
     * @return bool
     */
    static function verificarCodigo(string $id, string $codigo): bool
    {
        $model = Da::find()
            ->where(['Da' => $codigo, 'CodigoEstado' => Estado::ESTADO_VIGENTE])
            ->andWhere(['!=','IdDa',$id])
            ->one();

        return !$model;
    }

    /**
     * @param string $id
     * @return bool
     */
    static function validarId(string $id): bool
    {
        return Da::find()->where(['IdDa' => $id, 'CodigoEstado' => Estado::ESTADO_VIGENTE])->exists();
    }
}
