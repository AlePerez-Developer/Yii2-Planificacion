<?php

namespace app\modules\Planificacion\formModels;

use app\modules\Planificacion\models\FODAUnidad;
use yii\base\Model;

class FodaUnidadForm extends Model
{
    public string $descripcion = '';
    public string $tipo = '';
    public string $incidencia = '';

    public function rules(): array
    {
        return [
            [['descripcion', 'tipo', 'incidencia'], 'required'],
            [['descripcion'], 'string', 'min' => 2, 'max' => 500],
            [['tipo'], 'in', 'range' => array_values(FODAUnidad::tipos())],
            [['incidencia'], 'in', 'range' => array_values(FODAUnidad::incidencias())],
        ];
    }
}
