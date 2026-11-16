<?php

namespace app\modules\Planificacion\formModels;

use yii\base\Model;

class AccionEstrategicaForm extends Model
{
    public $codigo;
    public string $descripcion;

    public function rules(): array
    {
        return [
            [['codigo', 'descripcion'], 'required'],
            [['codigo'], 'integer', 'message' => 'El código debe ser un valor numérico.'],
            [['descripcion'], 'string', 'max' => 500],
            [['descripcion'], 'trim'],
        ];
    }
}
