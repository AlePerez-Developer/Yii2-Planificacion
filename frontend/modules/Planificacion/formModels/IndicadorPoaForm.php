<?php

namespace app\modules\Planificacion\formModels;

use yii\base\Model;

class IndicadorPoaForm extends Model
{
    public ?string $idObjEspecifico = null;
    public ?int $codigo = null;
    public ?string $descripcion = null;
    public ?int $meta = null;
    public ?string $tipo = null;
    public ?string $categoria = null;
    public ?string $unidad = null;

    public function rules(): array
    {
        return [
            [['idObjEspecifico', 'codigo', 'descripcion', 'meta', 'tipo', 'categoria', 'unidad'], 'required'],
            [['idObjEspecifico'], 'string', 'max' => 36],
            [['codigo'], 'integer', 'min' => 1],
            [['meta'], 'integer', 'min' => 0],
            [['descripcion'], 'string', 'min' => 2, 'max' => 500],
            [['tipo', 'categoria', 'unidad'], 'string', 'max' => 20],
            [['descripcion', 'tipo', 'categoria', 'unidad'], 'filter', 'filter' => static fn($value) => trim((string)$value)],
        ];
    }
}
