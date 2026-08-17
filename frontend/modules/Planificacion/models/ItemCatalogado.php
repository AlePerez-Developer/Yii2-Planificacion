<?php

namespace app\modules\Planificacion\models;

use common\models\Estado;
use common\models\seguridad\EstadosPoa;
use common\models\Usuario;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

class ItemCatalogado extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'ItemCatalogado';
    }

    public function rules(): array
    {
        return [
            [['IdOperacion', 'IdSigma', 'IdEstadoPoa', 'IdGestion', 'IdFuente', 'IdOrganismo', 'cantidad', 'Precio', 'formulario', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['IdItemCatalogado', 'IdOperacion', 'IdSigma', 'IdEstadoPoa', 'IdGestion'], 'string', 'max' => 36],
            [['cantidad'], 'integer', 'min' => 1],
            [['Precio'], 'number', 'min' => 0.01],
            [['Precio'], 'match', 'pattern' => '/^\d+(?:\.\d{1,2})?$/', 'message' => 'El precio debe tener máximo dos decimales.'],
            [['formulario'], 'integer', 'min' => 7, 'max' => 9],
            [['FechaHoraRegistro'], 'safe'],
            [['IdFuente', 'IdOrganismo'], 'string', 'max' => 10],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['IdItemCatalogado'], 'unique'],
            [['IdSigma'], 'exist', 'targetClass' => CatalogoSigma::class, 'targetAttribute' => ['IdSigma' => 'IdSigma']],
            [['IdOperacion'], 'exist', 'targetClass' => Operacion::class, 'targetAttribute' => ['IdOperacion' => 'IdOperacion']],
            [['IdFuente'], 'exist', 'targetClass' => Fuente::class, 'targetAttribute' => ['IdFuente' => 'IdFuente']],
            [['IdFuente', 'IdOrganismo'], 'exist', 'targetClass' => Organismo::class, 'targetAttribute' => ['IdFuente' => 'IdFuente', 'IdOrganismo' => 'IdOrganismo']],
            [['IdGestion'], 'exist', 'targetClass' => PeiGestion::class, 'targetAttribute' => ['IdGestion' => 'IdGestion']],
            [['IdEstadoPoa'], 'exist', 'targetClass' => EstadosPoa::class, 'targetAttribute' => ['IdEstadoPoa' => 'IdEstadoPoa']],
            [['CodigoEstado'], 'exist', 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
        ];
    }

    public function getOperacion(): ActiveQuery
    {
        return $this->hasOne(Operacion::class, ['IdOperacion' => 'IdOperacion']);
    }

    public function getSigma(): ActiveQuery
    {
        return $this->hasOne(CatalogoSigma::class, ['IdSigma' => 'IdSigma']);
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
