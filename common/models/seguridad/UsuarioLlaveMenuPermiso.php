<?php

namespace common\models\seguridad;

use app\modules\Planificacion\models\LlavePresupuestaria;
use app\modules\Planificacion\models\PeiGestion;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use common\models\Estado;

/**
 * This is the model class for table "UsuarioLlaveMenuPermiso".
 *
 * @property string $IdUsuarioLlaveMenuPermiso
 * @property string $IdUsuario
 * @property string $IdLlavePresupuestaria
 * @property string $IdMenu
 * @property string $IdGestion
 * @property string $IdEstadoPoa
 * @property int|null $PuedeVer
 * @property int|null $PuedeCrear
 * @property int|null $PuedeEditar
 * @property int|null $PuedeEliminar
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $Usuario
 *
 * @property LlavePresupuestaria $idLlavPresupuestaria
 * @property PeiGestion $idGestion
 * @property EstadosPoa $idEstadoPoa
 * @property Menu $idMenu
 * @property Usuario $idUsuario
 * @property Usuario $usuario
 * @property Estado $codigoEstado
 */
class UsuarioLlaveMenuPermiso extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'seguridad.UsuarioLlaveMenuPermiso';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['IdUsuarioLlaveMenuPermiso', 'IdUsuario', 'IdLlavePresupuestaria', 'IdMenu', 'IdGestion', 'IdEstadoPoa', 'Usuario'], 'string', 'max' => 36],
            [['IdUsuario', 'IdLlavePresupuestaria', 'IdMenu', 'IdGestion', 'IdEstadoPoa', 'CodigoEstado', 'Usuario'], 'required'],
            [['PuedeVer', 'PuedeCrear', 'PuedeEditar', 'PuedeEliminar'], 'integer'],
            [['FechaHoraRegistro'], 'safe'],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['IdUsuarioLlaveMenuPermiso'], 'unique'],
            [['IdLlavePresupuestaria'], 'exist', 'skipOnError' => true, 'targetClass' => LlavePresupuestaria::class, 'targetAttribute' => ['IdLlavePresupuestaria' => 'IdLlavePresupuestaria']],
            [['IdGestion'], 'exist', 'skipOnError' => true, 'targetClass' => PeiGestion::class, 'targetAttribute' => ['IdGestion' => 'IdGestion']],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['IdUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['IdUsuario' => 'IdUsuario']],
            [['Usuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['Usuario' => 'IdUsuario']],
            [['IdMenu'], 'exist', 'skipOnError' => true, 'targetClass' => Menu::class, 'targetAttribute' => ['IdMenu' => 'IdMenu']],
            [['IdEstadoPoa'], 'exist', 'skipOnError' => true, 'targetClass' => EstadosPoa::class, 'targetAttribute' => ['IdEstadoPoa' => 'IdEstadoPoa']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'IdUsuarioLlaveMenuPermiso' => 'Id Usuario Llave Menu Permiso',
            'IdUsuario' => 'Id Usuario',
            'IdLlavePresupuestaria' => 'Id Llave Presupuestaria',
            'IdMenu' => 'Id Menu',
            'IdGestion' => 'Id Gestion',
            'IdEstadoPoa' => 'Id Estado Poa',
            'PuedeVer' => 'Puede Ver',
            'PuedeCrear' => 'Puede Crear',
            'PuedeEditar' => 'Puede Editar',
            'PuedeEliminar' => 'Puede Eliminar',
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
    public function getIdLlavePresupuestaria(): ActiveQuery
    {
        return $this->hasOne(LlavePresupuestaria::class, ['IdLlavePresupuestaria' => 'IdLlavePresupuestaria']);
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
     * Gets query for [[IdMenu]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getIdMenu(): ActiveQuery
    {
        return $this->hasOne(Menu::class, ['IdMenu' => 'IdMenu']);
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
