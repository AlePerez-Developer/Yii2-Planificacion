<?php

namespace app\modules\Planificacion\models;

use common\models\Usuario;
use common\models\Estado;
use yii\db\ActiveRecord;
use yii\db\ActiveQuery;


/**
 * This is the model class for table "Indicadores".
 *
 * @property string $IdIndicador
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
 * @property Estado $codigoEstado
 * @property Usuario $codigoUsuario
 * @property CatCategoriaIndicador $idCategoriaIndicador
 * @property CatTipoResultado $idTipoResultado
 * @property CatUnidadIndicador $idUnidadIndicador
 * @property IndicadorEstrategico $indicadoresEstrategico
 */

class Indicador extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'Indicadores';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['IdIndicador', 'IdTipoResultado', 'IdCategoriaIndicador', 'IdUnidadIndicador'], 'string', 'max' => 36],
            [['Meta', 'Descripcion', 'LineaBase', 'IdTipoResultado', 'IdCategoriaIndicador', 'IdUnidadIndicador', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['Meta', 'LineaBase'], 'integer'],
            [['FechaHoraRegistro'], 'safe'],
            [['Descripcion'], 'string', 'max' => 500],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['IdIndicador'], 'unique'],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['IdTipoResultado'], 'exist', 'skipOnError' => true, 'targetClass' => CatTipoResultado::class, 'targetAttribute' => ['IdTipoResultado' => 'IdTipoResultado']],
            [['IdCategoriaIndicador'], 'exist', 'skipOnError' => true, 'targetClass' => CatCategoriaIndicador::class, 'targetAttribute' => ['IdCategoriaIndicador' => 'IdCategoriaIndicador']],
            [['IdUnidadIndicador'], 'exist', 'skipOnError' => true, 'targetClass' => CatUnidadIndicador::class, 'targetAttribute' => ['IdUnidadIndicador' => 'IdUnidadIndicador']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'IdIndicador' => 'Id Indicador',
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
     * Gets query for [[CodigoUsuario]].
     *
     * @return ActiveQuery
     */
    public function getCodigoUsuario(): ActiveQuery
    {
        return $this->hasOne(Usuario::class, ['CodigoUsuario' => 'CodigoUsuario']);
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
     * Gets query for [[IndicadoresEstrategico]].
     *
     * @return ActiveQuery
     * @noinspection PhpUnused
     */
    public function getIndicadoresEstrategico(): ActiveQuery
    {
        return $this->hasOne(IndicadorEstrategico::class, ['IdIndicador' => 'IdIndicador']);
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
}
