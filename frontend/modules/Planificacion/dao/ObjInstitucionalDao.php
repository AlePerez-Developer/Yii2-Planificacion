<?php

namespace app\modules\Planificacion\dao;

use app\modules\Planificacion\models\ObjetivoInstitucional;
use common\models\Estado;

class ObjInstitucionalDao
{
    public static function enUso(ObjetivoInstitucional $objetivo): bool
    {
        return $objetivo->getObjetivosEspecificos()->exists();
    }

    public static function verificarCodigo(
        string $id,
        string $idObjEstrategico,
        string $codigo
    ): bool {
        return !ObjetivoInstitucional::find()
            ->where([
                'IdObjEstrategico' => $idObjEstrategico,
                'Codigo' => $codigo,
            ])
            ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['<>', 'IdObjInstitucional', $id])
            ->exists();
    }
}
