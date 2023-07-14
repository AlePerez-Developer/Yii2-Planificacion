<?php

namespace app\modules\Planificacion\models;

use common\models\Estado;
use common\models\Usuario;
use Yii;

/**
 * This is the model class for table "AperturaProgramatica".
 *
 * @property int $CodigoAperturaProgramatica
 * @property string $Da
 * @property string $Ue
 * @property string $Prg
 * @property string $Descripcion
 * @property string $FechaInicio
 * @property string $FechaFin
 * @property int $Organizacional
 * @property int $Operacional
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property Estado $codigoEstado
 * @property Usuario $codigoUsuario
 */
class AperturaProgramatica extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'AperturasProgramaticas';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['Da', 'Ue', 'Prg', 'Descripcion', 'FechaInicio', 'FechaFin', 'Organizacional', 'Operacional', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['FechaInicio', 'FechaFin', 'FechaHoraRegistro'], 'safe'],
            [['Organizacional', 'Operacional'], 'integer'],
            [['Da', 'Ue', 'Prg', 'CodigoUsuario'], 'string', 'max' => 3],
            [['Descripcion'], 'string', 'max' => 250],
            [['CodigoEstado'], 'string', 'max' => 1],
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
            'CodigoAperturaProgramatica' => 'Codigo Apertura Programatica',
            'Da' => 'Da',
            'Ue' => 'Ue',
            'Prg' => 'Prg',
            'Descripcion' => 'Descripcion',
            'FechaInicio' => 'Fecha Inicio',
            'FechaFin' => 'Fecha Fin',
            'Organizacional' => 'Organizacional',
            'Operacional' => 'Operacional',
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
        $apertura = AperturaProgramatica::find()->where(["Da" => $this->Da, "Ue"=>$this->Ue, "Prg"=>$this->Prg])->andWhere(["CodigoEstado"=>"V"])->all();
        if(!empty($apertura)){
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
