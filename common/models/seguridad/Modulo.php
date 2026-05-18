<?php

namespace common\models\seguridad;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use common\models\Estado;

/**
 * This is the model class for table "Modulos".
 *
 * @property string $IdModulo
 * @property string $Codigo
 * @property string $Nombre
 * @property string|null $RoutePrefix
 * @property string|null $DashboardRoute
 * @property string|null $Color
 * @property string|null $Icono
 * @property int|null $Orden
 * @property int|null $Visible
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $IdUsuario
 *
 * @property Usuario $idUsuario
 * @property Estado $codigoEstado
 * @property Menu[] $menus
 * @property UsuarioContextoActivo[] $usuarioContextoActivos
 * @property UsuarioModulo[] $usuarioModulos
 *
 */

class Modulo extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'seguridad.Modulos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['IdModulo', 'IdUsuario'], 'string', 'max' => 36],
            [['Codigo', 'Nombre', 'CodigoEstado', 'IdUsuario'], 'required'],
            [['Orden', 'Visible'], 'integer'],
            [['FechaHoraRegistro'], 'safe'],
            [['Codigo'], 'string', 'max' => 50],
            [['Nombre', 'RoutePrefix', 'Icono'], 'string', 'max' => 100],
            [['DashboardRoute'], 'string', 'max' => 300],
            [['Color'], 'string', 'max' => 20],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['IdModulo'], 'unique'],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['IdUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['IdUsuario' => 'IdUsuario']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'IdModulo' => 'Id Modulo',
            'Codigo' => 'Codigo',
            'Nombre' => 'Nombre',
            'RoutePrefix' => 'Route Prefix',
            'DashboardRoute' => 'Dashboard Route',
            'Color' => 'Color',
            'Icono' => 'Icono',
            'Orden' => 'Orden',
            'Visible' => 'Visible',
            'CodigoEstado' => 'Codigo Estado',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
            'IdUsuario' => 'Id Usuario',
        ];
    }

    /**
     * Gets a query for [[Menus]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getMenus(): ActiveQuery
    {
        return $this->hasMany(Menu::class, ['IdModulo' => 'IdModulo']);
    }

    /**
     * Gets query for [[UsuarioContextoActivos]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getUsuarioContextoActivos(): ActiveQuery
    {
        return $this->hasMany(UsuarioContextoActivo::class, ['IdModulo' => 'IdModulo']);
    }

    /**
     * Gets a query for [[UsuarioModulos]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getUsuarioModulos(): ActiveQuery
    {
        return $this->hasMany(UsuarioModulo::class, ['IdModulo' => 'IdModulo']);
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
     * Gets a query for [[CodigoEstado]].
     *
     * @return ActiveQuery
     */
    public function getCodigoEstado(): ActiveQuery
    {
        return $this->hasOne(Estado::class, ['CodigoEstado' => 'CodigoEstado']);
    }

}
