<?php

namespace app\modules\Planificacion\models;

use Yii;

/**
 * This is the model class for table "Fuentes".
 *
 * @property string $IdFuente
 * @property string $Descripcion
 * @property string $FechaHoraRegistro
 *
 * @property ItemCatalogado[] $itemCatalogados
 * @property ItemDescatalogado[] $itemDescatalogados
 * @property Organismo[] $organismos
 * @property ProgramacionesItem[] $programacionesItems
 */
class Fuente extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Fuentes';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['FechaHoraRegistro'], 'default', 'value' => 'etdate('],
            [['IdFuente', 'Descripcion'], 'required'],
            [['FechaHoraRegistro'], 'safe'],
            [['IdFuente'], 'string', 'max' => 10],
            [['Descripcion'], 'string', 'max' => 250],
            [['IdFuente'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'IdFuente' => 'Id Fuente',
            'Descripcion' => 'Descripcion',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
        ];
    }

    /**
     * Gets query for [[ItemCatalogados]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItemCatalogados()
    {
        return $this->hasMany(ItemCatalogado::class, ['IdFuente' => 'IdFuente']);
    }

    /**
     * Gets query for [[ItemDescatalogados]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItemDescatalogados()
    {
        return $this->hasMany(ItemDescatalogado::class, ['IdFuente' => 'IdFuente']);
    }

    /**
     * Gets query for [[Organismos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrganismos()
    {
        return $this->hasMany(Organismo::class, ['IdFuente' => 'IdFuente']);
    }

    /**
     * Gets query for [[ProgramacionesItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProgramacionesItems()
    {
        return $this->hasMany(ProgramacionesItem::class, ['Idfuente' => 'IdFuente']);
    }

}
