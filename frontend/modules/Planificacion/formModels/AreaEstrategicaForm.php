<?php

namespace app\modules\Planificacion\formModels;

use yii\base\Model;

class AreaEstrategicaForm extends Model
{
    public int $codigoPei;      // CodigoPei
    public int $codigo;
    public string $descripcion;

    public function rules(): array
    {
        return [
            [['codigoPei', 'codigo', 'descripcion'], 'required'],
            [['codigoPei', 'codigo'], 'integer'],
            [['descripcion'], 'string', 'max' => 250],
            [['codigo', 'descripcion'], 'trim'],
        ];
    }
}
