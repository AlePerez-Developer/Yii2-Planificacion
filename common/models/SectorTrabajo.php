<?php

namespace common\models;


use yii\helpers\ArrayHelper;

class SectorTrabajo extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'SectoresTrabajo';
    }

    public function getEstado()
    {
        return $this->hasOne(Estado::className(), ['CodigoEstado' => 'CodigoEstado']);
    }
}
