<?php

namespace app\modules\PlanificacionCH\dao;

use yii\base\Model;

class CarreraObj extends Model
{
    private $CodigoCarrera;
    private $NombreCarrera;

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