<?php

namespace app\modules\Planificacion\models;

use Yii;

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
 * @property ObjetivosEstrategico $objetivosEstrategicos
 * @property Usuario $usuarios
 */
class IndicadorEstrategico extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'IndicadoresEstrategicos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['IdIndicadorEstrategico'], 'default', 'value' => 'ewsequentialid('],
            [['FechaHoraRegistro'], 'default', 'value' => 'etdate('],
            [['IdIndicadorEstrategico', 'IdObjEstrategico', 'IdTipoResultado', 'IdCategoriaIndicador', 'IdUnidadIndicador'], 'string'],
            [['IdObjEstrategico', 'Codigo', 'Meta', 'Descripcion', 'LineaBase', 'IdTipoResultado', 'IdCategoriaIndicador', 'IdUnidadIndicador', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['Codigo', 'Meta', 'LineaBase'], 'integer'],
            [['FechaHoraRegistro'], 'safe'],
            [['Descripcion'], 'string', 'max' => 500],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['IdIndicadorEstrategico'], 'unique'],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['IdTipoResultado'], 'exist', 'skipOnError' => true, 'targetClass' => CatTiposResultado::class, 'targetAttribute' => ['IdTipoResultado' => 'IdTipoResultado']],
            [['IdCategoriaIndicador'], 'exist', 'skipOnError' => true, 'targetClass' => CatCategoriasIndicadore::class, 'targetAttribute' => ['IdCategoriaIndicador' => 'IdCategoriaIndicador']],
            [['IdUnidadIndicador'], 'exist', 'skipOnError' => true, 'targetClass' => CatUnidadesIndicadore::class, 'targetAttribute' => ['IdUnidadIndicador' => 'IdUnidadIndicador']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
            [['IdObjEstrategico'], 'exist', 'skipOnError' => true, 'targetClass' => ObjetivosEstrategico::class, 'targetAttribute' => ['IdObjEstrategico' => 'IdObjEstrategico']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
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

    /**
     * Gets query for [[CatCategoriasIndicadores]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCatCategoriasIndicadores()
    {
        return $this->hasOne(CatCategoriasIndicadore::class, ['IdCategoriaIndicador' => 'IdCategoriaIndicador']);
    }

    /**
     * Gets query for [[CatTiposResultados]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCatTiposResultados()
    {
        return $this->hasOne(CatTiposResultado::class, ['IdTipoResultado' => 'IdTipoResultado']);
    }

    /**
     * Gets query for [[CatUnidadesIndicadores]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCatUnidadesIndicadores()
    {
        return $this->hasOne(CatUnidadesIndicadore::class, ['IdUnidadIndicador' => 'IdUnidadIndicador']);
    }

    /**
     * Gets query for [[Estados]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEstados()
    {
        return $this->hasOne(Estado::class, ['CodigoEstado' => 'CodigoEstado']);
    }

    /**
     * Gets query for [[IndicadorEstrategicoProgramacionGestions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIndicadorEstrategicoProgramacionGestions()
    {
        return $this->hasMany(IndicadorEstrategicoProgramacionGestion::class, ['IdIndicadorEstrategico' => 'IdIndicadorEstrategico']);
    }

    /**
     * Gets query for [[ObjetivosEstrategicos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getObjetivosEstrategicos()
    {
        return $this->hasOne(ObjetivosEstrategico::class, ['IdObjEstrategico' => 'IdObjEstrategico']);
    }

    /**
     * Gets query for [[Usuarios]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsuarios()
    {
        return $this->hasOne(Usuario::class, ['CodigoUsuario' => 'CodigoUsuario']);
    }

}
