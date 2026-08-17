<?php

namespace app\modules\Planificacion\models;

use Yii;

/**
 * This is the model class for table "Organismos".
 *
 * @property string $IdOrganismo
 * @property string $Descripcion
 * @property string $IdFuente
 * @property string $FechaHoraRegistro
 *
 * @property Fuente $idFuente
 * @property ProgramacionesItem[] $programacionesItems
 */
class Organismo extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Organismos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['FechaHoraRegistro'], 'default', 'value' => 'etdate('],
            [['IdOrganismo', 'Descripcion', 'IdFuente'], 'required'],
            [['FechaHoraRegistro'], 'safe'],
            [['IdOrganismo', 'IdFuente'], 'string', 'max' => 10],
            [['Descripcion'], 'string', 'max' => 250],
            [['IdFuente', 'IdOrganismo'], 'unique', 'targetAttribute' => ['IdFuente', 'IdOrganismo']],
            [['IdFuente'], 'exist', 'skipOnError' => true, 'targetClass' => Fuente::class, 'targetAttribute' => ['IdFuente' => 'IdFuente']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'IdOrganismo' => 'Id Organismo',
            'Descripcion' => 'Descripcion',
            'IdFuente' => 'Id Fuente',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
        ];
    }

    /**
     * Gets query for [[IdFuente]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIdFuente()
    {
        return $this->hasOne(Fuente::class, ['IdFuente' => 'IdFuente']);
    }

    /**
     * Gets query for [[ProgramacionesItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProgramacionesItems()
    {
        return $this->hasMany(ProgramacionesItem::class, ['Idfuente' => 'IdFuente', 'IdOrganismo' => 'IdOrganismo']);
    }

}
