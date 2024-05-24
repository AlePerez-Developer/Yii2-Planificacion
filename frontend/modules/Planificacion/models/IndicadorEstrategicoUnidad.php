<?php

namespace app\modules\Planificacion\models;

use Yii;

/**
 * This is the model class for table "IndicadoresEstrategicosUnidades".
 *
 * @property int $CodigoProgramacionUnidad
 * @property int $ProgramacionGestion
 * @property int $Unidad
 * @property int $Meta
 *
 * @property IndicadorEstrategicoGestion $programacionGestion
 * @property Unidad $unidad
 */
class IndicadorEstrategicoUnidad extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'IndicadoresEstrategicosUnidades';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ProgramacionGestion', 'Unidad', 'Meta'], 'required'],
            [['ProgramacionGestion', 'Unidad', 'Meta'], 'integer'],
            [['ProgramacionGestion'], 'exist', 'skipOnError' => true, 'targetClass' => IndicadorEstrategicoGestion::class, 'targetAttribute' => ['ProgramacionGestion' => 'CodigoProgramacionGestion']],
            [['Unidad'], 'exist', 'skipOnError' => true, 'targetClass' => Unidad::class, 'targetAttribute' => ['Unidad' => 'CodigoUnidad']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'CodigoProgramacionUnidad' => 'Codigo Programacion Unidad',
            'ProgramacionGestion' => 'Programacion Gestion',
            'Unidad' => 'Unidad',
            'Meta' => 'Meta',
        ];
    }

    /**
     * Gets query for [[ProgramacionGestion]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProgramacionGestion()
    {
        return $this->hasOne(IndicadorEstrategicoGestion::class, ['CodigoProgramacionGestion' => 'ProgramacionGestion']);
    }

    /**
     * Gets query for [[Unidad]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUnidad()
    {
        return $this->hasOne(Unidad::class, ['CodigoUnidad' => 'Unidad']);
    }
}
