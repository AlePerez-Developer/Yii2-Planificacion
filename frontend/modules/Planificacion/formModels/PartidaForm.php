<?php

namespace app\modules\Planificacion\formModels;

use yii\base\Model;

class PartidaForm extends Model
{
    public string $idFuente;
    public string $idOrganismo;

    public function rules(): array
    {
        return [
            [['idFuente', 'idOrganismo'], 'required'],
            [['idFuente', 'idOrganismo'], 'string', 'max' => 36],
        ];
    }
}
