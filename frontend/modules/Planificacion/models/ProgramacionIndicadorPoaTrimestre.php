<?php

namespace app\modules\Planificacion\models;

use common\models\Usuario;
use yii\db\ActiveRecord;

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
            [['MetaPrimerTrimestre', 'MetaSegundoTrimestre', 'MetaTercerTrimestre', 'MetaCuartoTrimestre'], 'integer', 'min' => 0],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['FechaHoraRegistro'], 'safe'],
            [['IdProgramacionIndicadorPoaGestion'], 'exist',
                'targetClass' => ProgramacionIndicadorPoaGestion::class,
                'targetAttribute' => ['IdProgramacionIndicadorPoaGestion' => 'IdProgramacionIndicadorPoaGestion']],
            [['CodigoUsuario'], 'exist', 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
        ];
    }
}
