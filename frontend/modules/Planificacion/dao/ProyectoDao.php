<?php
namespace app\modules\Planificacion\dao;

use app\modules\Planificacion\models\Proyecto;
use common\models\Estado;

class ProyectoDao
{
    static function enUso(Proyecto $modelo): bool
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
        $model = Proyecto::find()
            ->where(['Codigo' => $codigo, 'CodigoEstado' => Estado::ESTADO_VIGENTE])
            ->andWhere(['!=','IdProyecto',$id])
            ->andWhere(['IdPrograma' => $idPrograma])
            ->one();

        return !$model;
    }

    static function validarId(string $id): bool
    {
        return Proyecto::find()->where(['IdProyecto' => $id, 'CodigoEstado' => Estado::ESTADO_VIGENTE])->exists();
    }
}