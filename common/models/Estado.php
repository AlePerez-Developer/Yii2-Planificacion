<?php

namespace common\models;

use yii\db\ActiveRecord;

class Estado extends ActiveRecord
{
    const ESTADO_ELIMINADO = 'E';
    const ESTADO_CADUCO = 'C';
    const ESTADO_VIGENTE = 'V';

    public static function tableName()
    {
        return 'Estados';
    }
}