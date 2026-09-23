<?php

namespace app\modules\Planificacion\models;

use common\models\Estado;
use common\models\Usuario;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property string $IdCronograma
 * @property string|null $FechaInicio
 * @property string|null $FechaFin
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property ControlEnvioPoa[] $controlesEnvio
 */
class CronogramaPoa extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'Poa.CronogramaPoa';
    }

    public function rules(): array
    {
        return [
            [['CodigoEstado', 'CodigoUsuario'], 'required'],
            [['IdCronograma'], 'string', 'max' => 36],
            [['FechaInicio', 'FechaFin', 'FechaHoraRegistro'], 'safe'],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['IdCronograma'], 'unique'],
            [['FechaFin'], 'compare', 'compareAttribute' => 'FechaInicio', 'operator' => '>=', 'type' => 'datetime'],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
        ];
    }

    public static function listOne(string $id): ?self
    {
        return self::find()
            ->where(['IdCronograma' => $id])
            ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->one();
    }

    public static function listAll(): ActiveQuery
    {
        return self::find()
            ->select([
                'IdCronograma',
                'FechaInicio',
                'FechaFin',
                'CodigoEstado',
                'CodigoUsuario',
            ])
            ->where(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->orderBy(['FechaInicio' => SORT_DESC]);
    }

    public static function vigenteHoy(): ?self
    {
        $ahora = date('Y-m-d H:i:s');
        return self::find()
            ->where(['CodigoEstado' => Estado::ESTADO_VIGENTE])
            ->andWhere(['<=', 'FechaInicio', $ahora])
            ->andWhere(['>=', 'FechaFin', $ahora])
            ->orderBy(['FechaInicio' => SORT_DESC])
            ->one();
    }

    public function cambiarEstado(): void
    {
        $this->CodigoEstado = $this->CodigoEstado === Estado::ESTADO_VIGENTE
            ? Estado::ESTADO_CADUCO
            : Estado::ESTADO_VIGENTE;
    }

    public function eliminar(): void
    {
        $this->CodigoEstado = Estado::ESTADO_ELIMINADO;
    }

    public function getControlesEnvio(): ActiveQuery
    {
        return $this->hasMany(ControlEnvioPoa::class, ['IdCronograma' => 'IdCronograma']);
    }

    public function getCodigoEstado(): ActiveQuery
    {
        return $this->hasOne(Estado::class, ['CodigoEstado' => 'CodigoEstado']);
    }

    public function getCodigoUsuario(): ActiveQuery
    {
        return $this->hasOne(Usuario::class, ['CodigoUsuario' => 'CodigoUsuario']);
    }
}
