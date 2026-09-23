<?php

namespace common\models\seguridad;

use app\modules\Planificacion\models\PeiGestion;
use app\modules\Planificacion\models\UnidadEjecutora;
use common\models\Estado;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property string $IdUsuarioUnidadMenuPermiso
 * @property string $IdUsuario
 * @property string $IdUnidadEjecutora
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
 */
class UsuarioUnidadMenuPermiso extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'seguridad.UsuarioUnidadMenuPermiso';
    }

    public function rules(): array
    {
        return [
            [['IdUsuarioUnidadMenuPermiso', 'IdUsuario', 'IdUnidadEjecutora', 'IdMenu', 'IdGestion', 'IdEstadoPoa', 'Usuario'], 'string', 'max' => 36],
            [['IdUsuario', 'IdUnidadEjecutora', 'IdMenu', 'IdGestion', 'IdEstadoPoa', 'CodigoEstado', 'Usuario'], 'required'],
            [['PuedeVer', 'PuedeCrear', 'PuedeEditar', 'PuedeEliminar'], 'integer'],
            [['FechaHoraRegistro'], 'safe'],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['IdUsuarioUnidadMenuPermiso'], 'unique'],
            [['IdUnidadEjecutora'], 'exist', 'skipOnError' => true, 'targetClass' => UnidadEjecutora::class, 'targetAttribute' => ['IdUnidadEjecutora' => 'IdUnidadEjecutora']],
            [['IdGestion'], 'exist', 'skipOnError' => true, 'targetClass' => PeiGestion::class, 'targetAttribute' => ['IdGestion' => 'IdGestion']],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['IdUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['IdUsuario' => 'IdUsuario']],
            [['Usuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['Usuario' => 'IdUsuario']],
            [['IdMenu'], 'exist', 'skipOnError' => true, 'targetClass' => Menu::class, 'targetAttribute' => ['IdMenu' => 'IdMenu']],
            [['IdEstadoPoa'], 'exist', 'skipOnError' => true, 'targetClass' => EstadosPoa::class, 'targetAttribute' => ['IdEstadoPoa' => 'IdEstadoPoa']],
        ];
    }

    public function getUnidadEjecutora(): ActiveQuery
    {
        return $this->hasOne(UnidadEjecutora::class, ['IdUnidadEjecutora' => 'IdUnidadEjecutora']);
    }

    public function getIdMenu(): ActiveQuery
    {
        return $this->hasOne(Menu::class, ['IdMenu' => 'IdMenu']);
    }

    public function getIdGestion(): ActiveQuery
    {
        return $this->hasOne(PeiGestion::class, ['IdGestion' => 'IdGestion']);
    }

    public function getIdEstadoPoa(): ActiveQuery
    {
        return $this->hasOne(EstadosPoa::class, ['IdEstadoPoa' => 'IdEstadoPoa']);
    }

    public function getIdUsuario(): ActiveQuery
    {
        return $this->hasOne(Usuario::class, ['IdUsuario' => 'IdUsuario']);
    }
}
