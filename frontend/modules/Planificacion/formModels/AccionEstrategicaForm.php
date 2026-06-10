<?php

namespace app\modules\Planificacion\formModels;

use yii\base\Model;

class AccionEstrategicaForm  extends Model
{
    public string $descripcion;

    public function rules(): array
    {
        return [
            [['descripcion'], 'required'],
            [['descripcion'], 'string', 'max' => 500],
            [['descripcion'], 'trim'],
        ];
    }
}