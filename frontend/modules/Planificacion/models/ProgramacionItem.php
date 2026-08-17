<?php

namespace app\modules\Planificacion\models;

use common\models\Estado;
use common\models\Usuario;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property string $IdProgramacionItem
 * @property string $IdItem
 * @property string $IdLlavePresupuestaria
 * @property string $IdGestion
 * @property int $CodigoEstadoPoa
 * @property string|null $IdFuente
 * @property string|null $IdOrganismo
 * @property string|null $IdFuenteUniversitaria
 * @property string|null $IdFinalidad
 * @property string|null $IdUnidadMedida
 * @property int $Cantidad
 * @property float $PrecioUnitario
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property Item $item
 * @property LlavePresupuestaria $llavePresupuestaria
 * @property PeiGestion $gestion
 * @property ProgramacionItemOperacionDtic[] $asignacionesOperaciones
 */
class ProgramacionItem extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'ProgramacionesItems';
    }

    public function rules(): array
    {
        return [
            [['IdProgramacionItem', 'IdItem', 'IdLlavePresupuestaria', 'IdGestion'], 'string', 'max' => 36],
            [['IdFuente', 'IdOrganismo', 'IdFuenteUniversitaria', 'IdFinalidad', 'IdUnidadMedida'], 'string', 'max' => 36],
            [['IdItem', 'IdLlavePresupuestaria', 'IdGestion', 'CodigoEstadoPoa', 'Cantidad', 'PrecioUnitario', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['CodigoEstadoPoa', 'Cantidad'], 'integer', 'min' => 0],
            [['PrecioUnitario'], 'number', 'min' => 0],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['FechaHoraRegistro'], 'safe'],
            [['IdProgramacionItem'], 'unique'],
            [['IdItem'], 'exist', 'targetClass' => Item::class, 'targetAttribute' => ['IdItem' => 'IdItem']],
            [['IdLlavePresupuestaria'], 'exist', 'targetClass' => LlavePresupuestaria::class, 'targetAttribute' => ['IdLlavePresupuestaria' => 'IdLlavePresupuestaria']],
            [['IdGestion'], 'exist', 'targetClass' => PeiGestion::class, 'targetAttribute' => ['IdGestion' => 'IdGestion']],
            [['CodigoEstado'], 'exist', 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
        ];
    }

    public function getItem(): ActiveQuery
    {
        return $this->hasOne(Item::class, ['IdItem' => 'IdItem']);
    }

    public function getLlavePresupuestaria(): ActiveQuery
    {
        return $this->hasOne(LlavePresupuestaria::class, ['IdLlavePresupuestaria' => 'IdLlavePresupuestaria']);
    }

    public function getGestion(): ActiveQuery
    {
        return $this->hasOne(PeiGestion::class, ['IdGestion' => 'IdGestion']);
    }

    public function getAsignacionesOperaciones(): ActiveQuery
    {
        return $this->hasMany(
            ProgramacionItemOperacionDtic::class,
            ['IdProgramacionItem' => 'IdProgramacionItem']
        );
    }
}
