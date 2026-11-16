<?php

namespace app\modules\Planificacion\formModels;

use yii\base\Model;

/**
 * @property string $idIndicador
 * @property string $idDa
 */
class ProgramacionIndicadorDaForm extends Model
{
    public string $idIndicador = '';
    public string $idDa = '';

    public function rules(): array
    {
        return [
            [['idIndicador', 'idDa'], 'required'],
            [['idIndicador', 'idDa'], 'string', 'max' => 36],
        ];
    }
}
