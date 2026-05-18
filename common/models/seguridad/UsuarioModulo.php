<?php

namespace common\models\seguridad;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use common\models\Estado;


/**
 * This is the model class for table "UsuarioModulo".
 *
 * @property string $IdUsuarioModulo
 * @property string $IdUsuario
 * @property string $IdModulo
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $Usuario
 *
 * @property Modulo $idModulo
 * @property Usuario $idUsuario
 *
 * @property Estado $codigoEstado
 * @property Usuario $usuario
 */
class UsuarioModulo extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'seguridad.UsuarioModulo';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['IdUsuarioModulo', 'IdUsuario', 'IdModulo', 'Usuario'], 'string', 'max' => 36],
            [['IdUsuario', 'IdModulo', 'CodigoEstado', 'Usuario'], 'required'],
            [['FechaHoraRegistro'], 'safe'],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['IdUsuarioModulo'], 'unique'],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['IdUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['IdUsuario' => 'IdUsuario']],
            [['Usuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['Usuario' => 'IdUsuario']],
            [['IdModulo'], 'exist', 'skipOnError' => true, 'targetClass' => Modulo::class, 'targetAttribute' => ['IdModulo' => 'IdModulo']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'IdUsuarioModulo' => 'Id Usuario Modulo',
            'IdUsuario' => 'Id Usuario',
            'IdModulo' => 'Id Modulo',
            'CodigoEstado' => 'Codigo Estado',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
            'Usuario' => 'Usuario',
        ];
    }

    /**
     * Gets query for [[IdModulo]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getIdModulo(): ActiveQuery
    {
        return $this->hasOne(Modulo::class, ['IdModulo' => 'IdModulo']);
    }

    /**
     * Gets query for [[IdUsuario]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getIdUsuario(): ActiveQuery
    {
        return $this->hasOne(Usuario::class, ['IdUsuario' => 'IdUsuario']);
    }

    /**
     * Gets query for [[Usuario]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getUsuario(): ActiveQuery
    {
        return $this->hasOne(Usuario::class, ['IdUsuario' => 'Usuario']);
    }

    /**
     * Gets a query for [[CodigoEstado]].
     *
     * @return ActiveQuery
     */
    public function getCodigoEstado(): ActiveQuery
    {
        return $this->hasOne(Estado::class, ['CodigoEstado' => 'CodigoEstado']);
    }
}
