<?php

namespace app\modules\Planificacion\formModels;

use yii\base\Model;

class ProgramacionItemOperacionDticForm extends Model
{
    public string $idOperacion;
    public string $idProgramacionItem;
    public int $cantidadAsignada;

    public function rules(): array
    {
        return [
            [['idOperacion', 'idProgramacionItem', 'cantidadAsignada'], 'required'],
            [['idOperacion', 'idProgramacionItem'], 'string', 'max' => 36],
            [['cantidadAsignada'], 'integer', 'min' => 1],
        ];
    }
}
