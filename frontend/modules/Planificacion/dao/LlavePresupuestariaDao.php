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

    static function verificarCodigo(
        string $id,
        string $idUnidadEjecutora,
        string $idProyecto,
        string $idActividad
    ): bool {
        $model = LlavePresupuestaria::find()->where([
            'IdUnidadEjecutora' => $idUnidadEjecutora,
            'IdProyecto' => $idProyecto,
            'IdActividad' => $idActividad,
            'CodigoEstado' => Estado::ESTADO_VIGENTE
        ])
            ->andWhere(['!=', 'IdLlavePresupuestaria', $id])
            ->exists();

        return !$model;
    }
}
