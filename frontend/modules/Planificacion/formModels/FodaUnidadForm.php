<?php

namespace app\modules\Planificacion\formModels;

use app\modules\Planificacion\models\FODAUnidad;
use yii\base\Model;

class FodaUnidadForm extends Model
{
    public string $descripcion = '';
    public string $tipo = '';

    public function rules(): array
    {
        return [
            [['descripcion', 'tipo'], 'required'],
            [['descripcion'], 'string', 'min' => 2, 'max' => 500],
            [['tipo'], 'in', 'range' => array_values(FODAUnidad::tipos())],
        ];
    }
}
