<?php

namespace app\modules\Planificacion\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "ProgramacionIndicadorDa".
 *
 * @property string $IdProgramcion
 * @property string $IdIndicador
 * @property string $IdDa
 *
 * @property Da $da
 * @property IndicadorEstrategico $indicadorEstrategico
 */
class ProgramacionIndicadorDa extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'ProgramacionIndicadorDa';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['IdProgramcion', 'IdIndicador', 'IdDa'], 'string', 'max' => 36],
            [['IdIndicador', 'IdDa'], 'required'],
            [['IdProgramcion'], 'unique'],
            [['IdIndicador', 'IdDa'], 'unique', 'targetAttribute' => ['IdIndicador', 'IdDa']],
            [['IdDa'], 'exist', 'skipOnError' => true, 'targetClass' => Da::class, 'targetAttribute' => ['IdDa' => 'IdDa']],
            [['IdIndicador'], 'exist', 'skipOnError' => true, 'targetClass' => IndicadorEstrategico::class, 'targetAttribute' => ['IdIndicador' => 'IdIndicador']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'IdProgramcion' => 'Id Programación',
            'IdIndicador' => 'Id Indicador',
            'IdDa' => 'Id Da',
        ];
    }

    public static function listOne(string $idIndicador, string $idDa): ?self
    {
        return self::findOne([
            'IdIndicador' => $idIndicador,
            'IdDa' => $idDa,
        ]);
    }

    /**
     * @return ActiveQuery
     */
    public function getDa(): ActiveQuery
    {
        return $this->hasOne(Da::class, ['IdDa' => 'IdDa']);
    }

    /**
     * @return ActiveQuery
     */
    public function getIndicadorEstrategico(): ActiveQuery
    {
        return $this->hasOne(IndicadorEstrategico::class, ['IdIndicador' => 'IdIndicador']);
    }
}
