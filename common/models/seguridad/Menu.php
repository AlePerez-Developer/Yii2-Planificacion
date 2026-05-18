<?php

namespace common\models\seguridad;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use common\models\Estado;

/**
 * This is the model class for table "Menus".
 *
 * @property string $IdMenu
 * @property string $IdModulo
 * @property string|null $IdMenuPadre
 * @property string|null $Nombre
 * @property string|null $Ruta
 * @property string|null $Icono
 * @property int|null $Orden
 * @property int|null $Visible
 * @property string|null $CodigoPermiso
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $IdUsuario
 *
 * @property Menu $idMenuPadre
 * @property Modulo $idModulo
 * @property Usuario $idUsuario
 * @property Estado $codigoEstado
 * @property Menu[] $menus
 * @property UsuarioLlaveMenuPermiso[] $usuarioLlaveMenuPermisos
 *
 */

class Menu extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'seguridad.Menus';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['IdMenu', 'IdModulo', 'IdMenuPadre', 'IdUsuario'], 'string', 'max' => 36],
            [['IdModulo', 'CodigoEstado', 'IdUsuario'], 'required'],
            [['Orden', 'Visible'], 'integer'],
            [['FechaHoraRegistro'], 'safe'],
            [['Nombre', 'CodigoPermiso'], 'string', 'max' => 150],
            [['Ruta'], 'string', 'max' => 300],
            [['Icono'], 'string', 'max' => 100],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['IdMenu'], 'unique'],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['IdUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['IdUsuario' => 'IdUsuario']],
            [['IdModulo'], 'exist', 'skipOnError' => true, 'targetClass' => Modulo::class, 'targetAttribute' => ['IdModulo' => 'IdModulo']],
            [['IdMenuPadre'], 'exist', 'skipOnError' => true, 'targetClass' => Menu::class, 'targetAttribute' => ['IdMenuPadre' => 'IdMenu']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'IdMenu' => 'Id Menu',
            'IdModulo' => 'Id Modulo',
            'IdMenuPadre' => 'Id Menu Padre',
            'Nombre' => 'Nombre',
            'Ruta' => 'Ruta',
            'Icono' => 'Icono',
            'Orden' => 'Orden',
            'Visible' => 'Visible',
            'CodigoPermiso' => 'Codigo Permiso',
            'CodigoEstado' => 'Codigo Estado',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
            'IdUsuario' => 'Id Usuario',
        ];
    }

    /**
     * Gets a query for [[IdMenuPadre]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getIdMenuPadre(): ActiveQuery
    {
        return $this->hasOne(Menu::class, ['IdMenu' => 'IdMenuPadre']);
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
     * Gets a query for [[CodigoEstado]].
     *
     * @return ActiveQuery
     */
    public function getCodigoEstado(): ActiveQuery
    {
        return $this->hasOne(Estado::class, ['CodigoEstado' => 'CodigoEstado']);
    }

    /**
     * Gets a query for [[Menus]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getMenus(): ActiveQuery
    {
        return $this->hasMany(Menu::class, ['IdMenuPadre' => 'IdMenu']);
    }

    /**
     * Gets a query for [[UsuarioLlaveMenuPermisos]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getUsuarioLlaveMenuPermisos(): ActiveQuery
    {
        return $this->hasMany(UsuarioLlaveMenuPermiso::class, ['IdMenu' => 'IdMenu']);
    }
}
