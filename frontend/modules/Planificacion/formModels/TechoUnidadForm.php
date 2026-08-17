<?php

namespace app\modules\Planificacion\formModels;

use yii\base\Model;

class TechoUnidadForm extends Model
{
    public string $idLlavePresupuestaria;
    public int $techo;

    public function rules(): array
    {
        return [
            [['idLlavePresupuestaria', 'techo'], 'required'],
            [['idLlavePresupuestaria'], 'string', 'max' => 36],
            [['techo'], 'integer', 'min' => 1],
        ];
    }
}
