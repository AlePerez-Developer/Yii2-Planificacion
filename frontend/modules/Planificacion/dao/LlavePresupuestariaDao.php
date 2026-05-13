<?php

namespace app\modules\Planificacion\dao;

use app\modules\Planificacion\models\LlavePresupuestaria;
use common\models\Estado;

class LlavePresupuestariaDao
{
    static function enUso(LlavePresupuestaria $modelo): bool
    {
        return false;
    }

    static function verificarCodigo(string $id, string $idDa, string $idUe, string $idProyecto, string $idActividad): bool
    {
        $model = LlavePresupuestaria::find()->where([
            'IdDa' => $idDa,
            'IdUe' => $idUe,
            'IdProyecto' => $idProyecto,
            'IdActividad' => $idActividad,
            'CodigoEstado' => Estado::ESTADO_VIGENTE
        ])
            ->andWhere(['!=','IdLlavePresupuestaria',$id])
            ->exists();

        return !$model;
    }

    static function validarId(string $id, string $idDa, string $idUe, string $idProyecto, string $idActividad): bool
    {
        return LlavePresupuestaria::find()
            ->where([
                'IdLlavePresupuestaria' => $id,
                'IdDa' => $idDa,
                'IdUe' => $idUe,
                'IdProyecto' => $idProyecto,
                'IdActividad' => $idActividad,
                'CodigoEstado' => Estado::ESTADO_VIGENTE
            ])->exists();
    }
}