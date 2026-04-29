<?php

namespace app\modules\Planificacion\dao;

use app\modules\Planificacion\models\LlavePresupuestaria;

class LlavePresupuestariaDao
{
    static function enUso(LlavePresupuestaria $modelo): bool
    {
        return false;
    }
}