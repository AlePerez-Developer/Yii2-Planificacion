<?php
namespace app\modules\Planificacion\dao;

use app\modules\Planificacion\models\Programa;
use common\models\Estado;

class ProgramaDao
{
    static function enUso(Programa $modelo): bool
    {
        return $modelo->getActividades()->exists() || $modelo->getProyectos()->exists() || $modelo->getLlavesPresupuestarias()->exists();
    }

    /**
     * @param string $id
     * @param string $codigo
     * @return bool
     */
    static function verificarCodigo(string $id, string $codigo): bool
    {
        $model = Programa::find()
            ->where(['Codigo' => $codigo, 'CodigoEstado' => Estado::ESTADO_VIGENTE])
            ->andWhere(['!=','IdPrograma',$id])
            ->one();

        return !$model;
    }

    /**
     * @param string $id
     * @return bool
     */
    static function validarId(string $id): bool
    {
        return Programa::find()->where(['IdPrograma' => $id, 'CodigoEstado' => Estado::ESTADO_VIGENTE])->exists();
    }
}