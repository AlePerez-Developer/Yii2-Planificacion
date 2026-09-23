<?php

namespace app\modules\Planificacion\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property string $IdItem
 * @property string $IdSigma
 *
 * @property Item $item
 * @property CatalogoSigma $sigma
 */
class ItemCatalogado extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'Poa.ItemsCatalogados';
    }

    public static function primaryKey(): array
    {
        return ['IdItem'];
    }

    public function rules(): array
    {
        return [
            [['IdItem', 'IdSigma'], 'required'],
            [['IdItem', 'IdSigma'], 'string', 'max' => 36],
            [['IdItem'], 'unique'],
            [['IdItem'], 'exist', 'skipOnError' => true, 'targetClass' => Item::class, 'targetAttribute' => ['IdItem' => 'IdItem']],
            [['IdSigma'], 'exist', 'skipOnError' => true, 'targetClass' => CatalogoSigma::class, 'targetAttribute' => ['IdSigma' => 'IdSigma']],
        ];
    }

    public function getItem(): ActiveQuery
    {
        return $this->hasOne(Item::class, ['IdItem' => 'IdItem']);
    }

    public function getSigma(): ActiveQuery
    {
        return $this->hasOne(CatalogoSigma::class, ['IdSigma' => 'IdSigma']);
    }
}
