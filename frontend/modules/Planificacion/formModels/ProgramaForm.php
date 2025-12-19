<?php
namespace app\modules\Planificacion\formModels;

use yii\base\Model;

/**
 * @property string $codigo
 * @property string $descripcion
 */

class ProgramaForm extends Model
{
    public string $codigo;
    public string $descripcion;

    public function rules(): array
    {
        return [
            [['codigo', 'descripcion'], 'required'],
            ['codigo','match','pattern' => '/^\d{3}$/','message' => 'Debe contener exactamente 3 dígitos (ej: 023).'],
            [['descripcion'], 'string', 'max' => 500],
            [['codigo','descripcion'], 'trim'],
        ];
    }
}