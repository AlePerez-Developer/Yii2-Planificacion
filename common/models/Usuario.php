<?php

namespace common\models;

use yii\web\IdentityInterface;
use Yii;

/**
 *
 * @property string $CodigoUsuario
 */


class Usuario extends \yii\db\ActiveRecord implements IdentityInterface
{
    public static function tableName()
    {
        return 'Usuarios';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('dbAcademica');
    }

    /**
     * Finds an identity by the given ID.
     * @param string|int $id the ID to be looked for
     * @return IdentityInterface|null the identity object that matches the given ID.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentity($id)
    {
        return static::findOne(['CodigoUsuario' => $id]);
    }

    /**
     * Finds an identity by the given token.
     * @param mixed $token the token to be looked for
     * @param mixed $type the type of the token. The value of this parameter depends on the implementation.
     * For example, [[\yii\filters\auth\HttpBearerAuth]] will set this parameter to be `yii\filters\auth\HttpBearerAuth`.
     * @return IdentityInterface|null the identity object that matches the given token.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return null;
    }

    /**
     * Returns an ID that can uniquely identify a user identity.
     * @return string|int an ID that uniquely identifies a user identity.
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * Returns a key that can be used to check the validity of a given identity ID.
     *
     * The key should be unique for each individual user, and should be persistent
     * so that it can be used to check the validity of the user identity.
     *
     * The space of such keys should be big enough to defeat potential identity attacks.
     *
     * This is required if [[User::enableAutoLogin]] is enabled. The returned key will be stored on the
     * client side as a cookie and will be used to authenticate user even if PHP session has been expired.
     *
     * Make sure to invalidate earlier issued authKeys when you implement force user logout, password change and
     * other scenarios, that require forceful access revocation for old sessions.
     *
     * @return string a key that is used to check the validity of a given identity ID.
     * @see validateAuthKey()
     */
    public function getAuthKey()
    {
        return $this->Llave;
    }

    /**
     * Validates the given auth key.
     *
     * This is required if [[User::enableAutoLogin]] is enabled.
     * @param string $authKey the given auth key
     * @return bool whether the given auth key is valid.
     * @see getAuthKey()
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    public function getPersona()
    {
        return $this->hasOne(Persona::class, ['IdPersona' => 'IdPersona']);
    }

    public function getRoles()
    {
        return $this->hasMany(UsuarioRol::class, ['CodigoUsuario' => 'CodigoUsuario'])->asArray();
    }

    public function getGestion(){
        return date('Y') - 1;
    }

    public function getEsDirector(){
        $flag = false;
        foreach ($this->roles as $rol){
            if($rol['IdRol'] === Yii::$app->params['ROL_ES_DIRECTOR']){
                $flag = true;
                break;
            }
        }
        return $flag;
    }

    public function getEsDecano(){
        $flag = false;
        foreach ($this->roles as $rol){
            if($rol['IdRol'] === Yii::$app->params['ROL_ES_DECANO']){
                $flag = true;
                break;
            }
        }
        return $flag;
    }

    public function getEsRector(){
        $flag = false;
        foreach ($this->roles as $rol){
            if($rol['IdRol'] === Yii::$app->params['ROL_ES_RECTOR']){
                $flag = true;
                break;
            }
        }
        return $flag;
    }
}
