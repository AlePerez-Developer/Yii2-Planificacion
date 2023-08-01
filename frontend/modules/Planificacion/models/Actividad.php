<?php

namespace app\modules\Planificacion\models;

use common\models\Estado;
use common\models\Usuario;
use Yii;

/**
 * This is the model class for table "Actividades".
 *
 * @property int $CodigoActividad
 * @property int $Programa
 * @property string $Codigo
 * @property string $Descripcion
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property Programa $programa
 * @property Estado $codigoEstado
 * @property Usuario $codigoUsuario
 */
class Actividad extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Actividades';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['Codigo', 'Programa', 'Descripcion', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['CodigoActividad'], 'unique'],
            [['CodigoActividad', 'Programa'], 'integer'],
            [['FechaHoraRegistro'], 'safe'],
            [['Codigo'], 'string', 'max' => 20],
            [['Descripcion'], 'string', 'max' => 250],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['Programa'], 'exist', 'skipOnError' => true, 'targetClass' => Programa::class, 'targetAttribute' => ['Programa' => 'CodigoPrograma']],
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
            'CodigoActividad' => 'Codigo Actividad',
            'Programa' => 'Programa',
            'Codigo' => 'Codigo',
            'Descripcion' => 'Descripcion',
            'CodigoEstado' => 'Codigo Estado',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
            'CodigoUsuario' => 'Codigo Usuario',
        ];
    }

    /**
     * Gets query for [[Programa]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPrograma()
    {
        return $this->hasOne(Programa::class, ['CodigoPrograma' => 'Programa']);
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
        $data = Actividad::find()->where(["Codigo" => $this->Codigo])->andWhere(["CodigoEstado"=>"E"])->all();
        if(!empty($data)){
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
