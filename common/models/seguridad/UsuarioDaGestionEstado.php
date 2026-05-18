<?php

namespace common\models\seguridad;

use app\modules\Planificacion\models\Da;
use app\modules\Planificacion\models\PeiGestion;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use common\models\Estado;

/**
 * This is the model class for table "UsuarioDaGestionEstado".
 *
 * @property string $IdUsuarioDaGestionEstado
 * @property string $IdUsuario
 * @property string $IdDa
 * @property string $IdGestion
 * @property string $IdEstadoPoa
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $Usuario
 *
 * @property Da $idDa
 * @property PeiGestion $idGestion
 * @property EstadosPoa $idEstadoPoa
 * @property Usuario $idUsuario
 * @property Usuario $usuario
 */
class UsuarioDaGestionEstado extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'seguridad.UsuarioDaGestionEstado';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['IdUsuarioDaGestionEstado', 'IdUsuario', 'IdDa', 'IdGestion', 'IdEstadoPoa', 'Usuario'], 'string', 'max' => 36],
            [['IdUsuario', 'IdDa', 'IdGestion', 'IdEstadoPoa', 'CodigoEstado', 'Usuario'], 'required'],
            [['FechaHoraRegistro'], 'safe'],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['IdUsuarioDaGestionEstado'], 'unique'],
            [['IdDa'], 'exist', 'skipOnError' => true, 'targetClass' => Da::class, 'targetAttribute' => ['IdDa' => 'IdDa']],
            [['IdGestion'], 'exist', 'skipOnError' => true, 'targetClass' => PeiGestion::class, 'targetAttribute' => ['IdGestion' => 'IdGestion']],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['IdUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['IdUsuario' => 'IdUsuario']],
            [['Usuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['Usuario' => 'IdUsuario']],
            [['IdEstadoPoa'], 'exist', 'skipOnError' => true, 'targetClass' => EstadosPoa::class, 'targetAttribute' => ['IdEstadoPoa' => 'IdEstadoPoa']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'IdUsuarioDaGestionEstado' => 'Id Usuario Da Gestion Estado',
            'IdUsuario' => 'Id Usuario',
            'IdDa' => 'Id Da',
            'IdGestion' => 'Id Gestion',
            'IdEstadoPoa' => 'Id Estado Poa',
            'CodigoEstado' => 'Codigo Estado',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
            'Usuario' => 'Usuario',
        ];
    }

    /**
     * Gets a query for [[IdDa]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getIdDa(): ActiveQuery
    {
        return $this->hasOne(Da::class, ['IdDa' => 'IdDa']);
    }

    /**
     * Gets a query for [[IdGestion]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getIdGestion(): ActiveQuery
    {
        return $this->hasOne(PeiGestion::class, ['IdGestion' => 'IdGestion']);
    }


    /**
     * Gets a query for [[IdEstadoPoa]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getIdEstadoPoa(): ActiveQuery
    {
        return $this->hasOne(EstadosPoa::class, ['IdEstadoPoa' => 'IdEstadoPoa']);
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
