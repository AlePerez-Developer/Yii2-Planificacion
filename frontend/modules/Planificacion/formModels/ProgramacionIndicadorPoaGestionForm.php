<?php

namespace app\modules\Planificacion\formModels;

use yii\base\Model;

class ProgramacionIndicadorPoaGestionForm extends Model
{
    public string $idObjEspecifico;
    public string $idLlavePresupuestaria;
    public string $idIndicadorPoa;
    public int $metaProgramada;

    public function rules(): array
    {
        return [
            [['idObjEspecifico', 'idLlavePresupuestaria', 'idIndicadorPoa', 'metaProgramada'], 'required'],
            [['idObjEspecifico', 'idLlavePresupuestaria', 'idIndicadorPoa'], 'string', 'max' => 36],
            [['metaProgramada'], 'integer', 'min' => 0],
        ];
    }
}
