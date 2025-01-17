<?php

namespace common\models;

use app\models\CarrerasSede;
use app\models\Usuario;
use Yii;

/**
 * This is the model class for table "ConfiguracionesUsuariosCarreras".
 *
 * @property string $CodigoUsuario
 * @property int $CodigoCarrera
 * @property string $CodigoSede
 *
 * @property CarrerasSede $codigoCarrera
 * @property Usuario $codigoUsuario
 */
class ConfiguracionUsuarioCarrera extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ConfiguracionesUsuariosCarreras';
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
            [['CodigoUsuario', 'CodigoCarrera'], 'required'],
            [['CodigoCarrera'], 'integer'],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['CodigoSede'], 'string', 'max' => 2],
            [['CodigoCarrera', 'CodigoSede', 'CodigoUsuario'], 'unique', 'targetAttribute' => ['CodigoCarrera', 'CodigoSede', 'CodigoUsuario']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
            [['CodigoCarrera', 'CodigoSede'], 'exist', 'skipOnError' => true, 'targetClass' => CarrerasSede::class, 'targetAttribute' => ['CodigoCarrera' => 'CodigoCarrera', 'CodigoSede' => 'CodigoSede']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'CodigoUsuario' => 'Codigo Usuario',
            'CodigoCarrera' => 'Codigo Carrera',
            'CodigoSede' => 'Codigo Sede',
        ];
    }

    /**
     * Gets query for [[CodigoCarrera]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCodigoCarrera()
    {
        return $this->hasOne(CarrerasSede::class, ['CodigoCarrera' => 'CodigoCarrera', 'CodigoSede' => 'CodigoSede']);
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
}
