<?php

namespace app\modules\PlanificacionCH\models;

use app\models\CargaHoraria;
use app\models\CargaHorariaPropuesta;
use app\models\Carreras;
use app\models\CarrerasSedes;
use app\models\CronogramaCursos;
use app\models\CuadroHonor;
use app\models\DetalleCargaHoraria;
use app\models\Matriculas;
use app\models\PERSONASAUTORIDADESFACULTAD;
use app\models\PreciosMatriculas;
use app\models\Usuarios;
use Yii;

/**
 * This is the model class for table "Sedes".
 *
 * @property string $CodigoSede
 * @property string $NombreSede
 * @property string $CodigoUsuario
 * @property string $FechaHoraRegistro
 *
 * @property CargaHorariaPropuesta[] $cargaHorariaPropuestas
 * @property CargaHoraria[] $cargaHorarias
 * @property Carreras[] $carreras
 * @property CarrerasSedes[] $carrerasSedes
 * @property Usuarios $codigoUsuario
 * @property CronogramaCursos[] $cronogramaCursos
 * @property CuadroHonor[] $cuadroHonors
 * @property DetalleCargaHoraria[] $detalleCargaHorarias
 * @property Matriculas[] $matriculas
 * @property PERSONASAUTORIDADESFACULTAD[] $pERSONASAUTORIDADESFACULTADs
 * @property PreciosMatriculas[] $preciosMatriculas
 */
class Sede extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Sedes';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('dbAcademica');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['CodigoSede', 'NombreSede', 'CodigoUsuario'], 'required'],
            [['FechaHoraRegistro'], 'safe'],
            [['CodigoSede'], 'string', 'max' => 2],
            [['NombreSede'], 'string', 'max' => 50],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['CodigoSede'], 'unique'],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuarios::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'CodigoSede' => 'Codigo Sede',
            'NombreSede' => 'Nombre Sede',
            'CodigoUsuario' => 'Codigo Usuario',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
        ];
    }

    /**
     * Gets query for [[CargaHorariaPropuestas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCargaHorariaPropuestas()
    {
        return $this->hasMany(CargaHorariaPropuesta::class, ['CodigoSede' => 'CodigoSede']);
    }

    /**
     * Gets query for [[CargaHorarias]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCargaHorarias()
    {
        return $this->hasMany(CargaHoraria::class, ['CodigoSede' => 'CodigoSede']);
    }

    /**
     * Gets query for [[Carreras]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCarreras()
    {
        return $this->hasMany(Carreras::class, ['CodigoSede' => 'CodigoSede']);
    }

    /**
     * Gets query for [[CarrerasSedes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCarrerasSedes()
    {
        return $this->hasMany(CarrerasSedes::class, ['CodigoSede' => 'CodigoSede']);
    }

    /**
     * Gets query for [[CodigoUsuario]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCodigoUsuario()
    {
        return $this->hasOne(Usuarios::class, ['CodigoUsuario' => 'CodigoUsuario']);
    }

    /**
     * Gets query for [[CronogramaCursos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCronogramaCursos()
    {
        return $this->hasMany(CronogramaCursos::class, ['CodigoSede' => 'CodigoSede']);
    }

    /**
     * Gets query for [[CuadroHonors]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCuadroHonors()
    {
        return $this->hasMany(CuadroHonor::class, ['CodigoSede' => 'CodigoSede']);
    }

    /**
     * Gets query for [[DetalleCargaHorarias]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDetalleCargaHorarias()
    {
        return $this->hasMany(DetalleCargaHoraria::class, ['CodigoSede' => 'CodigoSede']);
    }

    /**
     * Gets query for [[Matriculas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMatriculas()
    {
        return $this->hasMany(Matriculas::class, ['CodigoSede' => 'CodigoSede']);
    }

    /**
     * Gets query for [[PERSONASAUTORIDADESFACULTADs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPERSONASAUTORIDADESFACULTADs()
    {
        return $this->hasMany(PERSONASAUTORIDADESFACULTAD::class, ['CodigoSede' => 'CodigoSede']);
    }

    /**
     * Gets query for [[PreciosMatriculas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPreciosMatriculas()
    {
        return $this->hasMany(PreciosMatriculas::class, ['CodigoSede' => 'CodigoSede']);
    }
}
