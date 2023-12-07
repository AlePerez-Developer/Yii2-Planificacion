<?php
namespace app\modules\Planificacion\models;

use common\models\Estado;
use common\models\Usuario;

/**
 * This is the model class for table "Unidades".
 *
 * @property int $CodigoUnidad
 * @property string $Da
 * @property string $Ue
 * @property string $Descripcion
 * @property string $Organizacional
 * @property string $FechaInicio
 * @property string $FechaFin
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property Estado $codigoEstado
 * @property Usuario $codigoUsuario
 */
class Unidad extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Unidades';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['CodigoUnidad', 'Da', 'Ue', 'Descripcion', 'Organizacional', 'FechaInicio', 'FechaFin', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['CodigoUnidad','Organizacional'], 'integer'],
            [['FechaInicio', 'FechaFin', 'FechaHoraRegistro'], 'safe'],
            [['Da', 'Ue'], 'string', 'max' => 20],
            [['Descripcion'], 'string', 'max' => 250],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['CodigoUnidad'], 'unique'],
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
            'CodigoUnidad' => 'Codigo Unidad',
            'Da' => 'Da',
            'Ue' => 'Ue',
            'Descripcion' => 'Descripcion',
            'Organizacional' => 'Organizacional',
            'FechaInicio' => 'Fecha Inicio',
            'FechaFin' => 'Fecha Fin',
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
        $unidad = Unidad::find()
            ->where('((Da = :Da) and (Ue = :Ue))',
                [':Da' => $this->Da, ':Ue' => $this->Ue]
            )
            ->andWhere(['!=','CodigoUnidad', $this->CodigoUnidad])
            ->andWhere(["CodigoEstado"=> Estado::ESTADO_VIGENTE])->all();
        if(!empty($unidad)){
            return true;
        }else{
            return false;
        }
    }

    public function isUsed()
    {
        return false;
    }
}
