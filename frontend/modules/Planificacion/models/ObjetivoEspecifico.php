<?php

namespace app\modules\Planificacion\models;

use common\models\Estado;
use common\models\Usuario;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

class ObjetivoEspecifico extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'ObjetivosEspecificos';
    }

    public function rules(): array
    {
        return [
            [['IdObjInstitucional', 'IdLlavePresupuestaria', 'Codigo', 'Objetivo', 'Producto', 'Gestion', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['IdObjEspecifico', 'IdObjInstitucional', 'IdLlavePresupuestaria'], 'string', 'max' => 36],
            [['Codigo'], 'match', 'pattern' => '/^\d{2}$/'],
            [['Objetivo', 'Producto'], 'string', 'max' => 200],
            [['Gestion'], 'integer'],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['FechaHoraRegistro'], 'safe'],
            [['IdObjInstitucional'], 'exist', 'targetClass' => ObjetivoInstitucional::class, 'targetAttribute' => ['IdObjInstitucional' => 'IdObjInstitucional']],
            [['IdLlavePresupuestaria'], 'exist', 'targetClass' => LlavePresupuestaria::class, 'targetAttribute' => ['IdLlavePresupuestaria' => 'IdLlavePresupuestaria']],
            [['CodigoEstado'], 'exist', 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
        ];
    }

    public static function listOne(string $id): ?self
    {
        return self::find()
            ->where(['IdObjEspecifico' => $id])
            ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->one();
    }

    public static function listAll(string $idLlavePresupuestaria, int $gestion): ActiveQuery
    {
        return self::find()->alias('OE')
            ->select([
                'OE.*',
                "CONCAT(OI.Compuesto, '-', OE.Codigo) AS Compuesto",
                'OI.Objetivo AS ObjetivoInstitucional',
                'OI.Producto AS ProductoInstitucional',
            ])
            ->innerJoin(
                ['OI' => ObjetivoInstitucional::listAll()],
                'OI.IdObjInstitucional = OE.IdObjInstitucional'
            )
            ->where([
                'OE.IdLlavePresupuestaria' => $idLlavePresupuestaria,
                'OE.Gestion' => $gestion,
            ])
            ->andWhere(['<>', 'OE.CodigoEstado', Estado::ESTADO_ELIMINADO]);
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

    public function getIndicadoresPoa(): ActiveQuery
    {
        return $this->hasMany(IndicadorPoa::class, ['IdObjEspecifico' => 'IdObjEspecifico']);
    }
}
