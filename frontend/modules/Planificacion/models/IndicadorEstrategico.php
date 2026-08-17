<?php

namespace app\modules\Planificacion\models;

use common\models\Usuario;
use common\models\Estado;
use yii\db\ActiveRecord;
use yii\db\ActiveQuery;


/**
 * This is the model class for table "IndicadoresEstrategicos".
 *
 * @property string $IdIndicador
 * @property string $IdPei
 * @property string $IdObjEstrategico
 * @property string $IdAccionEstrategica
 * @property int $Codigo
 * @property string $CodigoEstado
 *
 * @property Estado $codigoEstado
 * @property Indicador $indicador
 * @property ObjetivoEstrategico $idObjEstrategico
 * @property AccionEstrategica $idAccionEstrategica
 * @property IndicadorEstrategicoProgramacionGestion[] $indicadorEstrategicoProgramacionGestiones
 * @property PEI $idPei
 */

class IndicadorEstrategico extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'IndicadoresEstrategicos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['IdIndicador', 'IdPei', 'IdObjEstrategico', 'Codigo', 'CodigoEstado'], 'required'],
            [['IdIndicador', 'IdPei', 'IdObjEstrategico'], 'string', 'max' => 36],
            [['Codigo'], 'integer'],
            [['Codigo'], 'validateUniqueActiva', 'skipOnError' => true],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['IdIndicador'], 'unique'],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['IdIndicador'], 'exist', 'skipOnError' => true, 'targetClass' => Indicador::class, 'targetAttribute' => ['IdIndicador' => 'IdIndicador']],
            [['IdObjEstrategico'], 'exist', 'skipOnError' => true, 'targetClass' => ObjetivoEstrategico::class, 'targetAttribute' => ['IdObjEstrategico' => 'IdObjEstrategico']],
            [['IdAccionEstrategica'], 'exist', 'skipOnError' => true, 'targetClass' => AccionEstrategica::class, 'targetAttribute' => ['IdAccionEstrategica' => 'IdAccionEstrategica']],
            [['IdPei'], 'exist', 'skipOnError' => true, 'targetClass' => PEI::class, 'targetAttribute' => ['IdPei' => 'IdPei']],
        ];
    }

    /**
     * Válida que no exista otra política activa con el mismo código y área estratégica.
     *
     * @param string $attribute
     * @used-by      rules()
     * @noinspection PhpUnused
     */
    public function validateUniqueActiva(string $attribute): void
    {
        if ($this->CodigoEstado !== 'V') {
            return;
        }

        $id = $this->IdIndicador == null ? '00000000-0000-0000-0000-000000000000' : $this->IdIndicador;

        $exists = self::find()
            ->where([
                'Codigo' => $this->Codigo,
                'CodigoEstado' => 'V',
            ])
            ->andWhere(['<>', 'IdIndicador', $id]) // Evita conflicto consigo mismo en update
            ->exists();

        if ($exists) {
            $this->addError($attribute, 'El Codigo  de indicador estrategico ya existe');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'IdIndicador' => 'Id Indicador',
            'IdPei' => 'Id Pei',
            'IdObjEstrategico' => 'Id Obj Estrategico',
            'IdAccionEstrategica' => 'Id Accion Estrategica',
            'Codigo' => 'Codigo',
            'CodigoEstado' => 'Codigo Estado',
        ];
    }

    /**
     * @param string $id
     * @return IndicadorEstrategico|null
     */
    public static function listOne(string $id): ?IndicadorEstrategico
    {
        /** @var IndicadorEstrategico|null self */
        return self::find()
            ->where(['IdIndicador' => $id])
            ->andWhere(['!=', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->with('indicador')
            ->one();
    }

    /**
     * @param string $id
     * @return array
     */
    public static function listOneArray(string $id): array
    {
        $modelo = self::find()
            ->where(['IdIndicador' => $id])
            ->andWhere(['!=', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->with('indicador')
            ->one();

        return [
            'IdIndicador' => $modelo['IdIndicador'],
            'IdObjEstrategico' => $modelo['IdObjEstrategico'],
            'IdTipoResultado' => $modelo['indicador']['IdTipoResultado'] ,
            'IdCategoriaIndicador' => $modelo['indicador']['IdCategoriaIndicador'],
            'IdUnidadIndicador' => $modelo['indicador']['IdUnidadIndicador'],
            'IdAccionEstrategica' => $modelo['IdAccionEstrategica'],
            'Codigo' => $modelo['Codigo'],
            'Meta' => $modelo['indicador']['Meta'],
            'Descripcion' => $modelo['indicador']['Descripcion'],
            'LineaBase' => $modelo['indicador']['LineaBase']
        ];
    }

    /**
     * @return ActiveQuery<IndicadorEstrategico>
     */
    public static function listAll(): ActiveQuery
    {
        return self::find()->alias('I')
            ->select([
                'e.IdIndicador',
                'O.IdObjEstrategico',
                'CONCAT(a.Codigo,p.Codigo,O.Codigo) AS Compuesto',
                'I.Codigo',
                'e.Meta',
                'e.Descripcion',
                'e.LineaBase',
                'C.IdCategoriaIndicador',
                'T.IdTipoResultado',
                'U.IdUnidadIndicador',
                'Ac.IdAccionEstrategica',
                'I.CodigoEstado',
                'e.CodigoUsuario',
            ])
            ->joinWith('indicador e', true, 'INNER JOIN')
            ->joinWith('objetivosEstrategicos O', true, 'INNER JOIN')
            ->joinWith('objetivosEstrategicos.areaEstrategica a', true, 'INNER JOIN')
            ->joinWith('objetivosEstrategicos.politicaEstrategica p', true, 'INNER JOIN')
            ->joinWith('indicador.catCategoriasIndicadores C', true, 'INNER JOIN')
            ->joinWith('indicador.catTiposResultados T', true, 'INNER JOIN')
            ->joinWith('indicador.catUnidadesIndicadores U', true, 'INNER JOIN')
            ->joinWith('accionesEstrategicas Ac', true, 'INNER JOIN')
            ->where(['!=', 'I.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['!=', 'objetivosEstrategicos.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['!=', 'C.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['!=', 'T.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['!=', 'U.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['!=', 'e.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->groupBy(['e.IdIndicador', 'O.IdObjEstrategico', 'a.Codigo', 'p.Codigo', 'O.Codigo',
                'I.Codigo', 'e.Meta', 'e.Descripcion', 'e.LineaBase',
                'C.IdCategoriaIndicador', 'T.IdTipoResultado', 'U.IdUnidadIndicador', 'Ac.IdAccionEstrategica',
                'I.CodigoEstado', 'e.CodigoUsuario']);
    }

    /**
     * @return ActiveQuery<IndicadorEstrategico>
     */
    public static function listAllSimple() :ActiveQuery
    {
        return self::find()->alias('E')
            ->select([
                'I.IdIndicador',
                'E.Codigo',
                'I.Meta',
                'I.Descripcion',
                'I.LineaBase',
                'I.CodigoEstado',
                'I.CodigoUsuario',
            ])
            ->joinWith('indicador I', true, 'INNER JOIN')
            ->where(['!=', 'I.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->groupBy(['I.IdIndicador', 'E.Codigo', 'I.Meta', 'I.Descripcion', 'I.LineaBase',
                'I.CodigoEstado', 'I.CodigoUsuario']
            );
    }

    /**
     * Alterna el estado del modelo V/C.
     *
     * @return void
     */
    public function cambiarEstado(): void
    {
        $this->CodigoEstado = $this->CodigoEstado == Estado::ESTADO_VIGENTE
            ? Estado::ESTADO_CADUCO
            : Estado::ESTADO_VIGENTE;
    }

    /**
     * realiza el soft delete de un registro.
     *
     * @return void
     */
    public function eliminar(): void
    {
        $this->CodigoEstado = Estado::ESTADO_ELIMINADO;
    }



    /**
     * Gets query for [[CodigoEstado]].
     *
     * @return ActiveQuery
     */
    public function getCodigoEstado(): ActiveQuery
    {
        return $this->hasOne(Estado::class, ['CodigoEstado' => 'CodigoEstado']);
    }

    /**
     * Gets query for [[IdIndicador]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getIndicador(): ActiveQuery
    {
        return $this->hasOne(Indicador::class, ['IdIndicador' => 'IdIndicador']);
    }

    /**
     * Gets query for [[ObjetivosEstrategicos]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getObjetivosEstrategicos(): ActiveQuery
    {
        return $this->hasOne(ObjetivoEstrategico::class, ['IdObjEstrategico' => 'IdObjEstrategico']);
    }

    /**
     * Gets query for [[IdAccionEstrategica]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getAccionesEstrategicas(): ActiveQuery
    {
        return $this->hasOne(AccionEstrategica::class, ['IdAccionEstrategica' => 'IdAccionEstrategica']);
    }

    /**
     * Gets query for [[IdPei]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getIdPei(): ActiveQuery
    {
        return $this->hasOne(PEI::class, ['IdPei' => 'IdPei']);
    }


    /**
     * Gets a query for [[Estados]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getEstados(): ActiveQuery
    {
        return $this->hasOne(Estado::class, ['CodigoEstado' => 'CodigoEstado']);
    }

    /**
     * Gets a query for [[Usuarios]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getUsuarios(): ActiveQuery
    {
        return $this->hasOne(Usuario::class, ['CodigoUsuario' => 'CodigoUsuario']);
    }

    /**
     * Gets query for [[ProgramacionesIndicadoresGestiones]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getProgramacionesIndicadoresGestiones(): ActiveQuery
    {
        return $this->hasMany(ProgramacionIndicadorGestion::class, ['IdIndicadorEstrategico' => 'IdIndicador']);
    }

    /**
     * Gets a query for [[IndicadorEstrategicoProgramacionGestions]].
     *
     * @return ActiveQuery
     */
    public function getIndicadorEstrategicoProgramacionGestiones(): ActiveQuery
    {
        return $this->hasMany(IndicadorEstrategicoProgramacionGestion::class, ['IdIndicadorEstrategico' => 'IdIndicador']);
    }
}
