<?php

namespace app\modules\Planificacion\models;

use common\models\Estado;
use common\models\Usuario;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property string $IdItem_Gestion
 * @property string $IdItem
 * @property string $IdGestion
 * @property float $PrecioUnitario
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property Item $item
 * @property PeiGestion $gestion
 * @property ItemGestionOperacion[] $itemsGestionesOperaciones
 * @property Estado $codigoEstado
 * @property Usuario $codigoUsuario
 */
class ItemGestion extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'Poa.Items_Gestiones';
    }

    public function rules(): array
    {
        return [
            [['IdItem', 'IdGestion', 'PrecioUnitario', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['IdItem_Gestion', 'IdItem', 'IdGestion'], 'string', 'max' => 36],
            [['PrecioUnitario'], 'number', 'min' => 0],
            [['FechaHoraRegistro'], 'safe'],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['IdItem_Gestion'], 'unique'],
            [['IdItem', 'IdGestion'], 'unique', 'targetAttribute' => ['IdItem', 'IdGestion']],
            [['IdItem'], 'exist', 'skipOnError' => true, 'targetClass' => Item::class, 'targetAttribute' => ['IdItem' => 'IdItem']],
            [['IdGestion'], 'exist', 'skipOnError' => true, 'targetClass' => PeiGestion::class, 'targetAttribute' => ['IdGestion' => 'IdGestion']],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
        ];
    }

    public static function listOne(string $id): ?self
    {
        return self::find()
            ->where(['IdItem_Gestion' => $id])
            ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->one();
    }

    public static function listAll(): ActiveQuery
    {
        return self::find()
            ->select([
                'IdItem_Gestion',
                'IdItem',
                'IdGestion',
                'PrecioUnitario',
                'CodigoEstado',
                'CodigoUsuario',
            ])
            ->where(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO]);
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

    public function getItem(): ActiveQuery
    {
        return $this->hasOne(Item::class, ['IdItem' => 'IdItem']);
    }

    public function getGestion(): ActiveQuery
    {
        return $this->hasOne(PeiGestion::class, ['IdGestion' => 'IdGestion']);
    }

    public function getItemsGestionesOperaciones(): ActiveQuery
    {
        return $this->hasMany(ItemGestionOperacion::class, ['IdItem_Gestion' => 'IdItem_Gestion']);
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
