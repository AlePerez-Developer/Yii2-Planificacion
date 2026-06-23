<?php

namespace common\models\seguridad;

use app\modules\Planificacion\models\LlavePresupuestaria;
use app\modules\Planificacion\models\PeiGestion;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use common\models\Estado;

/**
 * This is the model class for table "UsuarioContextoActivo".
 *
 * @property string $IdUsuario
 * @property string|null $IdModulo
 * @property string|null $IdGestion
 * @property string|null $IdEstadoPoa
 * @property string|null $IdLlavePresupuestaria
 * @property string|null $FechaHoraActualizacion
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $Usuario
 *
 * @property LlavePresupuestaria $LlavPresupuestaria
 * @property PeiGestion $Gestion
 * @property EstadosPoa $EstadoPoa
 * @property Modulo $Modulo
 * @property Usuario $idUsuario
 * @property Usuario $usuario
 * @property Estado $codigoEstado
 */
class UsuarioContextoActivo extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'seguridad.UsuarioContextoActivo';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['IdUsuario', 'CodigoEstado', 'Usuario'], 'required'],
            [['IdUsuario', 'IdModulo', 'IdGestion', 'IdEstadoPoa', 'IdLlavePresupuestaria', 'Usuario'], 'string', 'max' => 36],
            [['FechaHoraActualizacion', 'FechaHoraRegistro'], 'safe'],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['IdUsuario'], 'unique'],
            [['IdLlavePresupuestaria'], 'exist', 'skipOnError' => true, 'targetClass' => LlavePresupuestaria::class, 'targetAttribute' => ['IdLlavePresupuestaria' => 'IdLlavePresupuestaria']],
            [['IdGestion'], 'exist', 'skipOnError' => true, 'targetClass' => PeiGestion::class, 'targetAttribute' => ['IdGestion' => 'IdGestion']],
            [['IdModulo'], 'exist', 'skipOnError' => true, 'targetClass' => Modulo::class, 'targetAttribute' => ['IdModulo' => 'IdModulo']],
            [['IdEstadoPoa'], 'exist', 'skipOnError' => true, 'targetClass' => EstadosPoa::class, 'targetAttribute' => ['IdEstadoPoa' => 'IdEstadoPoa']],
            [['IdUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['IdUsuario' => 'IdUsuario']],
            [['Usuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['Usuario' => 'IdUsuario']],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'IdUsuario' => 'Id Usuario',
            'IdModulo' => 'Id Modulo',
            'IdGestion' => 'Id Gestion',
            'IdEstadoPoa' => 'Id Estado Poa',
            'IdLlavePresupuestaria' => 'Id Llave Presupuestaria',
            'FechaHoraActualizacion' => 'Fecha Hora Actualizacion',
            'CodigoEstado' => 'Codigo Estado',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
            'Usuario' => 'Usuario',
        ];
    }

    /**
     * Gets a query for [[IdLlavePresupuestaria]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getLlavePresupuestaria(): ActiveQuery
    {
        return $this->hasOne(LlavePresupuestaria::class, ['IdLlavePresupuestaria' => 'IdLlavePresupuestaria']);
    }

    /**
     * Gets a query for [[IdGestion]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getGestion(): ActiveQuery
    {
        return $this->hasOne(PeiGestion::class, ['IdGestion' => 'IdGestion']);
    }

    /**
     * Gets a query for [[IdEstadoPoa]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getEstadoPoa(): ActiveQuery
    {
        return $this->hasOne(EstadosPoa::class, ['IdEstadoPoa' => 'IdEstadoPoa']);
    }

    /**
     * Gets query for [[IdModulo]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getModulo(): ActiveQuery
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
