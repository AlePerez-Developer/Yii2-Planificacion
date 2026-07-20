<?php

namespace app\modules\Planificacion\models;

use common\models\Estado;
use common\models\Usuario;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property string $IdObjInstitucional
 * @property string $IdObjEstrategico
 * @property string $Codigo
 * @property string $Objetivo
 * @property string $Producto
 * @property int $Gestion
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 */
class ObjetivoInstitucional extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'ObjetivosInstitucionales';
    }

    public function rules(): array
    {
        return [
            [['IdObjEstrategico', 'Codigo', 'Objetivo', 'Producto', 'Gestion', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['IdObjInstitucional', 'IdObjEstrategico'], 'string', 'max' => 36],
            [['Codigo'], 'string', 'length' => 2],
            [['Codigo'], 'match', 'pattern' => '/^\d{2}$/'],
            [['Objetivo', 'Producto'], 'string', 'max' => 200],
            [['Gestion'], 'integer'],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['FechaHoraRegistro'], 'safe'],
            [['IdObjInstitucional'], 'unique'],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
            [['IdObjEstrategico'], 'exist', 'skipOnError' => true, 'targetClass' => ObjetivoEstrategico::class, 'targetAttribute' => ['IdObjEstrategico' => 'IdObjEstrategico']],
        ];
    }

    public static function listOne(string $id): ?self
    {
        return self::find()
            ->where(['IdObjInstitucional' => $id])
            ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->one();
    }

    public static function listAll(string $search = ''): ActiveQuery
    {
        $query = self::find()->alias('OI')
            ->select([
                'OI.IdObjInstitucional',
                'OI.IdObjEstrategico',
                "CONCAT(OE.Compuesto, '-', OI.Codigo) AS Compuesto",
                'OI.Codigo',
                'OI.Objetivo',
                'OI.Producto',
                'OI.Gestion',
                'OI.CodigoEstado',
                'OI.CodigoUsuario',
                'OE.Objetivo AS ObjetivoEstrategico',
                'OE.Producto AS ProductoEstrategico',
            ])
            ->innerJoin(
                ['OE' => ObjetivoEstrategico::listAll()],
                'OE.IdObjEstrategico = OI.IdObjEstrategico'
            )
            ->where(['<>', 'OI.CodigoEstado', Estado::ESTADO_ELIMINADO]);

        if ($search !== '') {
            $query->andWhere([
                'or',
                ['like', 'OI.Codigo', $search],
                ['like', 'OI.Objetivo', $search],
                ['like', 'OI.Producto', $search],
                ['like', 'OE.Objetivo', $search],
            ]);
        }

        return $query;
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

    public function getObjetivoEstrategico(): ActiveQuery
    {
        return $this->hasOne(ObjetivoEstrategico::class, ['IdObjEstrategico' => 'IdObjEstrategico']);
    }

    public function getObjetivosEspecificos(): ActiveQuery
    {
        return $this->hasMany(ObjetivoEspecifico::class, ['IdObjInstitucional' => 'IdObjInstitucional']);
    }
}
