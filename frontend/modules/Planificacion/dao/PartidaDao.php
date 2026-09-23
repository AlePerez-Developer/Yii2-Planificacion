<?php

namespace app\modules\Planificacion\dao;

use app\modules\Planificacion\models\Partida;
use common\models\Estado;

class PartidaDao
{
    public static function enUso(Partida $modelo): bool
    {
        return false;
    }

    public static function verificarCombinacion(
        string $id,
        string $idFuente,
        string $idOrganismo
    ): bool {
        $existe = Partida::find()
            ->where([
                'IdFuente' => $idFuente,
                'IdOrganismo' => $idOrganismo,
                'CodigoEstado' => Estado::ESTADO_VIGENTE,
            ])
            ->andWhere(['<>', 'IdPartida', $id])
            ->exists();

        return !$existe;
    }
}
