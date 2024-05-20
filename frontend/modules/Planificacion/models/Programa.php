<?php

namespace app\modules\Planificacion\models;

use common\models\Usuario;
use common\models\Estado;
use Yii;

/**
 * This is the model class for table "Programas".
 *
 * @property int $CodigoPrograma
 * @property string $Codigo
 * @property string $Descripcion
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property Estado $codigoEstado
 * @property Usuario $codigoUsuario
 */
class Programa extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Programas';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['CodigoPrograma', 'Codigo', 'Descripcion', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['CodigoPrograma'], 'integer'],
            [['FechaHoraRegistro'], 'safe'],
            [['Codigo'], 'string', 'max' => 20],
            [['Descripcion'], 'string', 'max' => 250],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['CodigoPrograma'], 'unique'],
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
            'CodigoPrograma' => 'Codigo Programa',
            'Codigo' => 'Codigo',
            'Descripcion' => 'Descripcion',
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

    public function exist()
    {
        $programa = Programa::find()->where(['Codigo' => $this->Codigo])
            ->andWhere(['!=','CodigoPrograma', $this->CodigoPrograma])
            ->andWhere(["CodigoEstado" => Estado::ESTADO_VIGENTE])
            ->all();
        if(!empty($programa)){
            return true;
        }else{
            return false;
        }
    }

    public function enUso()
    {
        return false;
    }
}
