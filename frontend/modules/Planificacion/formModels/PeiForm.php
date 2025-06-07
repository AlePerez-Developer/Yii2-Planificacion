<?php
namespace app\modules\Planificacion\formModels;

use yii\base\Model;

class PeiForm extends Model
{
    public $descripcionPei;
    public $fechaAprobacion;
    public $gestionInicio;
    public $gestionFin;

    public function rules()
    {
        return [
            [['descripcionPei', 'fechaAprobacion', 'gestionInicio', 'gestionFin'], 'required'],
            [['descripcionPei'], 'string', 'max' => 255],
            [['fechaAprobacion'], 'date', 'format' => 'php:Y-m-d'],
            [['gestionInicio', 'gestionFin'], 'integer'],
            ['gestionFin', 'compare', 'compareAttribute' => 'gestionInicio', 'operator' => '>=', 'message' => 'La gestión final debe ser mayor o igual a la inicial.'],
        ];
    }
}