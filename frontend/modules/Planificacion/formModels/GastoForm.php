<?php

namespace app\modules\Planificacion\formModels;

use yii\base\Model;

class GastoForm extends Model
{
    public string $codigoGasto;
    public string $descripcion;
    public string $entidadTransferencia;

    public function rules(): array
    {
        return [
            [['codigoGasto', 'descripcion', 'entidadTransferencia'], 'required'],
            [['codigoGasto', 'entidadTransferencia'], 'string', 'max' => 10],
            [['descripcion'], 'string', 'max' => 500],
            [['codigoGasto', 'descripcion', 'entidadTransferencia'], 'trim'],
        ];
    }
}
