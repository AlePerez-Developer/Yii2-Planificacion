<?php
namespace common\components;

use common\models\seguridad\UsuarioContextoActivo;
use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\web\User;

class UserContext
{
    public function usuario(): User|IdentityInterface|null
    {
        return Yii::$app->user->identity;
    }

    public function contexto(): array|ActiveRecord|null
    {
        if (Yii::$app->user->isGuest) {
            return null;
        }

        return UsuarioContextoActivo::find()
            ->where([
                'IdUsuario' => Yii::$app->user->id
            ])
            ->one();
    }

    public function moduloActivo()
    {
        $contexto = $this->contexto();

        return $contexto?->modulo;
    }

    public function colorModulo(): string
    {
        $modulo = $this->moduloActivo();

        if (!$modulo) {
            return '#ffffff';
        }

        return $modulo->Color ?: '#ffffff';
    }
}
