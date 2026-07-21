<?php
namespace app\modules\Planificacion\dao;
use app\modules\Planificacion\models\ProgramacionIndicadorGestion;

class ProgramacionAnualDao
{
    static function enUso(ProgramacionIndicadorGestion $modelo): bool
    {
        return $modelo->getProgramacionesIndicadoresTrimestres()->exists();
    }
}
