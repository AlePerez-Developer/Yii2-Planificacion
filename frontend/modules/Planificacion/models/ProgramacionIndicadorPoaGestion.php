<?php

namespace app\modules\Planificacion\models;

use common\models\Usuario;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property string $IdProgramacionIndicadorPoaGestion
 * @property string $IdIndicadorPoa
 * @property string $IdObjEspecifico
 * @property string $IdLlavePresupuestaria
 * @property string $IdGestion
 * @property int $MetaProgramada
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 */
class ProgramacionIndicadorPoaGestion extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'ProgramacionesIndicadoresPoaGestiones';
    }

    public function rules(): array
    {
        return [
            [['IdIndicadorPoa', 'IdObjEspecifico', 'IdLlavePresupuestaria', 'IdGestion', 'CodigoUsuario'], 'required'],
            [['IdProgramacionIndicadorPoaGestion', 'IdIndicadorPoa', 'IdObjEspecifico', 'IdLlavePresupuestaria', 'IdGestion'], 'string', 'max' => 36],
            [['MetaProgramada'], 'integer', 'min' => 0],
            [['MetaProgramada'], 'default', 'value' => 0],
            [['FechaHoraRegistro'], 'safe'],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['IdProgramacionIndicadorPoaGestion'], 'unique'],
            [
                ['IdIndicadorPoa', 'IdObjEspecifico', 'IdLlavePresupuestaria', 'IdGestion'],
                'unique',
                'targetAttribute' => ['IdIndicadorPoa', 'IdObjEspecifico', 'IdLlavePresupuestaria', 'IdGestion'],
                'message' => 'La relación seleccionada ya se encuentra programada.',
            ],
            [['CodigoUsuario'], 'exist', 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
            [['IdGestion'], 'exist', 'targetClass' => PeiGestion::class, 'targetAttribute' => ['IdGestion' => 'IdGestion']],
            [['IdIndicadorPoa'], 'exist', 'targetClass' => IndicadorPoa::class, 'targetAttribute' => ['IdIndicadorPoa' => 'IdIndicador']],
            [['IdObjEspecifico'], 'exist', 'targetClass' => ObjetivoEspecifico::class, 'targetAttribute' => ['IdObjEspecifico' => 'IdObjEspecifico']],
            [['IdLlavePresupuestaria'], 'exist', 'targetClass' => LlavePresupuestaria::class, 'targetAttribute' => ['IdLlavePresupuestaria' => 'IdLlavePresupuestaria']],
        ];
    }

    public function getIndicadorPoa(): ActiveQuery
    {
        return $this->hasOne(IndicadorPoa::class, ['IdIndicador' => 'IdIndicadorPoa']);
    }

    public function getObjetivoEspecifico(): ActiveQuery
    {
        return $this->hasOne(ObjetivoEspecifico::class, ['IdObjEspecifico' => 'IdObjEspecifico']);
    }

    public function getLlavePresupuestaria(): ActiveQuery
    {
        return $this->hasOne(LlavePresupuestaria::class, ['IdLlavePresupuestaria' => 'IdLlavePresupuestaria']);
    }

    public function getGestion(): ActiveQuery
    {
        return $this->hasOne(PeiGestion::class, ['IdGestion' => 'IdGestion']);
    }

    public function getTrimestre(): ActiveQuery
    {
        return $this->hasOne(
            ProgramacionIndicadorPoaTrimestre::class,
            ['IdProgramacionIndicadorPoaGestion' => 'IdProgramacionIndicadorPoaGestion']
        );
    }
}
