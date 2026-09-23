<?php

namespace app\modules\Planificacion\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property string $IdItem
 * @property string $IdGasto
 *
 * @property Item $item
 * @property Gasto $gasto
 */
class ItemDescatalogado extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'Poa.ItemsDescatalogados';
    }

    public static function primaryKey(): array
    {
        return ['IdItem'];
    }

    public function rules(): array
    {
        return [
            [['IdItem', 'IdGasto'], 'required'],
            [['IdItem', 'IdGasto'], 'string', 'max' => 36],
            [['IdItem'], 'unique'],
            [['IdItem'], 'exist', 'skipOnError' => true, 'targetClass' => Item::class, 'targetAttribute' => ['IdItem' => 'IdItem']],
            [['IdGasto'], 'exist', 'skipOnError' => true, 'targetClass' => Gasto::class, 'targetAttribute' => ['IdGasto' => 'IdGasto']],
        ];
    }

    public function getItem(): ActiveQuery
    {
        return $this->hasOne(Item::class, ['IdItem' => 'IdItem']);
    }

    public function getGasto(): ActiveQuery
    {
        return $this->hasOne(Gasto::class, ['IdGasto' => 'IdGasto']);
    }
}
