<?php

namespace app\modules\Planificacion\models;

use common\models\Estado;
use common\models\Usuario;
use Yii;

/**
 * This is the model class for table "Indicadores".
 *
 * @property int $CodigoIndicador
 * @property int $Codigo
 * @property string $Descripcion
 * @property int $Articulacion
 * @property int $Resultado
 * @property int $TipoIndicador
 * @property int $Categoria
 * @property int $Unidad
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property TipoArticulacion $articulacion
 * @property CategoriaIndicador $categoria
 * @property TipoResultado $resultado
 * @property TipoIndicador $tipoIndicador
 * @property IndicadorUnidad $unidad
 * @property Estado $codigoEstado
 * @property Usuario $codigoUsuario
 */
class Indicador extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Indicadores';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['CodigoIndicador', 'Codigo', 'Descripcion', 'Articulacion', 'Resultado', 'TipoIndicador', 'Categoria', 'Unidad', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['CodigoIndicador', 'Codigo', 'Articulacion', 'Resultado', 'TipoIndicador', 'Categoria', 'Unidad'], 'integer'],
            [['FechaHoraRegistro'], 'safe'],
            [['Descripcion'], 'string', 'max' => 200],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['Articulacion', 'Codigo', 'CodigoIndicador'], 'unique', 'targetAttribute' => ['Articulacion', 'Codigo', 'CodigoIndicador']],
            [['CodigoIndicador'], 'unique'],
            [['Articulacion'], 'exist', 'skipOnError' => true, 'targetClass' => TipoArticulacion::className(), 'targetAttribute' => ['Articulacion' => 'CodigoTipo']],
            [['Resultado'], 'exist', 'skipOnError' => true, 'targetClass' => TipoResultado::className(), 'targetAttribute' => ['Resultado' => 'CodigoTipo']],
            [['TipoIndicador'], 'exist', 'skipOnError' => true, 'targetClass' => TipoIndicador::className(), 'targetAttribute' => ['TipoIndicador' => 'CodigoTipo']],
            [['Categoria'], 'exist', 'skipOnError' => true, 'targetClass' => CategoriaIndicador::className(), 'targetAttribute' => ['Categoria' => 'CodigoCategoria']],
            [['Unidad'], 'exist', 'skipOnError' => true, 'targetClass' => IndicadorUnidad::className(), 'targetAttribute' => ['Unidad' => 'CodigoTipo']],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::className(), 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::className(), 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'CodigoIndicador' => 'Codigo Indicador',
            'Codigo' => 'Codigo',
            'Descripcion' => 'Descripcion',
            'Articulacion' => 'Articulacion',
            'Resultado' => 'Resultado',
            'TipoIndicador' => 'Tipo Indicador',
            'Categoria' => 'Categoria',
            'Unidad' => 'Unidad',
            'CodigoEstado' => 'Codigo Estado',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
            'CodigoUsuario' => 'Codigo Usuario',
        ];
    }

    /**
     * Gets query for [[Articulacion]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getArticulacion()
    {
        return $this->hasOne(TipoArticulacion::className(), ['CodigoTipo' => 'Articulacion']);
    }

    /**
     * Gets query for [[Resultado]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getResultado()
    {
        return $this->hasOne(TipoResultado::className(), ['CodigoTipo' => 'Resultado']);
    }

    /**
     * Gets query for [[TipoIndicador]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTipoIndicador()
    {
        return $this->hasOne(TipoIndicador::className(), ['CodigoTipo' => 'TipoIndicador']);
    }

    /**
     * Gets query for [[Categoria]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategoria()
    {
        return $this->hasOne(CategoriaIndicador::className(), ['CodigoCategoria' => 'Categoria']);
    }

    /**
     * Gets query for [[Unidad]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUnidad()
    {
        return $this->hasOne(IndicadorUnidad::className(), ['CodigoTipo' => 'Unidad']);
    }

    /**
     * Gets query for [[CodigoEstado]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCodigoEstado()
    {
        return $this->hasOne(Estado::className(), ['CodigoEstado' => 'CodigoEstado']);
    }

    /**
     * Gets query for [[CodigoUsuario]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCodigoUsuario()
    {
        return $this->hasOne(Usuario::className(), ['CodigoUsuario' => 'CodigoUsuario']);
    }

    public function exist()
    {
        $indicador = Indicador::find()->where(["CodigoIndicador" => $this->CodigoIndicador, "Codigo"=>$this->Codigo, "Articulacion"=>$this->Articulacion])->andWhere(["CodigoEstado"=>"V"])->all();
        if(!empty($indicador)){
            return true;
        }else{
            return false;
        }
    }

    public function enUso()
    {
        /*$Obj = ObjetivoInstitucional::find()->where(["CodigoObjEstrategico" => $this->CodigoObjEstrategico])->all();
        if(!empty($Obj)){
            return true;
        }else{
            return false;
        }*/
        return false;
    }
}
