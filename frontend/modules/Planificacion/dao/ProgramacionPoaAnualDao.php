<?php

namespace app\modules\Planificacion\dao;

use app\modules\Planificacion\models\ProgramacionIndicadorPoaGestion;
use app\modules\Planificacion\models\ProgramacionIndicadorPoaTrimestre;

class ProgramacionPoaAnualDao
{
    public static function enUso(ProgramacionIndicadorPoaGestion $modelo): bool
    {
        return ProgramacionIndicadorPoaTrimestre::find()
            ->where([
                'IdProgramacionIndicadorPoaGestion' =>
                    $modelo->IdProgramacionIndicadorPoaGestion,
            ])
            ->exists();
    }
}
