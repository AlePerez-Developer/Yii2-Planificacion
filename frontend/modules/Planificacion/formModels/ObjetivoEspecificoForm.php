<?php

namespace app\modules\Planificacion\formModels;

use yii\base\Model;

class ObjetivoEspecificoForm extends Model
{
    public ?string $idObjInstitucional = null;
    public ?string $codigo = null;
    public ?string $objetivo = null;
    public ?string $producto = null;

    public function rules(): array
    {
        return [
            [['idObjInstitucional', 'codigo', 'objetivo', 'producto'], 'required'],
            [['idObjInstitucional'], 'string', 'max' => 36],
            [['codigo'], 'match', 'pattern' => '/^\d{2}$/', 'message' => 'El código debe tener exactamente dos dígitos.'],
            [['objetivo', 'producto'], 'string', 'min' => 2, 'max' => 200],
            [['codigo', 'objetivo', 'producto'], 'filter', 'filter' => static fn($value) => trim((string)$value)],
        ];
    }
}
