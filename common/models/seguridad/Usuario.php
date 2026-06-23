<?php

namespace common\models\seguridad;
use common\models\seguridad\EstadosPoa;
use app\modules\Planificacion\models\LlavePresupuestaria;
use app\modules\Planificacion\models\PeiGestion;
use common\models\Estado;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use common\models\Persona;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "Usuarios".
 *
 * @property string $IdUsuario
 * @property string $IdPersona
 * @property string $CodigoUsuario
 * @property string|null $Nick
 * @property string|null $TokenPortal
 * @property string|null $UltimoAcceso
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 *

 */
class Usuario extends ActiveRecord implements IdentityInterface
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'seguridad.Usuarios';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['IdUsuario'], 'string'],
            [['IdPersona', 'CodigoUsuario', 'CodigoEstado'], 'required'],
            [['UltimoAcceso', 'FechaHoraRegistro'], 'safe'],
            [['IdPersona'], 'string', 'max' => 15],
            [['CodigoUsuario'], 'string', 'max' => 50],
            [['Nick'], 'string', 'max' => 100],
            [['TokenPortal'], 'string', 'max' => 40],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['IdUsuario'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'IdUsuario' => 'Id Usuario',
            'IdPersona' => 'Id Persona',
            'CodigoUsuario' => 'Codigo Usuario',
            'Nick' => 'Nick',
            'TokenPortal' => 'TokenPortal',
            'UltimoAcceso' => 'Ultimo Acceso',
            'CodigoEstado' => 'Codigo Estado',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
        ];
    }

    /**
     * Gets a query for [[Persona]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getPersona(): ActiveQuery
    {
        return $this->hasOne(Persona::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets a query for [[UsuarioModulo]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getUsuarioModulos(): ActiveQuery
    {
        return $this->hasMany(
            UsuarioModulo::class,
            ['IdUsuario' => 'IdUsuario']
        );
    }

    public function getModulosPermitidos(): array
    {
        return Modulo::find()
            ->where(['CodigoEstado' => 'V'])
            ->orderBy('Orden')
            ->all();
    }

    public function getGestionesPermitidas()
    {
        return PeiGestion::find()
            ->alias('g')
            ->innerJoin(
                'seguridad.UsuarioDaGestionEstado uge',
                'uge.IdGestion = g.IdGestion'
            )
            ->where([
                'uge.IdUsuario' => $this->IdUsuario,
                'uge.CodigoEstado' => Estado::ESTADO_VIGENTE,
                'g.CodigoEstado' => Estado::ESTADO_VIGENTE,
            ])
            ->orderBy(['g.Gestion' => SORT_DESC])
            ->all();
    }

    public function getEstadosPoaPermitidos($idGestion)
    {
        return EstadosPoa::find()
            ->alias('e')
            ->innerJoin(
                'seguridad.UsuarioDaGestionEstado uge',
                'uge.IdEstadoPoa = e.IdEstadoPoa'
            )
            ->where([
                'uge.IdUsuario' => $this->IdUsuario,
                'uge.IdGestion' => $idGestion,
                'uge.CodigoEstado' => Estado::ESTADO_VIGENTE,
                'e.CodigoEstado' => Estado::ESTADO_VIGENTE,
            ])
            ->orderBy(['e.Codigo' => SORT_ASC])
            ->all();
    }

    public function getLlavesPermitidas($idGestion, $idEstadoPoa)
    {
        return LlavePresupuestaria::find()
            ->alias('l')
            ->innerJoin(
                'seguridad.UsuarioDaGestionEstado uge',
                'uge.IdDa = l.IdDa'
            )
            ->where([
                'uge.IdUsuario' => $this->IdUsuario,
                'uge.IdGestion' => $idGestion,
                'uge.IdEstadoPoa' => $idEstadoPoa,
                'uge.CodigoEstado' => Estado::ESTADO_VIGENTE,
                'l.CodigoEstado' => Estado::ESTADO_VIGENTE,
            ])
            ->orderBy(['l.Llave' => SORT_ASC])
            ->all();
    }

    /**
     * Finds an identity by the given ID.
     * @param string $id the ID to be looked for
     * @return IdentityInterface|null the identity object that matches the given ID.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentity($id): ?IdentityInterface
    {
        return static::findOne($id);
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
    public static function findIdentityByAccessToken($token, $type = null): ?IdentityInterface
    {
        return static::findOne(['TokenPortal' => $token]);
    }

    /**
     * Returns an ID that can uniquely identify a user identity.
     * @return string|int an ID that uniquely identifies a user identity.
     */
    public function getId(): int|string
    {
        return $this->IdUsuario;
    }

    /**
     * Returns a key that can be used to check the validity of a given identity ID.
     *
     * The key should be unique for each user, and should be persistent
     * so that it can be used to check the validity of the user identity.
     *
     * The space of such keys should be big enough to defeat potential identity attacks.
     *
     * The returned key is used to validate session and auto-login (if [[User::enableAutoLogin]] is enabled).
     *
     * Make sure to invalidate earlier issued authKeys when you implement force user logout, password change and
     * other scenarios that require forceful access revocation for old sessions.
     *
     * @return string|null a key that is used to check the validity of a given identity ID.
     * @see validateAuthKey()
     */
    public function getAuthKey(): ?string
    {
        return $this->TokenPortal;
    }

    /**
     * Validates the given auth key.
     *
     * @param string $authKey the given auth key
     * @return bool|null whether the given auth key is valid.
     * @see getAuthKey()
     */
    public function validateAuthKey($authKey): ?bool
    {
        return $this->TokenPortal === $authKey;
    }

    /**
     * @noinspection PhpUnused
    */
    public static function findByCu($cu): array|ActiveRecord|null
    {
        return self::find()
            ->where(['TokenPortal' => $cu])
            ->one();
    }
}
