<?php

namespace app\modules\Planificacion\dao;

use app\modules\Planificacion\models\ObjetivoEspecifico;
use common\models\Estado;

class ObjEspecificoDao
{
    public static function enUso(ObjetivoEspecifico $modelo): bool
    {
        return $modelo->getIndicadoresPoa()->exists();
    }

    public static function verificarCodigo(
        string $id,
        string $idObjInstitucional,
        string $idLlavePresupuestaria,
        int $gestion,
        string $codigo
    ): bool {
        return !ObjetivoEspecifico::find()
            ->where([
                'IdObjInstitucional' => $idObjInstitucional,
                'IdLlavePresupuestaria' => $idLlavePresupuestaria,
                'Gestion' => $gestion,
                'Codigo' => $codigo,
            ])
            ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['<>', 'IdObjEspecifico', $id])
            ->exists();
    }
}
