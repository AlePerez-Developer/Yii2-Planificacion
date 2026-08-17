<?php

namespace app\modules\Planificacion\formModels;

use yii\base\Model;

class ItemCatalogadoForm extends Model
{
    public string $idOperacion = '';
    public string $idSigma = '';
    public string $idFuente = '';
    public string $idOrganismo = '';
    public int $cantidad = 0;
    public float $precio = 0;
    public int $formulario = 0;

    public function rules(): array
    {
        return [
            [['idOperacion', 'idSigma', 'idFuente', 'idOrganismo', 'cantidad', 'precio', 'formulario'], 'required'],
            [['idOperacion', 'idSigma'], 'string', 'max' => 36],
            [['idFuente', 'idOrganismo'], 'string', 'max' => 10],
            [['cantidad'], 'integer', 'min' => 1],
            [['precio'], 'number', 'min' => 0.01],
            [['precio'], 'match', 'pattern' => '/^\d+(?:\.\d{1,2})?$/', 'message' => 'El precio debe tener máximo dos decimales.'],
            [['formulario'], 'integer', 'min' => 7, 'max' => 9],
        ];
    }
}
