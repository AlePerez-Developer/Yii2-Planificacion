<?php
namespace app\modules\Planificacion\formModels;

use yii\base\Model;

/**
 * This is the model class for table "PEIs".
 *
 * @property string $codigoObjetivo
 * @property string $objetivo
 * @property int $CodigoPei
 */

class ObjetivoEstrategicoForm extends Model
{
    public string $codigoObjetivo;
    public string $objetivo;
    public int $CodigoPei;

    public function rules(): array
    {
        return [
            [['codigoObjetivo', 'objetivo'], 'required'],
            [['codigoObjetivo'], 'string', 'max' => 3],
            [['objetivo'], 'string', 'max' => 450],

        ];
    }
}
