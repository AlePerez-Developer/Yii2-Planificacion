<?php
namespace app\modules\Planificacion\dao;

use app\modules\Planificacion\models\Actividad;
use common\models\Estado;

class ActividadDao
{
    static function enUso(Actividad $modelo): bool
    {
        return $modelo->getLlavesPresupuestarias()->exists();
    }

    /**
     * @param string $id
     * @param string $idPrograma
     * @param string $codigo
     * @return bool
     */
    static function verificarCodigo(string $id, string $idPrograma, string $codigo): bool
    {
        $model = Actividad::find()
            ->where(['Codigo' => $codigo, 'CodigoEstado' => Estado::ESTADO_VIGENTE])
            ->andWhere(['!=','IdActividad',$id])
            ->andWhere(['IdPrograma' => $idPrograma])
            ->one();

        return !$model;
    }

    static function validarId(string $id): bool
    {
        return Actividad::find()->where(['IdActividad' => $id, 'CodigoEstado' => Estado::ESTADO_VIGENTE])->exists();
    }
}