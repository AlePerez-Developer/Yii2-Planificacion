<?php

namespace app\modules\Planificacion\dao;

use app\modules\Planificacion\models\OperacionDtic;
use common\models\Estado;

class OperacionDticDao
{
    public static function verificarCodigo(
        string $id,
        string $idLlavePresupuestaria,
        string $idGestion,
        string $idEstadoPoa,
        string $codigo
    ): bool {
        return !OperacionDtic::find()
            ->where([
                'IdLlavePresupuestaria' => $idLlavePresupuestaria,
                'IdGestion' => $idGestion,
                'IdEstadoPoa' => $idEstadoPoa,
                'Codigo' => $codigo,
            ])
            ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['<>', 'IdOperacion', $id])
            ->exists();
    }
}
