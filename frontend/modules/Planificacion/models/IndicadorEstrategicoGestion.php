<?php

namespace app\modules\Planificacion\models;

/**
 * This is the model class for table "IndicadoresEstrategicosGestiones".
 *
 * @property int $CodigoProgramacion
 * @property int $Gestion
 * @property int $IndicadorEstrategico
 * @property int $Meta
 *
 * @property IndicadoresEstrategico $indicadorEstrategico
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
            [['Gestion', 'IndicadorEstrategico', 'Meta'], 'required'],
            [['Gestion', 'IndicadorEstrategico', 'Meta'], 'integer'],
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
            'CodigoProgramacion' => 'Codigo Programacion',
            'Gestion' => 'Gestion',
            'IndicadorEstrategico' => 'Indicador Estrategico',
            'MetaProgramada' => 'Meta Programada',
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

}
