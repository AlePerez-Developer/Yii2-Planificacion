<?php

namespace app\modules\Planificacion\dao;

use app\modules\Planificacion\models\IndicadorPoa;
use app\modules\Planificacion\models\ProgramacionIndicadorPoaGestion;
use common\models\Estado;

class IndicadorPoaDao
{
    public static function enUso(IndicadorPoa $modelo): bool
    {
        return ProgramacionIndicadorPoaGestion::find()
            ->where(['IdIndicadorPoa' => $modelo->IdIndicador])
            ->exists();
    }

    public static function verificarCodigo(
        string $id,
        string $idGestion,
        int $codigo
    ): bool {
        return !IndicadorPoa::find()
            ->where([
                'IdGestion' => $idGestion,
                'Codigo' => $codigo,
                'CodigoEstado' => Estado::ESTADO_VIGENTE,
            ])
            ->andWhere(['<>', 'IdIndicador', $id])
            ->exists();
    }
}
