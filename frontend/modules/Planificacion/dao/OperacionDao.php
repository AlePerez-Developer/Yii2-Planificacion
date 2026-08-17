<?php

namespace app\modules\Planificacion\dao;

use app\modules\Planificacion\models\Operacion;
use common\models\Estado;

class OperacionDao
{
    public static function verificarCodigo(
        string $id,
        string $idObjEspecifico,
        string $idUnidadEjecutora,
        int $idEstadoPoa,
        string $codigo
    ): bool {
        return !Operacion::find()
            ->where([
                'IdObjEspecifico' => $idObjEspecifico,
                'IdUnidadEjecutora' => $idUnidadEjecutora,
                'IdEstadoPoa' => $idEstadoPoa,
                'Codigo' => $codigo,
            ])
            ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['<>', 'IdOperacion', $id])
            ->exists();
    }
}
