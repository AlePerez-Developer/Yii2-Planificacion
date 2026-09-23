<?php

namespace common\models\seguridad;

use app\modules\Planificacion\models\PeiGestion;
use app\modules\Planificacion\models\UnidadEjecutora;
use common\models\Estado;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property string $IdUsuarioUnidadGestionEstado
 * @property string $IdUsuario
 * @property string $IdUnidadEjecutora
 * @property string $IdGestion
 * @property string $IdEstadoPoa
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $Usuario
 */
class UsuarioUnidadGestionEstado extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'seguridad.UsuarioUnidadGestionEstado';
    }

    public function rules(): array
    {
        return [
            [['IdUsuarioUnidadGestionEstado', 'IdUsuario', 'IdUnidadEjecutora', 'IdGestion', 'IdEstadoPoa', 'Usuario'], 'string', 'max' => 36],
            [['IdUsuario', 'IdUnidadEjecutora', 'IdGestion', 'IdEstadoPoa', 'CodigoEstado', 'Usuario'], 'required'],
            [['FechaHoraRegistro'], 'safe'],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['IdUsuarioUnidadGestionEstado'], 'unique'],
            [['IdUnidadEjecutora'], 'exist', 'skipOnError' => true, 'targetClass' => UnidadEjecutora::class, 'targetAttribute' => ['IdUnidadEjecutora' => 'IdUnidadEjecutora']],
            [['IdGestion'], 'exist', 'skipOnError' => true, 'targetClass' => PeiGestion::class, 'targetAttribute' => ['IdGestion' => 'IdGestion']],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['IdUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['IdUsuario' => 'IdUsuario']],
            [['Usuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['Usuario' => 'IdUsuario']],
            [['IdEstadoPoa'], 'exist', 'skipOnError' => true, 'targetClass' => EstadosPoa::class, 'targetAttribute' => ['IdEstadoPoa' => 'IdEstadoPoa']],
        ];
    }

    public function getUnidadEjecutora(): ActiveQuery
    {
        return $this->hasOne(UnidadEjecutora::class, ['IdUnidadEjecutora' => 'IdUnidadEjecutora']);
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
