<?php

namespace app\modules\PlanificacionCH\dao;

use yii\base\Model;

class PlanEstudioObj extends Model
{
    private $CodigoCarrera;
    private $NumeroPlanEstudios;
    private $CodigoSistema;

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