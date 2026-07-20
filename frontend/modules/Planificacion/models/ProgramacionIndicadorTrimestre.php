<?php

namespace app\modules\Planificacion\models;

use common\models\Usuario;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Modelo de la tabla "ProgramacionesIndicadoresTrimestres".
 *
 * @property string $IdProgramacionTrimestral
 * @property string $IdProgramacionIndicadorGestio
 * @property int $MetaPrimerTrimestre
 * @property int $MetaSegundoTrimestre
 * @property int $MetaTercerTrimestre
 * @property int $MetaCuartoTrimestre
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property ProgramacionIndicadorGestion $programacionIndicadorGestion
 * @property Usuario $codigoUsuario
 */
class ProgramacionIndicadorTrimestre extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'ProgramacionesIndicadoresTrimestres';
    }

    public function rules(): array
    {
        return [
            [['IdProgramacionTrimestral', 'IdProgramacionIndicadorGestio'], 'string', 'max' => 36],
            [['IdProgramacionIndicadorGestio', 'CodigoUsuario'], 'required'],
            [[
                'MetaPrimerTrimestre',
                'MetaSegundoTrimestre',
                'MetaTercerTrimestre',
                'MetaCuartoTrimestre',
            ], 'integer', 'min' => 0],
            [['FechaHoraRegistro'], 'safe'],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['IdProgramacionTrimestral'], 'unique'],
            [['IdProgramacionIndicadorGestio'], 'unique'],
            [['IdProgramacionIndicadorGestio'], 'exist',
                'skipOnError' => true,
                'targetClass' => ProgramacionIndicadorGestion::class,
                'targetAttribute' => ['IdProgramacionIndicadorGestio' => 'IdProgramacionIndicadorGestio'],
            ],
            [['CodigoUsuario'], 'exist',
                'skipOnError' => true,
                'targetClass' => Usuario::class,
                'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario'],
            ],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'IdProgramacionTrimestral' => 'Id Programación Trimestral',
            'IdProgramacionIndicadorGestio' => 'Programación anual',
            'MetaPrimerTrimestre' => 'Primer trimestre',
            'MetaSegundoTrimestre' => 'Segundo trimestre',
            'MetaTercerTrimestre' => 'Tercer trimestre',
            'MetaCuartoTrimestre' => 'Cuarto trimestre',
            'FechaHoraRegistro' => 'Fecha de registro',
            'CodigoUsuario' => 'Usuario',
        ];
    }

    public function getProgramacionIndicadorGestion(): ActiveQuery
    {
        return $this->hasOne(
            ProgramacionIndicadorGestion::class,
            ['IdProgramacionIndicadorGestio' => 'IdProgramacionIndicadorGestio']
        );
    }

    public function getCodigoUsuario(): ActiveQuery
    {
        return $this->hasOne(Usuario::class, ['CodigoUsuario' => 'CodigoUsuario']);
    }
}
