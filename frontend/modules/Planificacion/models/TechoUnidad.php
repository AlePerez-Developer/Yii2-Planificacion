<?php

namespace app\modules\Planificacion\models;

use common\models\Estado;
use common\models\Usuario;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

class TechoUnidad extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'TechoUnidad';
    }

    public function rules(): array
    {
        return [
            [['IdLlavePresupuestaria', 'IdGestion', 'Techo', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['IdAsignacion', 'IdLlavePresupuestaria', 'IdGestion'], 'string', 'max' => 36],
            [['Techo'], 'integer', 'min' => 1],
            [['FechaHoraRegistro'], 'safe'],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['IdAsignacion'], 'unique'],
            [
                ['IdLlavePresupuestaria', 'IdGestion'],
                'unique',
                'targetAttribute' => ['IdLlavePresupuestaria', 'IdGestion'],
                'message' => 'La llave ya tiene un techo asignado para esta gestión.',
            ],
            [['CodigoEstado'], 'exist', 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
            [['IdGestion'], 'exist', 'targetClass' => PeiGestion::class, 'targetAttribute' => ['IdGestion' => 'IdGestion']],
            [['IdLlavePresupuestaria'], 'exist', 'targetClass' => LlavePresupuestaria::class, 'targetAttribute' => ['IdLlavePresupuestaria' => 'IdLlavePresupuestaria']],
        ];
    }

    public function getGestion(): ActiveQuery
    {
        return $this->hasOne(PeiGestion::class, ['IdGestion' => 'IdGestion']);
    }

    public function getLlavePresupuestaria(): ActiveQuery
    {
        return $this->hasOne(LlavePresupuestaria::class, ['IdLlavePresupuestaria' => 'IdLlavePresupuestaria']);
    }
}
