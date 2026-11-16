<?php

namespace app\modules\Planificacion\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "ProgramacionIndicadorMetaAnualGlobal".
 *
 * @property string $IdProgramcion
 * @property string $IdIndicador
 * @property string $IdGestion
 * @property float $Meta
 *
 * @property PeiGestion $gestion
 * @property IndicadorEstrategico $indicadorEstrategico
 */
class ProgramacionIndicadorMetaAnualGlobal extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'ProgramacionIndicadorMetaAnualGlobal';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['Meta'], 'default', 'value' => 0],
            [['IdProgramcion', 'IdIndicador', 'IdGestion'], 'string', 'max' => 36],
            [['IdIndicador', 'IdGestion'], 'required'],
            [['Meta'], 'number', 'min' => 0],
            [['IdProgramcion'], 'unique'],
            [['IdIndicador', 'IdGestion'], 'unique', 'targetAttribute' => ['IdIndicador', 'IdGestion']],
            [['IdGestion'], 'exist', 'skipOnError' => true, 'targetClass' => PeiGestion::class, 'targetAttribute' => ['IdGestion' => 'IdGestion']],
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
            'IdGestion' => 'Id Gestión',
            'Meta' => 'Meta',
        ];
    }

    public static function listOne(string $idIndicador, string $idGestion): ?self
    {
        return self::findOne([
            'IdIndicador' => $idIndicador,
            'IdGestion' => $idGestion,
        ]);
    }

    /**
     * @return ActiveQuery
     */
    public function getGestion(): ActiveQuery
    {
        return $this->hasOne(PeiGestion::class, ['IdGestion' => 'IdGestion']);
    }

    /**
     * @return ActiveQuery
     */
    public function getIndicadorEstrategico(): ActiveQuery
    {
        return $this->hasOne(IndicadorEstrategico::class, ['IdIndicador' => 'IdIndicador']);
    }
}
