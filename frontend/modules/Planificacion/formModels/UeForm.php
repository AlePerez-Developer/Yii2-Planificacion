<?php
namespace app\modules\Planificacion\formModels;

use yii\base\Model;

/**
 * @property string $ue
 * @property string $descripcion
 */

class UeForm extends Model
{
    public string $ue;
    public string $descripcion;

    public function rules(): array
    {
        return [
            [['ue', 'descripcion'], 'required'],
            ['ue','match','pattern' => '/^\d{3}$/','message' => 'Debe contener exactamente 3 dígitos (ej: 023).'],
            [['descripcion'], 'string', 'max' => 500],
            [['ue','descripcion'], 'trim'],
        ];
    }
}
