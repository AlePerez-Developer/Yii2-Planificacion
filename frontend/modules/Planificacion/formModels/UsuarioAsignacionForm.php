<?php

namespace app\modules\Planificacion\formModels;

use yii\base\Model;

class UsuarioAsignacionForm extends Model
{
    public string $idUnidadEjecutora = '';
    public string $idGestion = '';
    public string $idEstadoPoa = '';

    public function rules(): array
    {
        return [
            [['idUnidadEjecutora', 'idGestion', 'idEstadoPoa'], 'required'],
            [['idUnidadEjecutora', 'idGestion', 'idEstadoPoa'], 'string', 'max' => 36],
        ];
    }
}
