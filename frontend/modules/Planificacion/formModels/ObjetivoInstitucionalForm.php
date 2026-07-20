<?php

namespace app\modules\Planificacion\formModels;

use yii\base\Model;

class ObjetivoInstitucionalForm extends Model
{
    public ?string $idObjEstrategico = null;
    public ?string $codigo = null;
    public ?string $objetivo = null;
    public ?string $producto = null;
    public ?int $gestion = null;

    public function rules(): array
    {
        return [
            [['idObjEstrategico', 'codigo', 'objetivo', 'producto', 'gestion'], 'required'],
            [['idObjEstrategico'], 'string', 'max' => 36],
            [['codigo'], 'match', 'pattern' => '/^\d{2}$/', 'message' => 'El código debe tener exactamente 2 dígitos.'],
            [['objetivo', 'producto'], 'string', 'min' => 2, 'max' => 200],
            [['gestion'], 'integer', 'min' => 2000, 'max' => 2100],
            [['codigo', 'objetivo', 'producto'], 'filter', 'filter' => static fn($value) => trim((string)$value)],
        ];
    }
}
