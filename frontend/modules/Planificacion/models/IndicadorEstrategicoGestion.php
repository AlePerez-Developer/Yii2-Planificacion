<?php

namespace app\modules\Planificacion\models;

use Yii;

/**
 * This is the model class for table "IndicadoresEstrategicosGestiones".
 *
 * @property int $CodigoProgramacionGestion
 * @property int $IndicadorEstrategico
 * @property int $Gestion
 * @property int $Meta
 *
 * @property IndicadorEstrategico $indicadorEstrategico
 * @property IndicadorEstrategicoUnidad[] $indicadorEstrategicoUnidades
 */
class IndicadorEstrategicoGestion extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'IndicadoresEstrategicosGestiones';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['IndicadorEstrategico', 'Gestion', 'Meta'], 'required'],
            [['IndicadorEstrategico', 'Gestion', 'Meta'], 'integer'],
            [['Gestion', 'IndicadorEstrategico'], 'unique', 'targetAttribute' => ['Gestion', 'IndicadorEstrategico']],
            [['IndicadorEstrategico'], 'exist', 'skipOnError' => true, 'targetClass' => IndicadorEstrategico::class, 'targetAttribute' => ['IndicadorEstrategico' => 'CodigoIndicador']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'CodigoProgramacionGestion' => 'Codigo Programacion Gestion',
            'IndicadorEstrategico' => 'Indicador Estrategico',
            'Gestion' => 'Gestion',
            'Meta' => 'Meta',
        ];
    }

    /**
     * Gets query for [[IndicadorEstrategico]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIndicadorEstrategico()
    {
        return $this->hasOne(IndicadorEstrategico::class, ['CodigoIndicador' => 'IndicadorEstrategico']);
    }

    /**
     * Gets query for [[IndicadoresEstrategicosUnidades]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIndicadoresEstrategicosUnidades()
    {
        return $this->hasMany(IndicadorEstrategicoUnidad::class, ['ProgramacionGestion' => 'CodigoProgramacionGestion']);
    }
}
