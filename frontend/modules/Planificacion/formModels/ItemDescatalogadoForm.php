<?php

namespace app\modules\Planificacion\formModels;

use yii\base\Model;

class ItemDescatalogadoForm extends Model
{
    public string $idOperacion = '';
    public int $idGasto = 0;
    public string $idFuente = '';
    public string $idOrganismo = '';
    public int $cantidad = 0;
    public float $precio = 0;
    public ?string $descripcion = null;
    public int $formulario = 0;

    public function rules(): array
    {
        return [
            [['idOperacion', 'idGasto', 'idFuente', 'idOrganismo', 'cantidad', 'precio', 'formulario'], 'required'],
            [['idOperacion'], 'string', 'max' => 36],
            [['idFuente', 'idOrganismo'], 'string', 'max' => 10],
            [['idGasto', 'cantidad'], 'integer', 'min' => 1],
            [['precio'], 'number', 'min' => 0.01],
            [['precio'], 'match', 'pattern' => '/^\d+(?:\.\d{1,2})?$/', 'message' => 'El precio debe tener máximo dos decimales.'],
            [['descripcion'], 'string', 'max' => 500],
            [['descripcion'], 'default', 'value' => null],
            [['formulario'], 'integer', 'min' => 10, 'max' => 14],
        ];
    }
}
