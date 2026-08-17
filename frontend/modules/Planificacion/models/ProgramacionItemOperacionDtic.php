<?php

namespace app\modules\Planificacion\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property string $IdProgramacionItemOperacion
 * @property string $IdProgramacionItem
 * @property string $IdOperacion
 * @property int $CantidadAsignada
 *
 * @property ProgramacionItem $programacionItem
 * @property OperacionDtic $operacion
 */
class ProgramacionItemOperacionDtic extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'ProgramacionItemOperacionDtic';
    }

    public function rules(): array
    {
        return [
            [['IdProgramacionItemOperacion', 'IdProgramacionItem', 'IdOperacion'], 'string', 'max' => 36],
            [['IdProgramacionItem', 'IdOperacion', 'CantidadAsignada'], 'required'],
            [['CantidadAsignada'], 'integer', 'min' => 1],
            [['IdProgramacionItemOperacion'], 'unique'],
            [['IdProgramacionItem', 'IdOperacion'], 'unique', 'targetAttribute' => ['IdProgramacionItem', 'IdOperacion']],
            [['IdProgramacionItem'], 'exist', 'targetClass' => ProgramacionItem::class, 'targetAttribute' => ['IdProgramacionItem' => 'IdProgramacionItem']],
            [['IdOperacion'], 'exist', 'targetClass' => OperacionDtic::class, 'targetAttribute' => ['IdOperacion' => 'IdOperacion']],
        ];
    }

    public function getProgramacionItem(): ActiveQuery
    {
        return $this->hasOne(
            ProgramacionItem::class,
            ['IdProgramacionItem' => 'IdProgramacionItem']
        );
    }

    public function getOperacion(): ActiveQuery
    {
        return $this->hasOne(OperacionDtic::class, ['IdOperacion' => 'IdOperacion']);
    }
}
