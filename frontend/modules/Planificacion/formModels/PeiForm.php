<?php
namespace app\modules\Planificacion\formModels;

use yii\base\Model;

class PeiForm extends Model
{
    public string $descripcion;
    public string $fechaAprobacion;
    public int $gestionInicio;
    public int $gestionFin;

    public function rules(): array
    {
        return [
            [['descripcion', 'fechaAprobacion', 'gestionInicio', 'gestionFin'], 'required'],
            [['descripcion'], 'string', 'max' => 500],
            [['fechaAprobacion'], 'date', 'format' => 'php:Y-m-d'],
            [['gestionInicio', 'gestionFin'], 'integer'],
            ['gestionFin', 'compare', 'compareAttribute' => 'gestionInicio', 'operator' => '>', 'message' => 'La gestión final debe ser mayor  a la inicial.'],
            ['gestionInicio', 'compare', 'compareValue' => 2000, 'operator' => '>', 'message' => 'La gestión inicio debe ser mayor  a 2000.'],
            ['gestionFin', 'compare', 'compareValue' => 2001, 'operator' => '>', 'message' => 'La gestión final debe ser mayor  a 2001.']
        ];
    }
}