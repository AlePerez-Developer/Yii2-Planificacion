<?php

namespace app\modules\Planificacion\models;

use common\models\Usuario;
use yii\db\ActiveRecord;

class ProgramacionIndicadorPoaGestion extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'ProgramacionesIndicadoresPoaGestiones';
    }

    public function rules(): array
    {
        return [
            [['IdIndicadorPoa', 'IdGestion', 'IdLlavePresupuestaria', 'MetaProgramada', 'CodigoUsuario'], 'required'],
            [['IdProgramacionIndicadorPoaGestion', 'IdIndicadorPoa', 'IdGestion', 'IdLlavePresupuestaria'], 'string', 'max' => 36],
            [['MetaProgramada'], 'integer', 'min' => 0],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['FechaHoraRegistro'], 'safe'],
            [['IdIndicadorPoa'], 'exist', 'targetClass' => IndicadorPoa::class, 'targetAttribute' => ['IdIndicadorPoa' => 'IdIndicadorPoa']],
            [['CodigoUsuario'], 'exist', 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
        ];
    }
}
