<?php

namespace app\modules\Planificacion\formModels;

use yii\base\Model;

class CronogramaPoaForm extends Model
{
    public string $fechaInicio = '';
    public string $fechaFin = '';

    public function rules(): array
    {
        return [
            [['fechaInicio', 'fechaFin'], 'required'],
            [['fechaInicio', 'fechaFin'], 'string', 'max' => 32],
        ];
    }
}
