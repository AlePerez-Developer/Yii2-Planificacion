<?php

namespace app\modules\Planificacion\formModels;

use yii\base\Model;

/**
 * @property string $idObjEstrategico
 * @property string $codigo
 * @property string $objetivo
 * @property string $producto
 * @property string $idGestion
 */

class ObjetivoInstitucionalForm extends Model
{
    public string $idObjEstrategico;
    public string $codigo;
    public string $objetivo;
    public string $producto;
    public string $idGestion;

    public function rules(): array
    {
        return [
            [['idObjEstrategico', 'codigo', 'objetivo', 'producto', 'idGestion'], 'required'],
            [['idObjEstrategico','idGestion'], 'string', 'max' => 36],
            [['codigo'], 'match', 'pattern' => '/^\d{2}$/', 'message' => 'El código debe tener exactamente 2 dígitos.'],
            [['objetivo', 'producto'], 'string', 'min' => 2, 'max' => 500]
        ];
    }
}
