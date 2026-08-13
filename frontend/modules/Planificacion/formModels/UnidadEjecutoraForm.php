<?php

namespace app\modules\Planificacion\formModels;

use yii\base\Model;

/**
 * @property string $idDa
 * @property string $ue
 * @property string $descripcion
 */

class UnidadEjecutoraForm extends Model
{
    public string $idDa;
    public string $ue;
    public string $descripcion;

    public function rules(): array
    {
        return [
            [['idDa','ue', 'descripcion'], 'required'],
            [['idDa', 'ue', 'descripcion'], 'string'],
            [['idDa'], 'string', 'max' => 36],
            ['ue','match','pattern' => '/^\d{3}$/','message' => 'Debe contener exactamente 3 dígitos (ej: 023).'],
            [['descripcion'], 'string', 'max' => 500],
            [['ue','descripcion'], 'trim'],
        ];
    }

}