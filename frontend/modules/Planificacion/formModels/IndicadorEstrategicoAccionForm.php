<?php

namespace app\modules\Planificacion\formModels;
use yii\base\Model;

/**
 *
  * @property string $idAccionEstrategica
 *
 */
class IndicadorEstrategicoAccionForm extends Model
{
    public string $idAccionEstrategica;

    public function rules(): array
    {
        return [
            [['idAccionEstrategica'], 'string', 'max' => 36],
            [['idAccionEstrategica'], 'required'],
        ];
    }

}