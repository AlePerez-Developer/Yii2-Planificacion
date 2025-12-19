<?php

namespace app\modules\Planificacion\formModels;

use yii\base\Model;

/**
 *
 * @property string $idPrograma
 * @property string $codigo
 * @property string $descripcion
 */

class ActividadForm extends Model
{
    public string $idPrograma;
    public string $codigo;
    public string $descripcion;

    public function rules(): array
    {
        return [
            [['idPrograma', 'codigo', 'descripcion'], 'required'],
            ['codigo','match','pattern' => '/^\d{3}$/','message' => 'Debe contener exactamente 3 dígitos (ej: 023).'],
            [['descripcion'], 'string', 'max' => 500],
            [['idPrograma'], 'string', 'max' => 36],
            [['codigo', 'descripcion'], 'trim'],
        ];
    }
}
