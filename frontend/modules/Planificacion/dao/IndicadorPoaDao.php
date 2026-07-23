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
            ->where(['IdIndicadorPoa' => $modelo->IdIndicadorPoa])
            ->exists();
    }

    public static function verificarCodigo(string $id, string $idObjEspecifico, int $codigo): bool
    {
        return !IndicadorPoa::find()
            ->where(['IdObjEspecifico' => $idObjEspecifico, 'Codigo' => $codigo])
            ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['<>', 'IdIndicadorPoa', $id])
            ->exists();
    }
}
