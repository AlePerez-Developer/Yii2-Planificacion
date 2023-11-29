<?php

namespace app\modules\Planificacion\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

use common\models\Estado;
use common\models\Usuario;

/**
 * This is the model class for table "ObjetivosEstrategicos".
 *
 * @property int $CodigoObjEstrategico
 * @property string $CodigoObjetivo
 * @property string $Objetivo
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
            [['CodigoObjEstrategico', 'CodigoObjetivo', 'Objetivo', 'CodigoPei', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['CodigoObjEstrategico', 'CodigoPei'], 'integer'],
            [['FechaHoraRegistro'], 'safe'],
            [['CodigoObjetivo', 'CodigoUsuario'], 'string', 'max' => 3],
            [['Objetivo'], 'string', 'max' => 450],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoObjEstrategico'], 'unique'],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::className(), 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::className(), 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
            [['CodigoPei'], 'exist', 'skipOnError' => true, 'targetClass' => Pei::className(), 'targetAttribute' => ['CodigoPei' => 'CodigoPei']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'CodigoObjEstrategico' => 'Codigo Obj Estrategico',
            'CodigoObjetivo' => 'Codigo Objetivo',
            'Objetivo' => 'Objetivo',
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
        return $this->hasOne(\app\modules\Planificacion\models\Pei::className(), ['CodigoPei' => 'CodigoPei']);
    }

    public function exist()
    {
        $obj = ObjetivoEstrategico::find()
            ->where('(CodigoObjetivo = :CodigoObjetivo) or (Objetivo = :Objetivo) ',
                [':CodigoObjetivo' => $this->CodigoObjetivo, ':Objetivo' => $this->Objetivo]
            )
            ->andWhere(['!=','CodigoObjEstrategico', $this->CodigoObjEstrategico])
            ->andWhere(["CodigoEstado"=> Estado::ESTADO_VIGENTE])->all();
        if(!empty($obj)){
            return true;
        }else{
            return false;
        }
    }

    public function enUso()
    {
        $Obj = ObjetivoInstitucional::find()->where(["CodigoObjEstrategico" => $this->CodigoObjEstrategico])->all();
        if(empty($Obj)){
            $Obj = IndicadorEstrategico::find()->where(["ObjetivoEstrategico" => $this->CodigoObjEstrategico])->all();
            if(empty($Obj))
                return false;
            else
                return true;
        }else{
            return true;
        }
    }
}
