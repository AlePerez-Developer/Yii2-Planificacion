<?php

namespace app\modules\Planificacion\models;

use common\models\Estado;
use common\models\Usuario;
use Yii;

/**
 * This is the model class for table "ObjetivosEspecificos".
 *
 * @property int $CodigoObjEspecifico
 * @property string $CodigoCOGE
 * @property string $Objetivo
 * @property int $CodigoObjInstitucional
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property Estado $codigoEstado
 * @property ObjetivoInstitucional $codigoObjInstitucional
 * @property Usuario $codigoUsuario
 */
class ObjetivoEspecifico extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ObjetivosEspecificos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['CodigoObjEspecifico', 'CodigoCOGE', 'Objetivo', 'CodigoObjInstitucional', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['CodigoObjEspecifico', 'CodigoObjInstitucional'], 'integer'],
            [['FechaHoraRegistro'], 'safe'],
            [['CodigoCOGE'], 'string', 'max' => 2],
            [['Objetivo'], 'string', 'max' => 200],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['CodigoObjEspecifico'], 'unique'],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
            [['CodigoObjInstitucional'], 'exist', 'skipOnError' => true, 'targetClass' => ObjetivoInstitucional::class, 'targetAttribute' => ['CodigoObjInstitucional' => 'CodigoObjInstitucional']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'CodigoObjEspecifico' => 'Codigo Obj Especifico',
            'CodigoCOGE' => 'Codigo Coge',
            'Objetivo' => 'Objetivo',
            'CodigoObjInstitucional' => 'Codigo Obj Institucional',
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
     * Gets query for [[CodigoObjInstitucional]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCodigoObjInstitucional()
    {
        return $this->hasOne(ObjetivoInstitucional::class, ['CodigoObjInstitucional' => 'CodigoObjInstitucional']);
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
        $obj = ObjetivoEspecifico::find()->where(["CodigoCOGE" => $this->CodigoCOGE, "Objetivo"=>$this->Objetivo])->andWhere(["CodigoEstado"=>"V"])->all();
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
