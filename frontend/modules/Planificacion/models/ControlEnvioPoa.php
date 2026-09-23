<?php

namespace app\modules\Planificacion\models;

use common\models\Estado;
use common\models\Usuario;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property string $IdControlEnvio
 * @property string $IdCronograma
 * @property string $IdUnidadEjecutora
 * @property int|null $Estado
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property CronogramaPoa $cronograma
 * @property UnidadEjecutora $unidadEjecutora
 */
class ControlEnvioPoa extends ActiveRecord
{
    public const ESTADO_ENVIADO = 1;

    public static function tableName(): string
    {
        return 'Poa.ControlEnvioPoa';
    }

    public function rules(): array
    {
        return [
            [['IdCronograma', 'IdUnidadEjecutora', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['IdControlEnvio', 'IdCronograma', 'IdUnidadEjecutora'], 'string', 'max' => 36],
            [['Estado'], 'integer'],
            [['FechaHoraRegistro'], 'safe'],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['IdControlEnvio'], 'unique'],
            [['IdCronograma'], 'exist', 'skipOnError' => true, 'targetClass' => CronogramaPoa::class, 'targetAttribute' => ['IdCronograma' => 'IdCronograma']],
            [['IdUnidadEjecutora'], 'exist', 'skipOnError' => true, 'targetClass' => UnidadEjecutora::class, 'targetAttribute' => ['IdUnidadEjecutora' => 'IdUnidadEjecutora']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
        ];
    }

    public static function listOne(string $id): ?self
    {
        return self::find()
            ->where(['IdControlEnvio' => $id])
            ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->one();
    }

    public static function listAll(): ActiveQuery
    {
        return self::find()->alias('CE')
            ->select([
                'CE.IdControlEnvio',
                'CE.IdCronograma',
                'CE.IdUnidadEjecutora',
                'CE.Estado',
                'CE.CodigoEstado',
                'CE.CodigoUsuario',
                'CE.FechaHoraRegistro',
                'UnidadDescripcion' => 'U.Descripcion',
                'UnidadCodigo' => 'U.Ue',
                'FechaInicio' => 'C.FechaInicio',
                'FechaFin' => 'C.FechaFin',
            ])
            ->innerJoin(['U' => UnidadEjecutora::tableName()], 'U.IdUnidadEjecutora = CE.IdUnidadEjecutora')
            ->innerJoin(['C' => CronogramaPoa::tableName()], 'C.IdCronograma = CE.IdCronograma')
            ->where(['<>', 'CE.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->orderBy(['CE.FechaHoraRegistro' => SORT_DESC]);
    }

    public static function envioActivo(string $idUnidadEjecutora, string $idCronograma): ?self
    {
        return self::find()
            ->where([
                'IdUnidadEjecutora' => $idUnidadEjecutora,
                'IdCronograma' => $idCronograma,
                'Estado' => self::ESTADO_ENVIADO,
            ])
            ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->one();
    }

    public function eliminar(): void
    {
        $this->CodigoEstado = Estado::ESTADO_ELIMINADO;
    }

    public function getCronograma(): ActiveQuery
    {
        return $this->hasOne(CronogramaPoa::class, ['IdCronograma' => 'IdCronograma']);
    }

    public function getUnidadEjecutora(): ActiveQuery
    {
        return $this->hasOne(UnidadEjecutora::class, ['IdUnidadEjecutora' => 'IdUnidadEjecutora']);
    }

    public function getCodigoUsuario(): ActiveQuery
    {
        return $this->hasOne(Usuario::class, ['CodigoUsuario' => 'CodigoUsuario']);
    }
}
