<?php
namespace app\modules\Planificacion\formModels;

use yii\base\Model;

/**
 * @property string $da
 * @property string $descripcion
 */

class DaForm extends Model
{
    public string $da;
    public string $descripcion;

    public function rules(): array
    {
        return [
            [['da', 'descripcion'], 'required'],
            ['da','match','pattern' => '/^\d{2}$/','message' => 'Debe contener exactamente 2 dígitos (ej: 09).'],
            [['descripcion'], 'string', 'max' => 500],
            [['da','descripcion'], 'trim'],
        ];
    }
}
