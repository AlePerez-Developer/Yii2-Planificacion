<?php

namespace app\modules\PlanificacionCH\models;

use app\models\Especialidades;
use app\models\Materias;
use app\models\ModalidadesCursos;
use app\models\ModalidadesCursosPlanesEstudios;
use app\models\PlanesEstudiosNivelesAcademicos;
use app\models\PlanesEstudiosTiposMaterias;
use app\models\Universitarios;
use common\models\Usuario;
use Yii;

/**
 * This is the model class for table "PlanesEstudios".
 *
 * @property int $CodigoCarrera
 * @property int $NumeroPlanEstudios
 * @property string $CodigoSistema
 * @property string $CodigoHabilitacionMateria
 * @property string $CodigoLimiteProgramacion
 * @property string $CodigoEstadoPlanEstudios
 * @property string $CodigoUsuario
 * @property string $FechaHoraRegistro
 * @property string|null $Resolucion
 * @property string|null $Annio
 * @property string|null $TipoModificacion
 * @property string|null $TipoEgreso
 * @property int $TodasGrado
 *
 * @property ModalidadesCursos[] $codigoModalidadCursos
 * @property Usuarios $codigoUsuario
 * @property Especialidades[] $especialidades
 * @property Materias[] $materias
 * @property ModalidadesCursosPlanesEstudios[] $modalidadesCursosPlanesEstudios
 * @property PlanesEstudiosNivelesAcademicos[] $planesEstudiosNivelesAcademicos
 * @property PlanesEstudiosTiposMaterias[] $planesEstudiosTiposMaterias
 * @property Universitarios[] $universitarios
 */
class PlanEstudio extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'PlanesEstudios';
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
            [['CodigoCarrera', 'NumeroPlanEstudios', 'CodigoSistema', 'CodigoHabilitacionMateria', 'CodigoLimiteProgramacion', 'CodigoUsuario'], 'required'],
            [['CodigoCarrera', 'NumeroPlanEstudios', 'TodasGrado'], 'integer'],
            [['FechaHoraRegistro'], 'safe'],
            [['CodigoSistema', 'CodigoHabilitacionMateria', 'CodigoLimiteProgramacion', 'CodigoEstadoPlanEstudios', 'TipoEgreso'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['Resolucion'], 'string', 'max' => 10],
            [['Annio'], 'string', 'max' => 50],
            [['TipoModificacion'], 'string', 'max' => 12],
            [['CodigoCarrera', 'NumeroPlanEstudios'], 'unique', 'targetAttribute' => ['CodigoCarrera', 'NumeroPlanEstudios']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'CodigoCarrera' => 'Codigo Carrera',
            'NumeroPlanEstudios' => 'Numero Plan Estudios',
            'CodigoSistema' => 'Codigo Sistema',
            'CodigoHabilitacionMateria' => 'Codigo Habilitacion Materia',
            'CodigoLimiteProgramacion' => 'Codigo Limite Programacion',
            'CodigoEstadoPlanEstudios' => 'Codigo Estado Plan Estudios',
            'CodigoUsuario' => 'Codigo Usuario',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
            'Resolucion' => 'Resolucion',
            'Annio' => 'Annio',
            'TipoModificacion' => 'Tipo Modificacion',
            'TipoEgreso' => 'Tipo Egreso',
            'TodasGrado' => 'Todas Grado',
        ];
    }

    /**
     * Gets query for [[CodigoModalidadCursos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCodigoModalidadCursos()
    {
        return $this->hasMany(ModalidadesCursos::class, ['CodigoModalidadCurso' => 'CodigoModalidadCurso'])->viaTable('ModalidadesCursosPlanesEstudios', ['CodigoCarrera' => 'CodigoCarrera', 'NumeroPlanEstudios' => 'NumeroPlanEstudios']);
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
     * Gets query for [[Especialidades]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEspecialidades()
    {
        return $this->hasMany(Especialidades::class, ['CodigoCarrera' => 'CodigoCarrera', 'NumeroPlanEstudios' => 'NumeroPlanEstudios']);
    }

    /**
     * Gets query for [[Materias]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMaterias()
    {
        return $this->hasMany(Materias::class, ['CodigoCarrera' => 'CodigoCarrera', 'NumeroPlanEstudios' => 'NumeroPlanEstudios']);
    }

    /**
     * Gets query for [[ModalidadesCursosPlanesEstudios]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getModalidadesCursosPlanesEstudios()
    {
        return $this->hasMany(ModalidadesCursosPlanesEstudios::class, ['CodigoCarrera' => 'CodigoCarrera', 'NumeroPlanEstudios' => 'NumeroPlanEstudios']);
    }

    /**
     * Gets query for [[PlanesEstudiosNivelesAcademicos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlanesEstudiosNivelesAcademicos()
    {
        return $this->hasMany(PlanesEstudiosNivelesAcademicos::class, ['CodigoCarrera' => 'CodigoCarrera', 'NumeroPlanEstudios' => 'NumeroPlanEstudios']);
    }

    /**
     * Gets query for [[PlanesEstudiosTiposMaterias]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlanesEstudiosTiposMaterias()
    {
        return $this->hasMany(PlanesEstudiosTiposMaterias::class, ['CodigoCarrera' => 'CodigoCarrera', 'NumeroPlanEstudios' => 'NumeroPlanEstudios']);
    }

    /**
     * Gets query for [[Universitarios]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUniversitarios()
    {
        return $this->hasMany(Universitarios::class, ['CodigoCarrera' => 'CodigoCarrera', 'NumeroPlanEstudiosAct' => 'NumeroPlanEstudios']);
    }
}
