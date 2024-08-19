<?php

namespace app\modules\Planificacion\models;

use common\models\Estado;
use common\models\Usuario;
use Yii;

/**
 * This is the model class for table "Indicadores".
 *
 * @property int $CodigoIndicador
 * @property string|null $Codigo
  * @property string $Descripcion
 * @property int $Gestion
 * @property int $ObjetivoEspecifico
 * @property int $Actividad
 * @property int $Articulacion
 * @property int $Resultado
 * @property int $TipoIndicador
 * @property int $Categoria
 * @property int $Unidad
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property ObjetivoEspecifico $objetivoEspecifico
 * @property Actividad $actividad
 * @property TipoArticulacion $articulacion
 * @property TipoResultado $resultado
 * @property TipoIndicador $tipoIndicador
 * @property CategoriaIndicador $categoria
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
            [['CodigoIndicador', 'Descripcion', 'Gestion', 'ObjetivoEspecifico', 'Actividad', 'Articulacion', 'Resultado', 'TipoIndicador', 'Categoria', 'Unidad', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['CodigoIndicador', 'Gestion', 'ObjetivoEspecifico', 'Actividad', 'Articulacion', 'Resultado', 'TipoIndicador', 'Categoria', 'Unidad'], 'integer'],
            [['FechaHoraRegistro'], 'safe'],
            [['Codigo', 'CodigoUsuario'], 'string', 'max' => 3],
            [['Descripcion'], 'string', 'max' => 200],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoIndicador'], 'unique'],
            [['ObjetivoEspecifico'], 'exist', 'skipOnError' => true, 'targetClass' => ObjetivoEspecifico::class, 'targetAttribute' => ['ObjetivoEspecifico' => 'CodigoObjEspecifico']],
            [['Actividad'], 'exist', 'skipOnError' => true, 'targetClass' => Actividad::class, 'targetAttribute' => ['Actividad' => 'CodigoActividad']],
            [['Articulacion'], 'exist', 'skipOnError' => true, 'targetClass' => TipoArticulacion::class, 'targetAttribute' => ['Articulacion' => 'CodigoTipo']],
            [['Resultado'], 'exist', 'skipOnError' => true, 'targetClass' => TipoResultado::class, 'targetAttribute' => ['Resultado' => 'CodigoTipo']],
            [['TipoIndicador'], 'exist', 'skipOnError' => true, 'targetClass' => TipoIndicador::class, 'targetAttribute' => ['TipoIndicador' => 'CodigoTipo']],
            [['Categoria'], 'exist', 'skipOnError' => true, 'targetClass' => CategoriaIndicador::class, 'targetAttribute' => ['Categoria' => 'CodigoCategoria']],
            [['Unidad'], 'exist', 'skipOnError' => true, 'targetClass' => IndicadorUnidad::class, 'targetAttribute' => ['Unidad' => 'CodigoTipo']],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
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
            'Gestion' => 'Gestion',
            'ObjetivoEspecifico' => 'Objetivo Especifico',
            'Actividad' => 'Actividad',
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
     * Gets query for [[ObjetivoEspecifico]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getObjetivoEspecifico()
    {
        return $this->hasOne(ObjetivoEspecifico::class, ['CodigoObjEspecifico' => 'ObjetivoEspecifico']);
    }

    /**
     * Gets query for [[Actividad]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getActividad()
    {
        return $this->hasOne(Actividad::class, ['CodigoActividad' => 'Actividad']);
    }

    /**
     * Gets query for [[Articulacion]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getArticulacion()
    {
        return $this->hasOne(TipoArticulacion::class, ['CodigoTipo' => 'Articulacion']);
    }

    /**
     * Gets query for [[Resultado]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getResultado()
    {
        return $this->hasOne(TipoResultado::class, ['CodigoTipo' => 'Resultado']);
    }

    /**
     * Gets query for [[TipoIndicador]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTipoIndicador()
    {
        return $this->hasOne(TipoIndicador::class, ['CodigoTipo' => 'TipoIndicador']);
    }

    /**
     * Gets query for [[Categoria]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategoria()
    {
        return $this->hasOne(CategoriaIndicador::class, ['CodigoCategoria' => 'Categoria']);
    }

    /**
     * Gets query for [[Unidad]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUnidad()
    {
        return $this->hasOne(IndicadorUnidad::class, ['CodigoTipo' => 'Unidad']);
    }

    /**
     * Gets query for [[CodigoEstado]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCodigoEstado()
    {
        return $this->hasOne(Estado::class, ['CodigoEstado' => 'CodigoEstado']);
    }

    /**
     * Gets query for [[CodigoUsuario]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCodigoUsuario()
    {
        return $this->hasOne(Usuario::class, ['CodigoUsuario' => 'CodigoUsuario']);
    }

    public function exist()
    {
        /*$obj = ObjetivoEstrategico::find()->where(["CodigoCOGE" => $this->CodigoCOGE, "Objetivo"=>$this->Objetivo, "Producto"=>$this->Producto])->andWhere(["CodigoEstado"=>"V"])->all();
        if(!empty($obj)){
            return true;
        }else{
            return false;
        }*/
        return false;
    }

    public function enUso()
    {
        return false;
    }
}
