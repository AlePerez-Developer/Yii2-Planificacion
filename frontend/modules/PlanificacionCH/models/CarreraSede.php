<?php

namespace app\modules\PlanificacionCH\models;

use app\models\CargaHoraria;
use app\models\ConfiguracionesUsuariosCarreras;
use app\models\DetalleCargaHoraria;
use app\models\MateriasDocentes;
use app\models\PlazasCarrerasGestiones;
use app\models\PostulantesAdmisionesEspeciales;
use app\models\Sedes;
use app\models\Universitarios;
use app\models\Usuarios;
use Yii;

/**
 * This is the model class for table "CarrerasSedes".
 *
 * @property int $CodigoCarrera
 * @property string $CodigoSede
 * @property string $CodigoUsuario
 * @property string $FechaHoraRegistro
 *
 * @property CargaHoraria[] $cargaHorarias
 * @property Sedes $codigoSede
 * @property Usuarios $codigoUsuario
 * @property Usuarios[] $codigoUsuarios
 * @property ConfiguracionesUsuariosCarreras[] $configuracionesUsuariosCarreras
 * @property DetalleCargaHoraria[] $detalleCargaHorarias
 * @property MateriasDocentes[] $materiasDocentes
 * @property PlazasCarrerasGestiones[] $plazasCarrerasGestiones
 * @property PostulantesAdmisionesEspeciales[] $postulantesAdmisionesEspeciales
 * @property PostulantesAdmisionesEspeciales[] $postulantesAdmisionesEspeciales0
 * @property Universitarios[] $universitarios
 */
class CarreraSede extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'CarrerasSedes';
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
            [['CodigoCarrera', 'CodigoSede', 'CodigoUsuario'], 'required'],
            [['CodigoCarrera'], 'integer'],
            [['FechaHoraRegistro'], 'safe'],
            [['CodigoSede'], 'string', 'max' => 2],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['CodigoCarrera', 'CodigoSede'], 'unique', 'targetAttribute' => ['CodigoCarrera', 'CodigoSede']],
            [['CodigoSede'], 'exist', 'skipOnError' => true, 'targetClass' => Sedes::class, 'targetAttribute' => ['CodigoSede' => 'CodigoSede']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuarios::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'CodigoCarrera' => 'Codigo Carrera',
            'CodigoSede' => 'Codigo Sede',
            'CodigoUsuario' => 'Codigo Usuario',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
        ];
    }

    /**
     * Gets query for [[CargaHorarias]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCargaHorarias()
    {
        return $this->hasMany(CargaHoraria::class, ['CodigoCarrera' => 'CodigoCarrera', 'CodigoSede' => 'CodigoSede']);
    }

    /**
     * Gets query for [[CodigoSede]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCodigoSede()
    {
        return $this->hasOne(Sedes::class, ['CodigoSede' => 'CodigoSede']);
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
     * Gets query for [[CodigoUsuarios]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCodigoUsuarios()
    {
        return $this->hasMany(Usuarios::class, ['CodigoUsuario' => 'CodigoUsuario'])->viaTable('ConfiguracionesUsuariosCarreras', ['CodigoCarrera' => 'CodigoCarrera', 'CodigoSede' => 'CodigoSede']);
    }

    /**
     * Gets query for [[ConfiguracionesUsuariosCarreras]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getConfiguracionesUsuariosCarreras()
    {
        return $this->hasMany(ConfiguracionesUsuariosCarreras::class, ['CodigoCarrera' => 'CodigoCarrera', 'CodigoSede' => 'CodigoSede']);
    }

    /**
     * Gets query for [[DetalleCargaHorarias]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDetalleCargaHorarias()
    {
        return $this->hasMany(DetalleCargaHoraria::class, ['CodigoCarrera' => 'CodigoCarrera', 'CodigoSede' => 'CodigoSede']);
    }

    /**
     * Gets query for [[MateriasDocentes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMateriasDocentes()
    {
        return $this->hasMany(MateriasDocentes::class, ['CodigoCarrera' => 'CodigoCarrera', 'CodigoSede' => 'CodigoSede']);
    }

    /**
     * Gets query for [[PlazasCarrerasGestiones]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlazasCarrerasGestiones()
    {
        return $this->hasMany(PlazasCarrerasGestiones::class, ['CodigoCarrera' => 'CodigoCarrera', 'CodigoSede' => 'CodigoSede']);
    }

    /**
     * Gets query for [[PostulantesAdmisionesEspeciales]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPostulantesAdmisionesEspeciales()
    {
        return $this->hasMany(PostulantesAdmisionesEspeciales::class, ['CodigoCarrera1' => 'CodigoCarrera', 'CodigoSede1' => 'CodigoSede']);
    }

    /**
     * Gets query for [[PostulantesAdmisionesEspeciales0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPostulantesAdmisionesEspeciales0()
    {
        return $this->hasMany(PostulantesAdmisionesEspeciales::class, ['CodigoCarrera2' => 'CodigoCarrera', 'CodigoSede2' => 'CodigoSede']);
    }

    /**
     * Gets query for [[Universitarios]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUniversitarios()
    {
        return $this->hasMany(Universitarios::class, ['CodigoCarrera' => 'CodigoCarrera', 'CodigoSede' => 'CodigoSede']);
    }
}
