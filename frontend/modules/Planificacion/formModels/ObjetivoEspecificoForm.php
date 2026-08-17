<?php

namespace app\modules\Planificacion\formModels;

use yii\base\Model;

class ObjetivoEspecificoForm extends Model
{
    public string $idObjInstitucional;
    public string $codigo;
    public string $objetivo;

    public function rules(): array
    {
        return [
            [['idObjInstitucional', 'codigo', 'objetivo'], 'required'],
            [['idObjInstitucional'], 'string', 'max' => 36],
            [['codigo'], 'match', 'pattern' => '/^\d{2}$/', 'message' => 'El código debe tener exactamente dos dígitos.'],
            [['objetivo'], 'string', 'min' => 2, 'max' => 500],
        ];
    }
}
