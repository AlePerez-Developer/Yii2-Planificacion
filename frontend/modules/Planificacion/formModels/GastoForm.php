<?php
namespace app\modules\Planificacion\formModels;

use yii\base\Model;

class GastoForm extends Model
{
    public string $descripcion = ''; // Inicializada
    public string $entidadTransferencia = ''; // Inicializada
    public ?int $codigoGasto = null; // Inicializada como nullable

    public function rules(): array
    {
        return [
            [['descripcion', 'entidadTransferencia'], 'required'],
            [['descripcion'], 'string', 'max' => 450],
            [['entidadTransferencia'], 'string', 'max' => 5],
            [['entidadTransferencia'], 'trim'],
            ['codigoGasto', 'integer'],
        ];
    }

}