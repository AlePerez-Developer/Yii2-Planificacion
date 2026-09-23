<?php

namespace app\modules\Planificacion\dao;

use app\modules\Planificacion\models\ControlEnvioPoa;
use app\modules\Planificacion\models\CronogramaPoa;
use common\models\Estado;

class CronogramaPoaDao
{
    public static function enUso(CronogramaPoa $modelo): bool
    {
        return ControlEnvioPoa::find()
            ->where(['IdCronograma' => $modelo->IdCronograma])
            ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->exists();
    }

    public static function solapa(string $id, string $inicio, string $fin): bool
    {
        return CronogramaPoa::find()
            ->where(['CodigoEstado' => Estado::ESTADO_VIGENTE])
            ->andWhere(['<=', 'FechaInicio', $fin])
            ->andWhere(['>=', 'FechaFin', $inicio])
            ->andWhere(['<>', 'IdCronograma', $id])
            ->exists();
    }
}
