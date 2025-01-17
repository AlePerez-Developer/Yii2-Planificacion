<?php

namespace common\models;

use app\models\Role;
use app\models\Usuario;
use Yii;

/**
 * This is the model class for table "UsuariosRoles".
 *
 * @property string $CodigoUsuario
 * @property int $IdRol
 *
 * @property Usuario $codigoUsuario
 * @property Role $idRol
 */
class UsuarioRol extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'UsuariosRoles';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('dbAcademica');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['CodigoUsuario', 'IdRol'], 'required'],
            [['IdRol'], 'integer'],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['CodigoUsuario', 'IdRol'], 'unique', 'targetAttribute' => ['CodigoUsuario', 'IdRol']],
            [['IdRol'], 'exist', 'skipOnError' => true, 'targetClass' => Role::class, 'targetAttribute' => ['IdRol' => 'IdRol']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'CodigoUsuario' => 'Codigo Usuario',
            'IdRol' => 'Id Rol',
        ];
    }

    /**
     * Gets query for [[CodigoUsuario]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCodigoUsuario()
    {
        return $this->hasOne(Usuario::class, ['CodigoUsuario' => 'CodigoUsuario']);
    }

    /**
     * Gets query for [[IdRol]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIdRol()
    {
        return $this->hasOne(Role::class, ['IdRol' => 'IdRol']);
    }
}
