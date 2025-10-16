<?php

namespace app\modules\Planificacion\formModels;

use yii\base\Model;

class AreaEstrategicaForm extends Model
{
    public string $idPei;
    public int $codigo;
    public string $descripcion;

    public function rules(): array
    {
        return [
            [['idPei', 'codigo', 'descripcion'], 'required'],
            [['codigo'], 'integer'],
            [['idPei'], 'string', 'max' => 36],
            [['descripcion'], 'string', 'max' => 500],
            [['codigo', 'descripcion'], 'trim'],
        ];
    }
}
