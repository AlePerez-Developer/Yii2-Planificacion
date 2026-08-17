<?php

namespace app\modules\Planificacion\models;

use common\models\Usuario;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property string $IdProgramacionPoaTrimestral
 * @property string $IdProgramacionIndicadorPoaGestion
 * @property int $MetaPrimerTrimestre
 * @property int $MetaSegundoTrimestre
 * @property int $MetaTercerTrimestre
 * @property int $MetaCuartoTrimestre
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 */
class ProgramacionIndicadorPoaTrimestre extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'ProgramacionesIndicadoresPoaTrimestres';
    }

    public function rules(): array
    {
        return [
            [['IdProgramacionIndicadorPoaGestion', 'CodigoUsuario'], 'required'],
            [['IdProgramacionPoaTrimestral', 'IdProgramacionIndicadorPoaGestion'], 'string', 'max' => 36],
            [[
                'MetaPrimerTrimestre',
                'MetaSegundoTrimestre',
                'MetaTercerTrimestre',
                'MetaCuartoTrimestre',
            ], 'integer', 'min' => 0],
            [[
                'MetaPrimerTrimestre',
                'MetaSegundoTrimestre',
                'MetaTercerTrimestre',
                'MetaCuartoTrimestre',
            ], 'default', 'value' => 0],
            [['FechaHoraRegistro'], 'safe'],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['IdProgramacionPoaTrimestral'], 'unique'],
            [['IdProgramacionIndicadorPoaGestion'], 'unique'],
            [['IdProgramacionIndicadorPoaGestion'], 'exist', 'targetClass' => ProgramacionIndicadorPoaGestion::class, 'targetAttribute' => ['IdProgramacionIndicadorPoaGestion' => 'IdProgramacionIndicadorPoaGestion']],
            [['CodigoUsuario'], 'exist', 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
        ];
    }

    public function getProgramacionGestion(): ActiveQuery
    {
        return $this->hasOne(
            ProgramacionIndicadorPoaGestion::class,
            ['IdProgramacionIndicadorPoaGestion' => 'IdProgramacionIndicadorPoaGestion']
        );
    }
}
