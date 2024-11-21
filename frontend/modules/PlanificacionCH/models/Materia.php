<?php

namespace app\modules\PlanificacionCH\models;

use app\models\Calificacione;
use app\models\CargaHorarium;
use app\models\ContenidosMinimo;
use app\models\DetalleCargaHorarium;
use app\models\MateriasMencione;
use app\models\MateriasPreRequisito;
use app\models\PlanesEstudio;
use app\models\PracticasDeMateriasTeorica;
use app\models\ProgramacionMateria;
use app\models\Usuario;
use Yii;

/**
 * This is the model class for table "Materias".
 *
 * @property int $CodigoCarrera
 * @property int $NumeroPlanEstudios
 * @property string $SiglaMateria
 * @property string $NombreMateria
 * @property int $Curso
 * @property int|null $HorasTeoria
 * @property int|null $HorasPractica
 * @property int|null $HorasLaboratorio
 * @property int|null $Creditos
 * @property string|null $Contenidoprogramatico
 * @property string|null $Alcance
 * @property string|null $Enfoque
 * @property string|null $TextosGuia
 * @property string $CodigoTipoMateria
 * @property string $CodigoEstadoMateria
 * @property string $CodigoUsuario
 * @property string $FechaHoraRegistro
 * @property int|null $ElijePractica
 *
 * @property Calificacione[] $calificaciones
 * @property CargaHorarium[] $cargaHoraria
 * @property PlanesEstudio $codigoCarrera
 * @property Materia[] $codigoCarreras
 * @property Materia[] $codigoCarreras0
 * @property Usuario $codigoUsuario
 * @property ContenidosMinimo[] $contenidosMinimos
 * @property DetalleCargaHorarium[] $detalleCargaHoraria
 * @property MateriasMencione[] $materiasMenciones
 * @property MateriasPreRequisito[] $materiasPreRequisitos
 * @property MateriasPreRequisito[] $materiasPreRequisitos0
 * @property PracticasDeMateriasTeorica[] $practicasDeMateriasTeoricas
 * @property ProgramacionMateria $programacionMateria
 */
class Materia extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Materias';
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
            [['CodigoCarrera', 'NumeroPlanEstudios', 'SiglaMateria', 'NombreMateria', 'Curso', 'CodigoUsuario'], 'required'],
            [['CodigoCarrera', 'NumeroPlanEstudios', 'Curso', 'HorasTeoria', 'HorasPractica', 'HorasLaboratorio', 'Creditos', 'ElijePractica'], 'integer'],
            [['Contenidoprogramatico', 'Alcance', 'Enfoque'], 'string'],
            [['FechaHoraRegistro'], 'safe'],
            [['SiglaMateria'], 'string', 'max' => 6],
            [['NombreMateria'], 'string', 'max' => 50],
            [['TextosGuia'], 'string', 'max' => 500],
            [['CodigoTipoMateria', 'CodigoEstadoMateria'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['CodigoCarrera', 'NumeroPlanEstudios', 'SiglaMateria'], 'unique', 'targetAttribute' => ['CodigoCarrera', 'NumeroPlanEstudios', 'SiglaMateria']],
            [['CodigoCarrera', 'NumeroPlanEstudios'], 'exist', 'skipOnError' => true, 'targetClass' => PlanesEstudio::class, 'targetAttribute' => ['CodigoCarrera' => 'CodigoCarrera', 'NumeroPlanEstudios' => 'NumeroPlanEstudios']],
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
            'SiglaMateria' => 'Sigla Materia',
            'NombreMateria' => 'Nombre Materia',
            'Curso' => 'Curso',
            'HorasTeoria' => 'Horas Teoria',
            'HorasPractica' => 'Horas Practica',
            'HorasLaboratorio' => 'Horas Laboratorio',
            'Creditos' => 'Creditos',
            'Contenidoprogramatico' => 'Contenidoprogramatico',
            'Alcance' => 'Alcance',
            'Enfoque' => 'Enfoque',
            'TextosGuia' => 'Textos Guia',
            'CodigoTipoMateria' => 'Codigo Tipo Materia',
            'CodigoEstadoMateria' => 'Codigo Estado Materia',
            'CodigoUsuario' => 'Codigo Usuario',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
            'ElijePractica' => 'Elije Practica',
        ];
    }

    /**
     * Gets query for [[Calificaciones]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCalificaciones()
    {
        return $this->hasMany(Calificacione::class, ['CodigoCarrera' => 'CodigoCarrera', 'NumeroPlanEstudios' => 'NumeroPlanEstudios', 'SiglaMateria' => 'SiglaMateria']);
    }

    /**
     * Gets query for [[CargaHoraria]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCargaHoraria()
    {
        return $this->hasMany(CargaHorarium::class, ['CodigoCarrera' => 'CodigoCarrera', 'NumeroPlanEstudios' => 'NumeroPlanEstudios', 'SiglaMateria' => 'SiglaMateria']);
    }

    /**
     * Gets query for [[CodigoCarrera]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCodigoCarrera()
    {
        return $this->hasOne(PlanesEstudio::class, ['CodigoCarrera' => 'CodigoCarrera', 'NumeroPlanEstudios' => 'NumeroPlanEstudios']);
    }

    /**
     * Gets query for [[CodigoCarreras]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCodigoCarreras()
    {
        return $this->hasMany(Materia::class, ['CodigoCarrera' => 'CodigoCarrera', 'NumeroPlanEstudios' => 'NumeroPlanEstudios', 'SiglaMateria' => 'SiglaMateriaPreRequisito'])->viaTable('MateriasPreRequisitos', ['CodigoCarrera' => 'CodigoCarrera', 'NumeroPlanEstudios' => 'NumeroPlanEstudios', 'SiglaMateria' => 'SiglaMateria']);
    }

    /**
     * Gets query for [[CodigoCarreras0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCodigoCarreras0()
    {
        return $this->hasMany(Materia::class, ['CodigoCarrera' => 'CodigoCarrera', 'NumeroPlanEstudios' => 'NumeroPlanEstudios', 'SiglaMateria' => 'SiglaMateria'])->viaTable('MateriasPreRequisitos', ['CodigoCarrera' => 'CodigoCarrera', 'NumeroPlanEstudios' => 'NumeroPlanEstudios', 'SiglaMateriaPreRequisito' => 'SiglaMateria']);
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

    /**
     * Gets query for [[ContenidosMinimos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getContenidosMinimos()
    {
        return $this->hasMany(ContenidosMinimo::class, ['CodigoCarrera' => 'CodigoCarrera', 'NumeroPlanEstudios' => 'NumeroPlanEstudios', 'SiglaMateria' => 'SiglaMateria']);
    }

    /**
     * Gets query for [[DetalleCargaHoraria]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDetalleCargaHoraria()
    {
        return $this->hasMany(DetalleCargaHorarium::class, ['CodigoCarrera' => 'CodigoCarrera', 'NumeroPlanEstudios' => 'NumeroPlanEstudios', 'SiglaMateria' => 'SiglaMateria']);
    }

    /**
     * Gets query for [[MateriasMenciones]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMateriasMenciones()
    {
        return $this->hasMany(MateriasMencione::class, ['CodigoCarrera' => 'CodigoCarrera', 'NumeroPlanEstudios' => 'NumeroPlanEstudios', 'SiglaMateria' => 'SiglaMateria']);
    }

    /**
     * Gets query for [[MateriasPreRequisitos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMateriasPreRequisitos()
    {
        return $this->hasMany(MateriasPreRequisito::class, ['CodigoCarrera' => 'CodigoCarrera', 'NumeroPlanEstudios' => 'NumeroPlanEstudios', 'SiglaMateria' => 'SiglaMateria']);
    }

    /**
     * Gets query for [[MateriasPreRequisitos0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMateriasPreRequisitos0()
    {
        return $this->hasMany(MateriasPreRequisito::class, ['CodigoCarrera' => 'CodigoCarrera', 'NumeroPlanEstudios' => 'NumeroPlanEstudios', 'SiglaMateriaPreRequisito' => 'SiglaMateria']);
    }

    /**
     * Gets query for [[PracticasDeMateriasTeoricas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPracticasDeMateriasTeoricas()
    {
        return $this->hasMany(PracticasDeMateriasTeorica::class, ['CodigoCarrera' => 'CodigoCarrera', 'NumeroPlanEstudios' => 'NumeroPlanEstudios', 'Siglamateria' => 'SiglaMateria']);
    }

    /**
     * Gets query for [[ProgramacionMateria]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProgramacionMateria()
    {
        return $this->hasOne(ProgramacionMateria::class, ['CodigoCarrera' => 'CodigoCarrera', 'NumeroPlanEstudios' => 'NumeroPlanEstudios', 'SiglaMateria' => 'SiglaMateria']);
    }
}
