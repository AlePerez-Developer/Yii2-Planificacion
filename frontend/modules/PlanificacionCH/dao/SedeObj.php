<?php

namespace app\modules\PlanificacionCH\dao;

use yii\base\Model;

class SedeObj extends Model
{
    private $CodigoSede;
    private $NombreSede;

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    public function __set($property, $value)
    {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        }
    }
}