<?php

namespace app\modules\Planificacion\formModels;

use yii\base\Model;

class IngresoForm extends Model
{
    public int $cantidad = 0;
    public float $precio = 0;
    public ?string $descripcion = null;

    public function rules(): array
    {
        return [
            [['cantidad', 'precio'], 'required'],
            [['cantidad'], 'integer', 'min' => 1],
            [['precio'], 'number', 'min' => 0.01],
            [['precio'], 'match', 'pattern' => '/^\d+(?:\.\d{1,2})?$/', 'message' => 'El precio debe tener máximo dos decimales.'],
            [['descripcion'], 'string', 'max' => 500],
            [['descripcion'], 'default', 'value' => null],
        ];
    }
}
