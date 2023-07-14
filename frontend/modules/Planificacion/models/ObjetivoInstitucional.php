<?php

namespace app\modules\Planificacion\models;

use common\models\Estado;
use common\models\Usuario;
use Yii;

/**
 * This is the model class for table "ObjetivosInstitucionales".
 *
 * @property int $CodigoObjInstitucional
 * @property string $CodigoCOGE
 * @property string $Objetivo
 * @property int $CodigoObjEstrategico
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property Estado $codigoEstado
 * @property ObjetivoEstrategico $codigoObjEstrategico
 * @property Usuario $codigoUsuario
 */
class ObjetivoInstitucional extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ObjetivosInstitucionales';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['CodigoObjInstitucional', 'CodigoCOGE', 'Objetivo', 'CodigoObjEstrategico', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['CodigoObjInstitucional', 'CodigoObjEstrategico'], 'integer'],
            [['FechaHoraRegistro'], 'safe'],
            [['CodigoCOGE'], 'string', 'max' => 2],
            [['Objetivo'], 'string', 'max' => 200],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['CodigoObjInstitucional'], 'unique'],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
            [['CodigoObjEstrategico'], 'exist', 'skipOnError' => true, 'targetClass' => ObjetivoEstrategico::class, 'targetAttribute' => ['CodigoObjEstrategico' => 'CodigoObjEstrategico']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'CodigoObjInstitucional' => 'Codigo Obj Institucional',
            'CodigoCOGE' => 'Codigo Coge',
            'Objetivo' => 'Objetivo',
            'CodigoObjEstrategico' => 'Codigo Obj Estrategico',
            'CodigoEstado' => 'Codigo Estado',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
            'CodigoUsuario' => 'Codigo Usuario',
        ];
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
     * Gets query for [[CodigoObjEstrategico]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCodigoObjEstrategico()
    {
        return $this->hasOne(ObjetivoEstrategico::class, ['CodigoObjEstrategico' => 'CodigoObjEstrategico']);
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
        $obj = ObjetivoInstitucional::find()->where(["CodigoCOGE" => $this->CodigoCOGE, "Objetivo"=>$this->Objetivo])->andWhere(["CodigoEstado"=>"V"])->all();
        if(!empty($obj)){
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
