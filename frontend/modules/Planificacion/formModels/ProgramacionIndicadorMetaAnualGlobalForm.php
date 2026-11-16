<?php

namespace app\modules\Planificacion\formModels;

use yii\base\Model;

/**
 * @property string $idIndicador
 * @property string $idGestion
 * @property float|int|string $meta
 */
class ProgramacionIndicadorMetaAnualGlobalForm extends Model
{
    public string $idIndicador = '';
    public string $idGestion = '';
    public $meta = 0;

    public function rules(): array
    {
        return [
            [['idIndicador', 'idGestion', 'meta'], 'required'],
            [['idIndicador', 'idGestion'], 'string', 'max' => 36],
            [['meta'], 'number', 'min' => 0],
        ];
    }
}
