<?php

namespace app\modules\Planificacion\models;

use common\models\Estado;
use common\models\Usuario;
use common\models\seguridad\EstadosPoa;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property string $IdOperacion
 * @property string $IdLlavePresupuestaria
 * @property string $IdObjEspecifico
 * @property string|null $IdIndicadorEstrategico
 * @property string|null $IdIndicadorPoa
 * @property string $IdGestion
 * @property string $IdEstadoPoa
 * @property string|null $Descripcion
 * @property int $PrimerTrimestre
 * @property int $SegundoTrimestre
 * @property int $TercerTrimestre
 * @property int $CuartoTrimestre
 * @property string|null $Codigo
 * @property string|null $CodigoCompuesto
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property ObjetivoEspecifico $objetivoEspecifico
 * @property IndicadorEstrategico|null $indicadorEstrategico
 * @property IndicadorPoa|null $indicadorPoa
 * @property LlavePresupuestaria $llavePresupuestaria
 * @property PeiGestion $peiGestion
 * @property EstadosPoa $estadoPoa
 */
class OperacionDtic extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'OperacionesDtic';
    }

    public function rules(): array
    {
        return [
            [['IdOperacion', 'IdLlavePresupuestaria', 'IdObjEspecifico', 'IdGestion', 'IdEstadoPoa'], 'string', 'max' => 36],
            [['IdIndicadorEstrategico', 'IdIndicadorPoa'], 'string', 'max' => 36],
            [[
                'IdLlavePresupuestaria', 'IdObjEspecifico', 'IdGestion', 'IdEstadoPoa',
                'PrimerTrimestre', 'SegundoTrimestre', 'TercerTrimestre', 'CuartoTrimestre',
                'CodigoEstado', 'CodigoUsuario',
            ], 'required'],
            [['PrimerTrimestre', 'SegundoTrimestre', 'TercerTrimestre', 'CuartoTrimestre'], 'integer', 'min' => 0],
            [['Descripcion'], 'string', 'max' => 500],
            [['Codigo'], 'string', 'max' => 2],
            [['CodigoCompuesto'], 'string', 'max' => 9],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['FechaHoraRegistro'], 'safe'],
            [['Codigo'], 'match', 'pattern' => '/^\d{2}$/', 'skipOnEmpty' => true],
            [['Codigo'], 'validateUniqueActiva', 'skipOnError' => true],
            [['IdLlavePresupuestaria'], 'exist', 'targetClass' => LlavePresupuestaria::class, 'targetAttribute' => ['IdLlavePresupuestaria' => 'IdLlavePresupuestaria']],
            [['IdObjEspecifico'], 'exist', 'targetClass' => ObjetivoEspecifico::class, 'targetAttribute' => ['IdObjEspecifico' => 'IdObjEspecifico']],
            [['IdIndicadorEstrategico'], 'exist', 'skipOnEmpty' => true, 'targetClass' => IndicadorEstrategico::class, 'targetAttribute' => ['IdIndicadorEstrategico' => 'IdIndicadorEstrategico']],
            [['IdIndicadorPoa'], 'exist', 'skipOnEmpty' => true, 'targetClass' => IndicadorPoa::class, 'targetAttribute' => ['IdIndicadorPoa' => 'IdIndicador']],
            [['IdGestion'], 'exist', 'targetClass' => PeiGestion::class, 'targetAttribute' => ['IdGestion' => 'IdGestion']],
            [['IdEstadoPoa'], 'exist', 'targetClass' => EstadosPoa::class, 'targetAttribute' => ['IdEstadoPoa' => 'IdEstadoPoa']],
            [['CodigoEstado'], 'exist', 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
        ];
    }

    public function validateUniqueActiva(string $attribute): void
    {
        if ($this->CodigoEstado !== Estado::ESTADO_VIGENTE) {
            return;
        }

        $id = $this->IdOperacion ?? '00000000-0000-0000-0000-000000000000';

        $exists = self::find()
            ->where([
                'IdLlavePresupuestaria' => $this->IdLlavePresupuestaria,
                'IdObjEspecifico' => $this->IdObjEspecifico,
                'IdGestion' => $this->IdGestion,
                'IdEstadoPoa' => $this->IdEstadoPoa,
                'Codigo' => $this->Codigo,
                'CodigoEstado' => Estado::ESTADO_VIGENTE,
            ])
            ->andWhere(['<>', 'IdOperacion', $id])
            ->exists();

        if ($exists) {
            $this->addError($attribute, 'El código de operación ya existe en el contexto actual.');
        }
    }

    public static function listOne(string $id): ?self
    {
        return self::find()
            ->where(['IdOperacion' => $id])
            ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->one();
    }

    public static function listAll(string $idLlavePresupuestaria, string $idGestion, string $idEstadoPoa): ActiveQuery
    {
        return self::find()->alias('Op')
            ->select([
                'Op.IdOperacion',
                'Op.IdLlavePresupuestaria',
                'Op.IdObjEspecifico',
                'Op.IdIndicadorEstrategico',
                'Op.IdIndicadorPoa',
                'Op.IdGestion',
                'Op.IdEstadoPoa',
                'Op.Descripcion',
                'Op.PrimerTrimestre',
                'Op.SegundoTrimestre',
                'Op.TercerTrimestre',
                'Op.CuartoTrimestre',
                'Op.Codigo',
                'Op.CodigoCompuesto',
                'Op.CodigoEstado',
                'Op.CodigoUsuario',
                "CONCAT(a.Codigo, p.Codigo, Oes.Codigo, Oi.Codigo, Oe.Codigo) AS CompuestoObj",
                'Objetivo' => 'Oe.Objetivo',
                'Producto' => 'Oe.Producto',
                'IndicadorEstrategicoDescripcion' => 'Ieb.Descripcion',
                'IndicadorPoaDescripcion' => 'Ipb.Descripcion',
            ])
            ->joinWith('objetivoEspecifico Oe', true, 'INNER JOIN')
            ->joinWith('objetivoEspecifico.objetivosInstitucionales Oi', true, 'INNER JOIN')
            ->joinWith('objetivoEspecifico.objetivosInstitucionales.objetivosEstrategicos Oes', true, 'INNER JOIN')
            ->joinWith('objetivoEspecifico.objetivosInstitucionales.objetivosEstrategicos.areaEstrategica a', true, 'INNER JOIN')
            ->joinWith('objetivoEspecifico.objetivosInstitucionales.objetivosEstrategicos.politicaEstrategica p', true, 'INNER JOIN')
            ->joinWith('indicadorEstrategico Ie', false, 'LEFT JOIN')
            ->joinWith('indicadorEstrategico.indicador Ieb', false, 'LEFT JOIN')
            ->joinWith('indicadorPoa Ip', false, 'LEFT JOIN')
            ->joinWith('indicadorPoa.indicador Ipb', false, 'LEFT JOIN')
            ->where(['<>', 'Op.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['Op.IdLlavePresupuestaria' => $idLlavePresupuestaria])
            ->andWhere(['Op.IdGestion' => $idGestion])
            ->andWhere(['Op.IdEstadoPoa' => $idEstadoPoa])
            ->groupBy([
                'Op.IdOperacion', 'Op.IdLlavePresupuestaria', 'Op.IdObjEspecifico',
                'Op.IdIndicadorEstrategico', 'Op.IdIndicadorPoa', 'Op.IdGestion', 'Op.IdEstadoPoa',
                'Op.Descripcion', 'Op.PrimerTrimestre', 'Op.SegundoTrimestre', 'Op.TercerTrimestre',
                'Op.CuartoTrimestre', 'Op.Codigo', 'Op.CodigoCompuesto', 'Op.CodigoEstado', 'Op.CodigoUsuario',
                'a.Codigo', 'p.Codigo', 'Oes.Codigo', 'Oi.Codigo', 'Oe.Codigo',
                'Oe.Objetivo', 'Oe.Producto', 'Ieb.Descripcion', 'Ipb.Descripcion',
            ]);
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

    public function getIndicadorEstrategico(): ActiveQuery
    {
        return $this->hasOne(IndicadorEstrategico::class, ['IdIndicadorEstrategico' => 'IdIndicadorEstrategico']);
    }

    public function getIndicadorPoa(): ActiveQuery
    {
        return $this->hasOne(IndicadorPoa::class, ['IdIndicador' => 'IdIndicadorPoa']);
    }

    public function getLlavePresupuestaria(): ActiveQuery
    {
        return $this->hasOne(LlavePresupuestaria::class, ['IdLlavePresupuestaria' => 'IdLlavePresupuestaria']);
    }

    public function getPeiGestion(): ActiveQuery
    {
        return $this->hasOne(PeiGestion::class, ['IdGestion' => 'IdGestion']);
    }

    public function getEstadoPoa(): ActiveQuery
    {
        return $this->hasOne(EstadosPoa::class, ['IdEstadoPoa' => 'IdEstadoPoa']);
    }

    public function getAsignacionesItems(): ActiveQuery
    {
        return $this->hasMany(
            ProgramacionItemOperacionDtic::class,
            ['IdOperacion' => 'IdOperacion']
        );
    }
}
