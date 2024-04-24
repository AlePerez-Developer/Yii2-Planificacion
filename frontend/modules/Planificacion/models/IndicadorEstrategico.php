<?php

namespace app\modules\Planificacion\models;

use common\models\Usuario;
use common\models\Estado;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "IndicadoresEstrategicos".
 *
 * @property int $CodigoIndicador
 * @property int $Codigo
 * @property int $Meta
 * @property string $Descripcion
 * @property int $ObjetivoEstrategico
 * @property int $Resultado
 * @property int $TipoIndicador
 * @property int $Categoria
 * @property int $Unidad
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property ObjetivoEstrategico $objetivoEstrategico
 * @property TipoResultado $resultado
 * @property TipoIndicador $tipoIndicador
 * @property CategoriaIndicador $categoria
 * @property IndicadorUnidad $unidad
 * @property Estado $codigoEstado
 * @property Usuario $codigoUsuario
 */

class IndicadorEstrategico extends ActiveRecord
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
            [['CodigoIndicador', 'Codigo', 'Descripcion', 'Meta', 'ObjetivoEstrategico', 'Resultado', 'TipoIndicador', 'Categoria', 'Unidad', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['CodigoIndicador', 'Codigo', 'Meta', 'ObjetivoEstrategico', 'Resultado', 'TipoIndicador', 'Categoria', 'Unidad'], 'integer'],
            [['FechaHoraRegistro'], 'safe'],
            [['Descripcion'], 'string', 'max' => 250],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['Codigo', 'ObjetivoEstrategico'], 'unique', 'targetAttribute' => ['Codigo', 'ObjetivoEstrategico']],
            [['CodigoIndicador'], 'unique'],
            [['ObjetivoEstrategico'], 'exist', 'skipOnError' => true, 'targetClass' => ObjetivoEstrategico::class, 'targetAttribute' => ['ObjetivoEstrategico' => 'CodigoObjEstrategico']],
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
            'Meta' => 'Meta',
            'Descripcion' => 'Descripcion',
            'Pei' => 'Pei',
            'ObjetivoEstrategico' => 'Objetivo Estrategico',
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
     * Gets query for [[ObjetivoEstrategico]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getObjetivoEstrategico()
    {
        return $this->hasOne(ObjetivoEstrategico::class, ['CodigoObjEstrategico' => 'ObjetivoEstrategico']);
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

    public function enUso()
    {
        return false;
    }

    public function exist()
    {
        $indicador = IndicadorEstrategico::find()
            ->where(['Codigo' => $this->Codigo])
            ->andWhere(['!=','CodigoIndicador', $this->CodigoIndicador])
            ->andWhere(["CodigoEstado" => Estado::ESTADO_VIGENTE])->all();
        if(!empty($indicador)){
            return true;
        }else{
            return false;
        }
    }

    public function generarProgramacion(): bool
    {
        $inicio = $this->objetivoEstrategico->codigoPei->GestionInicio;
        $fin = $this->objetivoEstrategico->codigoPei->GestionFin;
        for ($i = $inicio; $i<=$fin; $i++ )
        {
            $flag = false;
            $programacion = IndicadorEstrategicoGestion::find()->where(['IndicadorEstrategico'=>$this->CodigoIndicador, 'Gestion' => $i])->one();
            if (!$programacion){
                $programacion = new IndicadorEstrategicoGestion();
                $programacion->Gestion = $i;
                $programacion->IndicadorEstrategico = $this->CodigoIndicador;
                $programacion->Meta = 0;
                if ($programacion->validate())
                {
                    if ($programacion->save())
                    {
                        $flag = true;
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            }
        }
        return $flag;
    }
}
