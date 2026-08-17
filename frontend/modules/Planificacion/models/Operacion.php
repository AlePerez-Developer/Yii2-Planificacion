<?php

namespace app\modules\Planificacion\models;

use common\models\Estado;
use common\models\Usuario;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;

class Operacion extends ActiveRecord
{
    public const TIPO_FUNCIONAMIENTO = 'Funcionamiento';
    public const TIPO_INVERSION = 'Inversion';

    public static function tableName(): string
    {
        return 'Poa.Operaciones';
    }

    public function rules(): array
    {
        return [
            [['Descripcion'], 'default', 'value' => null],
            [[
                'Codigo',
                'IdObjEspecifico',
                'IdUnidadEjecutora',
                'IdGestion',
                'IdIndicador',
                'IdLlavePresupuestaria',
                'PrimerTrimestre',
                'SegundoTrimestre',
                'TercerTrimestre',
                'CuartoTrimestre',
                'TipoOperacion',
                'IdEstadoPoa',
                'CodigoEstado',
                'CodigoUsuario',
            ], 'required'],
            [['IdOperacion', 'IdObjEspecifico', 'IdUnidadEjecutora', 'IdGestion', 'IdIndicador', 'IdLlavePresupuestaria'], 'string', 'max' => 36],
            [['PrimerTrimestre', 'SegundoTrimestre', 'TercerTrimestre', 'CuartoTrimestre'], 'integer', 'min' => 0],
            [['PrimerTrimestre'], 'validateTotalTrimestral'],
            [['IdEstadoPoa'], 'integer'],
            [['FechaHoraRegistro'], 'safe'],
            [['Codigo'], 'match', 'pattern' => '/^\d{2}$/', 'message' => 'El código debe tener exactamente dos dígitos.'],
            [['Descripcion'], 'string', 'max' => 300],
            [['TipoOperacion'], 'in', 'range' => [self::TIPO_FUNCIONAMIENTO, self::TIPO_INVERSION]],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['IdOperacion'], 'unique'],
            [['CodigoEstado'], 'exist', 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['IdIndicador'], 'exist', 'targetClass' => Indicador::class, 'targetAttribute' => ['IdIndicador' => 'IdIndicador']],
            [['CodigoUsuario'], 'exist', 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
            [['IdObjEspecifico'], 'exist', 'targetClass' => ObjetivoEspecifico::class, 'targetAttribute' => ['IdObjEspecifico' => 'IdObjEspecifico']],
            [['IdUnidadEjecutora'], 'exist', 'targetClass' => UnidadEjecutora::class, 'targetAttribute' => ['IdUnidadEjecutora' => 'IdUnidadEjecutora']],
            [['IdGestion'], 'exist', 'targetClass' => PeiGestion::class, 'targetAttribute' => ['IdGestion' => 'IdGestion']],
            [['IdLlavePresupuestaria'], 'exist', 'targetClass' => LlavePresupuestaria::class, 'targetAttribute' => ['IdLlavePresupuestaria' => 'IdLlavePresupuestaria']],
            [['IdEstadoPoa'], 'exist', 'targetClass' => EstadoPoa::class, 'targetAttribute' => ['IdEstadoPoa' => 'CodigoEstadoPOA']],
        ];
    }

    public function validateTotalTrimestral(string $attribute): void
    {
        $total = (int)$this->PrimerTrimestre
            + (int)$this->SegundoTrimestre
            + (int)$this->TercerTrimestre
            + (int)$this->CuartoTrimestre;

        if ($total > 100) {
            $this->addError($attribute, 'La programación trimestral acumulada no puede superar 100.');
        }
    }

    public static function listAll(
        string $idUnidadEjecutora,
        string $idGestion,
        int $idEstadoPoa
    ): ActiveQuery {
        return self::find()->alias('O')
            ->select([
                'O.*',
                'ObjetivoCodigo' => 'OE.Codigo',
                'ObjetivoDescripcion' => 'OE.Objetivo',
                'IndicadorDescripcion' => 'I.Descripcion',
                'IndicadorCodigo' => new Expression('COALESCE(IP.Codigo, IE.Codigo)'),
                'IndicadorTipo' => new Expression(
                    "CASE WHEN IP.IdIndicador IS NOT NULL THEN 'POA' ELSE 'Estratégico' END"
                ),
                'Llave' => 'LP.Llave',
                'LlaveDescripcion' => 'LP.Descripcion',
            ])
            ->innerJoin(['OE' => ObjetivoEspecifico::tableName()], 'OE.IdObjEspecifico = O.IdObjEspecifico')
            ->innerJoin(['I' => Indicador::tableName()], 'I.IdIndicador = O.IdIndicador')
            ->leftJoin(['IP' => IndicadorPoa::tableName()], 'IP.IdIndicador = O.IdIndicador')
            ->leftJoin(['IE' => IndicadorEstrategico::tableName()], 'IE.IdIndicador = O.IdIndicador')
            ->innerJoin(['LP' => LlavePresupuestaria::tableName()], 'LP.IdLlavePresupuestaria = O.IdLlavePresupuestaria')
            ->where([
                'O.IdUnidadEjecutora' => $idUnidadEjecutora,
                'O.IdGestion' => $idGestion,
                'O.IdEstadoPoa' => $idEstadoPoa,
            ])
            ->andWhere(['<>', 'O.CodigoEstado', Estado::ESTADO_ELIMINADO]);
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

    public function getObjetivoEspecifico(): ActiveQuery
    {
        return $this->hasOne(ObjetivoEspecifico::class, ['IdObjEspecifico' => 'IdObjEspecifico']);
    }

    public function getUnidadEjecutora(): ActiveQuery
    {
        return $this->hasOne(UnidadEjecutora::class, ['IdUnidadEjecutora' => 'IdUnidadEjecutora']);
    }

    public function getGestion(): ActiveQuery
    {
        return $this->hasOne(PeiGestion::class, ['IdGestion' => 'IdGestion']);
    }

    public function getIndicador(): ActiveQuery
    {
        return $this->hasOne(Indicador::class, ['IdIndicador' => 'IdIndicador']);
    }

    public function getEstadoPoa(): ActiveQuery
    {
        return $this->hasOne(EstadoPoa::class, ['CodigoEstadoPOA' => 'IdEstadoPoa']);
    }

    public function getLlavePresupuestaria(): ActiveQuery
    {
        return $this->hasOne(LlavePresupuestaria::class, ['IdLlavePresupuestaria' => 'IdLlavePresupuestaria']);
    }
}
