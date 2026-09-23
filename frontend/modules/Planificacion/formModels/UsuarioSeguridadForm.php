<?php

namespace app\modules\Planificacion\formModels;

use yii\base\Model;

class UsuarioSeguridadForm extends Model
{
    public string $idPersona = '';
    public string $codigoUsuario = '';
    public string $nick = '';
    public string $tokenPortal = '';

    public function rules(): array
    {
        return [
            [['idPersona', 'codigoUsuario'], 'required'],
            [['idPersona'], 'string', 'max' => 15],
            [['codigoUsuario'], 'string', 'max' => 50],
            [['nick'], 'string', 'max' => 100],
            [['tokenPortal'], 'string', 'max' => 40],
        ];
    }
}
