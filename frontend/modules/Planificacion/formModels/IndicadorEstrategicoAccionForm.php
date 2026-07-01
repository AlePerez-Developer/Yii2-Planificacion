<?php

namespace app\modules\Planificacion\formModels;
use yii\base\Model;

/**
 *
 * @property string $accionDescripcion
 * @property string $idAccionEstrategica
 *
 */
class IndicadorEstrategicoAccionForm extends Model
{
    public string $accionDescripcion;
    public string $idAccionEstrategica;

    public function rules(): array
    {
        return [
            [['idAccionEstrategica'], 'string', 'max' => 36],
            [['accionDescripcion', 'idAccionEstrategica'], 'required'],
            [['accionDescripcion'], 'string', 'min' => 2, 'max' => 500],
        ];
    }

}