<?php

namespace app\modules\Planificacion\models;

use common\models\Usuario;
use common\models\Estado;
use yii\db\ActiveRecord;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "IndicadoresEstrategicos".
 *
 * @property string $IdIndicadorEstrategico
 * @property string $IdObjEstrategico
 * @property int $Codigo
 * @property int $Meta
 * @property string $Descripcion
 * @property int $LineaBase
 * @property string $IdTipoResultado
 * @property string $IdCategoriaIndicador
 * @property string $IdUnidadIndicador
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property CatCategoriaIndicador $catCategoriasIndicadores
 * @property CatTipoResultado $catTiposResultados
 * @property CatUnidadIndicador $catUnidadesIndicadores
 * @property Estado $estados
 * @property IndicadorEstrategicoProgramacionGestion[] $indicadorEstrategicoProgramacionGestions
 * @property ObjetivoEstrategico $objetivosEstrategicos
 * @property Usuario $usuarios
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
            [['IdIndicadorEstrategico', 'IdObjEstrategico', 'IdTipoResultado', 'IdCategoriaIndicador', 'IdUnidadIndicador'], 'string', 'max' => 36],
            [['IdObjEstrategico', 'Codigo', 'Meta', 'Descripcion', 'LineaBase', 'IdTipoResultado', 'IdCategoriaIndicador', 'IdUnidadIndicador', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['Codigo', 'Meta', 'LineaBase'], 'integer'],
            [['FechaHoraRegistro'], 'safe'],
            [['Descripcion'], 'string', 'max' => 500],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['IdIndicadorEstrategico'], 'unique'],
            [['Codigo'], 'validateUniqueActiva', 'skipOnError' => true],
            [['IdObjEstrategico'], 'exist', 'skipOnError' => true, 'targetClass' => ObjetivoEstrategico::class, 'targetAttribute' => ['IdObjEstrategico' => 'IdObjEstrategico']],
            [['IdTipoResultado'], 'exist', 'skipOnError' => true, 'targetClass' => CatTipoResultado::class, 'targetAttribute' => ['IdTipoResultado' => 'IdTipoResultado']],
            [['IdCategoriaIndicador'], 'exist', 'skipOnError' => true, 'targetClass' => CatCategoriaIndicador::class, 'targetAttribute' => ['IdCategoriaIndicador' => 'IdCategoriaIndicador']],
            [['IdUnidadIndicador'], 'exist', 'skipOnError' => true, 'targetClass' => CatUnidadIndicador::class, 'targetAttribute' => ['IdUnidadIndicador' => 'IdUnidadIndicador']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
        ];
    }

    /**
     * Valida que no exista otra política activa con el mismo código y área estratégica.
     *
     * @param string $attribute
     * @used-by rules()
     * @noinspection PhpUnused
     */
    public function validateUniqueActiva(string $attribute): void
    {
        if ($this->CodigoEstado !== 'V') {
            return;
        }

        $id = $this->IdIndicadorEstrategico == null  ? '00000000-0000-0000-0000-000000000000' : $this->IdIndicadorEstrategico;

        $exists = self::find()
            ->where([
                'Codigo' => $this->Codigo,
                'CodigoEstado' => 'V',
            ])
            ->andWhere(['<>', 'IdIndicadorEstrategico', $id]) // Evita conflicto consigo mismo en update
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
            'IdIndicadorEstrategico' => 'Id Indicador Estrategico',
            'IdObjEstrategico' => 'Id Obj Estrategico',
            'Codigo' => 'Codigo',
            'Meta' => 'Meta',
            'Descripcion' => 'Descripcion',
            'LineaBase' => 'Linea Base',
            'IdTipoResultado' => 'Id Tipo Resultado',
            'IdCategoriaIndicador' => 'Id Categoria Indicador',
            'IdUnidadIndicador' => 'Id Unidad Indicador',
            'CodigoEstado' => 'Codigo Estado',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
            'CodigoUsuario' => 'Codigo Usuario',
        ];
    }

    public static function listOne(string $id): ?IndicadorEstrategico
    {
        return self::findOne(['IdIndicadorEstrategico' => $id,['!=','CodigoEstado',Estado::ESTADO_ELIMINADO]]);
    }

    public static function listAll(): ActiveQuery
    {
        return self::find()->alias('I')
            ->select([
                'I.IdIndicadorEstrategico',
                'O.IdObjEstrategico',
                'CONCAT(a.Codigo,p.Codigo,O.Codigo) AS Compuesto',
                'I.Codigo',
                'I.Meta',
                'I.Descripcion',
                'I.LineaBase',
                'C.IdCategoriaIndicador',
                'T.IdTipoResultado',
                'U.IdUnidadIndicador',
                'I.CodigoEstado',
                'I.CodigoUsuario',
            ])
            ->joinWith('objetivosEstrategicos O', true, 'INNER JOIN')
            ->joinWith('objetivosEstrategicos.areaEstrategica a', true, 'INNER JOIN')
            ->joinWith('objetivosEstrategicos.politicaEstrategica p', true, 'INNER JOIN')
            ->joinWith('catCategoriasIndicadores C', true, 'INNER JOIN')
            ->joinWith('catTiposResultados T', true, 'INNER JOIN')
            ->joinWith('catUnidadesIndicadores U', true, 'INNER JOIN')
            ->where(['!=', 'I.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['!=', 'objetivosEstrategicos.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['!=', 'C.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['!=', 'T.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['!=', 'U.CodigoEstado', Estado::ESTADO_ELIMINADO]);
    }

    /**
     * alterna el estado del modelo V/C.
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
     * Gets a query for [[CatCategoriasIndicadores]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getCatCategoriasIndicadores(): ActiveQuery
    {
        return $this->hasOne(CatCategoriaIndicador::class, ['IdCategoriaIndicador' => 'IdCategoriaIndicador']);
    }

    /**
     * Gets a query for [[CatTiposResultados]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getCatTiposResultados(): ActiveQuery
    {
        return $this->hasOne(CatTipoResultado::class, ['IdTipoResultado' => 'IdTipoResultado']);
    }

    /**
     * Gets a query for [[CatUnidadesIndicadores]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getCatUnidadesIndicadores(): ActiveQuery
    {
        return $this->hasOne(CatUnidadIndicador::class, ['IdUnidadIndicador' => 'IdUnidadIndicador']);
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
     * Gets a query for [[IndicadorEstrategicoProgramacionGestions]].
     *
     * @return ActiveQuery
     */
    public function getIndicadorEstrategicoProgramacionGestions(): ActiveQuery
    {
        return $this->hasMany(IndicadorEstrategicoProgramacionGestion::class, ['IdIndicadorEstrategico' => 'IdIndicadorEstrategico']);
    }
}
