<?php

namespace app\modules\Planificacion\models;

use yii\db\ActiveRecord;

use common\models\Estado;
use common\models\Usuario;

/**
 * This is the model class for table "ObjetivosEstrategicos".
 *
 * @property int $CodigoObjEstrategico
 * @property string $CodigoCOGE
 * @property string $Objetivo
 * @property string $Producto
 * @property int $CodigoPei
 * @property string $CodigoEstado
 * @property string|null $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property Estado $codigoEstado
 * @property Usuario $codigoUsuario
 * @property PEI $codigoPei
 */

class ObjetivoEstrategico extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ObjetivosEstrategicos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['CodigoObjEstrategico', 'CodigoCOGE', 'Objetivo', 'Producto', 'CodigoPei', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['CodigoObjEstrategico', 'CodigoPei'], 'integer'],
            [['FechaHoraRegistro'], 'safe'],
            [['CodigoCOGE', 'CodigoUsuario'], 'string', 'max' => 3],
            [['Objetivo', 'Producto'], 'string', 'max' => 200],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoCOGE'], 'unique'],
            [['CodigoObjEstrategico'], 'unique'],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::className(), 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::className(), 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
            [['CodigoPei'], 'exist', 'skipOnError' => true, 'targetClass' => PEI::className(), 'targetAttribute' => ['CodigoPei' => 'CodigoPei']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'CodigoObjEstrategico' => 'Codigo Obj Estrategico',
            'CodigoCOGE' => 'Codigo Coge',
            'Objetivo' => 'Objetivo',
            'Producto' => 'Producto',
            'CodigoPei' => 'Codigo pei',
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

    /**
     * Gets query for [[CodigoPei]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCodigoPei()
    {
        return $this->hasOne(PEI::className(), ['CodigoPei' => 'CodigoPei']);
    }

    public function exist()
    {
        $obj = ObjetivoEstrategico::find()->where(["CodigoCOGE" => $this->CodigoCOGE, "Objetivo"=>$this->Objetivo, "Producto"=>$this->Producto])->andWhere(["CodigoEstado"=>"V"])->all();
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
