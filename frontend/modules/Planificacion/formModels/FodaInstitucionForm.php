<?php

namespace app\modules\Planificacion\formModels;

use app\modules\Planificacion\models\FODAInstitucion;
use yii\base\Model;

class FodaInstitucionForm extends Model
{
    public string $descripcion = '';
    public string $tipo = '';

    public function rules(): array
    {
        return [
            [['descripcion', 'tipo'], 'required'],
            [['descripcion'], 'string', 'min' => 2, 'max' => 500],
            [['tipo'], 'in', 'range' => array_values(FODAInstitucion::tipos())],
        ];
    }
}
