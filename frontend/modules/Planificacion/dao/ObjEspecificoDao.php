<?php

namespace app\modules\Planificacion\dao;

use app\modules\Planificacion\models\ObjetivoEspecifico;
use common\models\Estado;

class ObjEspecificoDao
{
    public static function enUso(ObjetivoEspecifico $modelo): bool
    {
        return false;
    }

    public static function verificarCodigo(
        string $id,
        string $idObjInstitucional,
        string $idUnidadEjecutora,
        string $idGestion,
        string $codigo
    ): bool {
        return !ObjetivoEspecifico::find()
            ->where([
                'IdObjInstitucional' => $idObjInstitucional,
                'IdUnidadEjecutora' => $idUnidadEjecutora,
                'IdGestion' => $idGestion,
                'Codigo' => $codigo,
            ])
            ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['<>', 'IdObjEspecifico', $id])
            ->exists();
    }
}
