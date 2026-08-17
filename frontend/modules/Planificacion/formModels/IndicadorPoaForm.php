<?php

namespace app\modules\Planificacion\formModels;

use yii\base\Model;

class IndicadorPoaForm extends Model
{
    public int $codigo;
    public int $meta;
    public string $descripcion;
    public int $lineaBase;
    public string $idTipoResultado;
    public string $idCategoriaIndicador;
    public string $idUnidadIndicador;

    public function rules(): array
    {
        return [
            [['codigo', 'meta', 'descripcion', 'lineaBase', 'idTipoResultado', 'idCategoriaIndicador', 'idUnidadIndicador'], 'required'],
            [['idTipoResultado', 'idCategoriaIndicador', 'idUnidadIndicador'], 'string', 'max' => 36],
            [['codigo'], 'integer', 'min' => 1],
            [['meta', 'lineaBase'], 'integer', 'min' => 0],
            [['descripcion'], 'string', 'min' => 2, 'max' => 500],
        ];
    }
}
