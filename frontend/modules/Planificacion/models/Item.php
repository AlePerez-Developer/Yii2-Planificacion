<?php

namespace app\modules\Planificacion\models;

use common\models\Estado;
use common\models\Usuario;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property string $IdItem
 * @property string $IdFuente
 * @property string $IdOrganismo
 * @property string $IdFuenteUniversitaria
 * @property int $Formulario
 * @property string $Descripcion
 * @property string $IdUnidadMedida
 * @property float $PrecioReferencial
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property Fuente $fuente
 * @property Organismo $organismo
 * @property FuenteUniversitaria $fuenteUniversitaria
 * @property UnidadMedida $unidadMedida
 * @property ItemCatalogado $itemCatalogado
 * @property ItemDescatalogado $itemDescatalogado
 * @property ItemGestion[] $itemsGestiones
 * @property Estado $codigoEstado
 * @property Usuario $codigoUsuario
 */
class Item extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'Poa.Items';
    }

    public function rules(): array
    {
        return [
            [[
                'IdFuente',
                'IdOrganismo',
                'IdFuenteUniversitaria',
                'Formulario',
                'Descripcion',
                'IdUnidadMedida',
                'PrecioReferencial',
                'CodigoEstado',
                'CodigoUsuario',
            ], 'required'],
            [['IdItem', 'IdFuente', 'IdOrganismo', 'IdFuenteUniversitaria', 'IdUnidadMedida'], 'string', 'max' => 36],
            [['Formulario'], 'integer'],
            [['PrecioReferencial'], 'number', 'min' => 0],
            [['Descripcion'], 'string', 'max' => 500],
            [['FechaHoraRegistro'], 'safe'],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['IdItem'], 'unique'],
            [['IdFuente'], 'exist', 'skipOnError' => true, 'targetClass' => Fuente::class, 'targetAttribute' => ['IdFuente' => 'IdFuente']],
            [['IdOrganismo'], 'exist', 'skipOnError' => true, 'targetClass' => Organismo::class, 'targetAttribute' => ['IdOrganismo' => 'IdOrganismo']],
            [['IdFuenteUniversitaria'], 'exist', 'skipOnError' => true, 'targetClass' => FuenteUniversitaria::class, 'targetAttribute' => ['IdFuenteUniversitaria' => 'IdFuenteUniversitaria']],
            [['IdUnidadMedida'], 'exist', 'skipOnError' => true, 'targetClass' => UnidadMedida::class, 'targetAttribute' => ['IdUnidadMedida' => 'IdUnidadMedida']],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
        ];
    }

    public static function listOne(string $id): ?self
    {
        return self::find()
            ->where(['IdItem' => $id])
            ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->one();
    }

    public static function listAll(): ActiveQuery
    {
        return self::find()
            ->select([
                'IdItem',
                'IdFuente',
                'IdOrganismo',
                'IdFuenteUniversitaria',
                'Formulario',
                'Descripcion',
                'IdUnidadMedida',
                'PrecioReferencial',
                'CodigoEstado',
                'CodigoUsuario',
            ])
            ->where(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->orderBy(['Descripcion' => SORT_ASC]);
    }

    public function cambiarEstado(): void
    {
        $this->CodigoEstado = $this->CodigoEstado === Estado::ESTADO_VIGENTE
            ? Estado::ESTADO_CADUCO
            : Estado::ESTADO_VIGENTE;
    }

    public function eliminar(): void
    {
        $this->CodigoEstado = Estado::ESTADO_ELIMINADO;
    }

    public function getFuente(): ActiveQuery
    {
        return $this->hasOne(Fuente::class, ['IdFuente' => 'IdFuente']);
    }

    public function getOrganismo(): ActiveQuery
    {
        return $this->hasOne(Organismo::class, ['IdOrganismo' => 'IdOrganismo']);
    }

    public function getFuenteUniversitaria(): ActiveQuery
    {
        return $this->hasOne(FuenteUniversitaria::class, ['IdFuenteUniversitaria' => 'IdFuenteUniversitaria']);
    }

    public function getUnidadMedida(): ActiveQuery
    {
        return $this->hasOne(UnidadMedida::class, ['IdUnidadMedida' => 'IdUnidadMedida']);
    }

    public function getItemCatalogado(): ActiveQuery
    {
        return $this->hasOne(ItemCatalogado::class, ['IdItem' => 'IdItem']);
    }

    public function getItemDescatalogado(): ActiveQuery
    {
        return $this->hasOne(ItemDescatalogado::class, ['IdItem' => 'IdItem']);
    }

    public function getItemsGestiones(): ActiveQuery
    {
        return $this->hasMany(ItemGestion::class, ['IdItem' => 'IdItem']);
    }

    public function getCodigoEstado(): ActiveQuery
    {
        return $this->hasOne(Estado::class, ['CodigoEstado' => 'CodigoEstado']);
    }

    public function getCodigoUsuario(): ActiveQuery
    {
        return $this->hasOne(Usuario::class, ['CodigoUsuario' => 'CodigoUsuario']);
    }
}
