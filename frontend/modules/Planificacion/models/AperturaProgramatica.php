<?php

namespace app\modules\Planificacion\models;

use common\models\Estado;
use common\models\Usuario;
use Yii;

/**
 * This is the model class for table "AperturasProgramaticas".
 *
 * @property int $CodigoAperturaProgramatica
 * @property int $Unidad
 * @property int $Programa
 * @property int $Proyecto
 * @property int $Actividad
 * @property string $Descripcion
 * @property int|null $Organizacional
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property Actividad $actividad
 * @property Estado $codigoEstado
 * @property Usuario $codigoUsuario
 * @property Programa $programa
 * @property Proyecto $proyecto
 * @property Unidad $unidad
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
            [['CodigoAperturaProgramatica', 'Unidad', 'Programa', 'Proyecto', 'Actividad', 'Descripcion', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['CodigoAperturaProgramatica', 'Unidad', 'Programa', 'Proyecto', 'Actividad', 'Organizacional'], 'integer'],
            [['FechaHoraRegistro'], 'safe'],
            [['Descripcion'], 'string', 'max' => 250],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['Actividad', 'Programa', 'Proyecto', 'Unidad'], 'unique', 'targetAttribute' => ['Actividad', 'Programa', 'Proyecto', 'Unidad']],
            [['CodigoAperturaProgramatica'], 'unique'],
            [['Unidad'], 'exist', 'skipOnError' => true, 'targetClass' => Unidad::class, 'targetAttribute' => ['Unidad' => 'CodigoUnidad']],
            [['Programa'], 'exist', 'skipOnError' => true, 'targetClass' => Programa::class, 'targetAttribute' => ['Programa' => 'CodigoPrograma']],
            [['Proyecto'], 'exist', 'skipOnError' => true, 'targetClass' => Proyecto::class, 'targetAttribute' => ['Proyecto' => 'CodigoProyecto']],
            [['Actividad'], 'exist', 'skipOnError' => true, 'targetClass' => Actividad::class, 'targetAttribute' => ['Actividad' => 'CodigoActividad']],
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
            'Unidad' => 'Unidad',
            'Programa' => 'Programa',
            'Proyecto' => 'Proyecto',
            'Actividad' => 'Actividad',
            'Descripcion' => 'Descripcion',
            'Organizacional' => 'Organizacional',
            'CodigoEstado' => 'Codigo Estado',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
            'CodigoUsuario' => 'Codigo Usuario',
        ];
    }

    /**
     * Gets query for [[Unidad]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUnidad()
    {
        return $this->hasOne(Unidad::class, ['CodigoUnidad' => 'Unidad']);
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
     * Gets query for [[Proyecto]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProyecto()
    {
        return $this->hasOne(Proyecto::class, ['CodigoProyecto' => 'Proyecto']);
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
        return false;
    }

    public function enUso()
    {
        return false;
    }
}
