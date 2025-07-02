<?php
namespace app\modules\Planificacion\formModels;

use yii\base\Model;

class PeiForm extends Model
{
    public string $descripcionPei;
    public string $fechaAprobacion;
    public int $gestionInicio;
    public int $gestionFin;

    public function rules(): array
    {
        return [
            [['descripcionPei', 'fechaAprobacion', 'gestionInicio', 'gestionFin'], 'required'],
            [['descripcionPei'], 'string', 'max' => 255],
            [['fechaAprobacion'], 'date', 'format' => 'php:Y-m-d'],
            [['gestionInicio', 'gestionFin'], 'integer'],
            ['gestionFin', 'compare', 'compareAttribute' => 'gestionInicio', 'operator' => '>', 'message' => 'La gestión final debe ser mayor  a la inicial.'],
        ];
    }
}