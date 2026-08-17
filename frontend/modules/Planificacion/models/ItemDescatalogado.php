<?php

namespace app\modules\Planificacion\models;

use common\models\Estado;
use common\models\seguridad\EstadosPoa;
use common\models\Usuario;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

class ItemDescatalogado extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'ItemDescatalogado';
    }

    public function rules(): array
    {
        return [
            [['Descripcion'], 'default', 'value' => null],
            [['IdOperacion', 'IdEstadoPoa', 'IdGestion', 'IdGasto', 'IdFuente', 'IdOrganismo', 'cantidad', 'Precio', 'formulario', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['IdItemDescatalogado', 'IdOperacion', 'IdEstadoPoa', 'IdGestion'], 'string', 'max' => 36],
            [['IdGasto', 'cantidad'], 'integer'],
            [['cantidad'], 'integer', 'min' => 1],
            [['Precio'], 'number', 'min' => 0.01],
            [['Precio'], 'match', 'pattern' => '/^\d+(?:\.\d{1,2})?$/', 'message' => 'El precio debe tener máximo dos decimales.'],
            [['formulario'], 'integer', 'min' => 10, 'max' => 14],
            [['FechaHoraRegistro'], 'safe'],
            [['Descripcion'], 'string', 'max' => 500],
            [['IdFuente', 'IdOrganismo'], 'string', 'max' => 10],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['IdItemDescatalogado'], 'unique'],
            [['IdOperacion'], 'exist', 'targetClass' => Operacion::class, 'targetAttribute' => ['IdOperacion' => 'IdOperacion']],
            [['IdFuente'], 'exist', 'targetClass' => Fuente::class, 'targetAttribute' => ['IdFuente' => 'IdFuente']],
            [['IdFuente', 'IdOrganismo'], 'exist', 'targetClass' => Organismo::class, 'targetAttribute' => ['IdFuente' => 'IdFuente', 'IdOrganismo' => 'IdOrganismo']],
            [['IdGestion'], 'exist', 'targetClass' => PeiGestion::class, 'targetAttribute' => ['IdGestion' => 'IdGestion']],
            [['IdEstadoPoa'], 'exist', 'targetClass' => EstadosPoa::class, 'targetAttribute' => ['IdEstadoPoa' => 'IdEstadoPoa']],
            [['IdGasto'], 'exist', 'targetClass' => Gasto::class, 'targetAttribute' => ['IdGasto' => 'CodigoGasto']],
            [['CodigoEstado'], 'exist', 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
        ];
    }

    public function getOperacion(): ActiveQuery
    {
        return $this->hasOne(Operacion::class, ['IdOperacion' => 'IdOperacion']);
    }

    public function getGasto(): ActiveQuery
    {
        return $this->hasOne(Gasto::class, ['CodigoGasto' => 'IdGasto']);
    }

    public function getFuente(): ActiveQuery
    {
        return $this->hasOne(Fuente::class, ['IdFuente' => 'IdFuente']);
    }

    public function getOrganismo(): ActiveQuery
    {
        return $this->hasOne(Organismo::class, ['IdFuente' => 'IdFuente', 'IdOrganismo' => 'IdOrganismo']);
    }
}
