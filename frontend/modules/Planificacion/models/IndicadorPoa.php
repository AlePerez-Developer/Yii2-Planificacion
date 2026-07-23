<?php

namespace app\modules\Planificacion\models;

use common\models\Estado;
use common\models\Usuario;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

class IndicadorPoa extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'IndicadoresPoa';
    }

    public function rules(): array
    {
        return [
            [['IdObjEspecifico', 'Codigo', 'Descripcion', 'Meta', 'Tipo', 'Categoria', 'Unidad', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['IdIndicadorPoa', 'IdObjEspecifico'], 'string', 'max' => 36],
            [['Codigo'], 'integer', 'min' => 1],
            [['Descripcion'], 'string', 'max' => 500],
            [['Meta'], 'integer', 'min' => 0],
            [['Tipo', 'Categoria', 'Unidad'], 'string', 'max' => 20],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['FechaHoraRegistro'], 'safe'],
            [['IdObjEspecifico'], 'exist', 'targetClass' => ObjetivoEspecifico::class, 'targetAttribute' => ['IdObjEspecifico' => 'IdObjEspecifico']],
            [['CodigoEstado'], 'exist', 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
        ];
    }

    public static function listOne(string $id): ?self
    {
        return self::find()
            ->where(['IdIndicadorPoa' => $id])
            ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->one();
    }

    public static function listAll(string $idLlavePresupuestaria, int $gestion): ActiveQuery
    {
        return self::find()->alias('IP')
            ->select([
                'IP.*',
                'OE.Compuesto AS ObjEspecificoCompuesto',
                'OE.Objetivo AS ObjetivoEspecifico',
                'OE.Producto AS ProductoEspecifico',
            ])
            ->innerJoin(
                ['OE' => ObjetivoEspecifico::listAll($idLlavePresupuestaria, $gestion)],
                'OE.IdObjEspecifico = IP.IdObjEspecifico'
            )
            ->where(['<>', 'IP.CodigoEstado', Estado::ESTADO_ELIMINADO]);
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
}
