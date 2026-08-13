<?php

namespace app\modules\Planificacion\dao;

use app\modules\Planificacion\models\UnidadEjecutora;
use common\models\Estado;

class UnidadEjecutoraDao
{
    static function enUso(UnidadEjecutora $modelo): bool
    {
        return $modelo->getLlavesPresupuestarias()->exists();
    }

    /**
     * @param string $id
     * @param string $IdDa
     * @param string $codigo
     * @return bool
     */
    static function verificarCodigo(string $id, string $IdDa, string $codigo): bool
    {
        $model = UnidadEjecutora::find()
            ->where(['IdDa' => $IdDa, 'Ue' => $codigo, 'CodigoEstado' => Estado::ESTADO_VIGENTE])
            ->andWhere(['!=','IdUnidadEjecutora',$id])
            ->one();

        return !$model;
    }

    /**
     * @param string $id
     * @param string $IdDa
     * @return bool
     */
    static function validarId(string $id, string $IdDa): bool
    {
        return UnidadEjecutora::find()->where(['IdUe' => $id, 'IdDa' => $IdDa, 'CodigoEstado' => Estado::ESTADO_VIGENTE])->exists();
    }

}