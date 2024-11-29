<?php

namespace app\modules\PlanificacionCH\models;

use app\models\Autoridades;
use app\models\AutorizacionesCursos;
use app\models\CargaHorariaPropuesta;
use app\models\CHCargaHorariaExtraordinaria;
use app\models\CHComisionesSuplenciasExtraordinarias;
use app\models\CHControlAsistenciaDocentes;
use app\models\CHDetalleContratos;
use app\models\CiegoConocimiento;
use app\models\DeclaracionFamilia;
use app\models\DetalleCalificacionesModalidadesGraduacion;
use app\models\DetalleFamilia;
use app\models\Diplomas;
use app\models\DistribucionPersonasAulas;
use app\models\DocentesCursos;
use app\models\FLTramitesPersonas;
use app\models\ImpresionCarnetsAdmision;
use app\models\Lugares;
use app\models\MateriasDocentes;
use app\models\MatriculasOrdenCompra;
use app\models\MejoresAlumnosCarreras;
use app\models\PersonasDatosActualizables;
use app\models\PersonasParentesco;
use app\models\PersonasPW;
use app\models\PersonasTitulos;
use app\models\PersonasTitulosModificados;
use app\models\PostulantesAdmisionesEspeciales;
use app\models\ProgramacionesHospital;
use app\models\SeguimientoAProfesionales;
use app\models\SeguimientoAProfesionalesTitulos;
use app\models\TBuDatosBeca;
use app\models\TBuDatosPadres;
use app\models\TBuDeporte;
use app\models\TBuInternos;
use app\models\TBuInvestigacion;
use app\models\TBuTrabajo;
use app\models\Universitarios;
use app\models\UniversitariosSuspendidos;
use app\models\Usuarios;
use app\models\VentaValoresCaja;
use Yii;

/**
 * This is the model class for table "Personas".
 *
 * @property string $IdPersona
 * @property string|null $Paterno
 * @property string|null $Materno
 * @property string $Nombres
 * @property string|null $FechaNacimiento
 * @property string $Sexo
 * @property int $IdLugarNacimiento
 * @property string $CodigoUsuario
 * @property string $FechaHoraRegistro
 * @property string|null $Observaciones
 * @property int $Valido
 * @property string|null $CodigoNacionalidad
 * @property int $IdArchivo
 * @property int $IdLugarEmisionCI
 *
 * @property Autoridades $autoridades
 * @property AutorizacionesCursos[] $autorizacionesCursos
 * @property CHCargaHorariaExtraordinaria[] $cHCargaHorariaExtraordinarias
 * @property CHComisionesSuplenciasExtraordinarias[] $cHComisionesSuplenciasExtraordinarias
 * @property CHControlAsistenciaDocentes[] $cHControlAsistenciaDocentes
 * @property CHDetalleContratos[] $cHDetalleContratos
 * @property CargaHorariaPropuesta[] $cargaHorariaPropuestas
 * @property CiegoConocimiento[] $ciegoConocimientos
 * @property Usuarios $codigoUsuario
 * @property DeclaracionFamilia[] $declaracionFamilias
 * @property DetalleCalificacionesModalidadesGraduacion[] $detalleCalificacionesModalidadesGraduacions
 * @property DetalleFamilia $detalleFamilia
 * @property Diplomas[] $diplomas
 * @property DistribucionPersonasAulas[] $distribucionPersonasAulas
 * @property DocentesCursos[] $docentesCursos
 * @property FLTramitesPersonas[] $fLTramitesPersonas
 * @property Lugares $idLugarNacimiento
 * @property ImpresionCarnetsAdmision[] $impresionCarnetsAdmisions
 * @property MateriasDocentes[] $materiasDocentes
 * @property MatriculasOrdenCompra[] $matriculasOrdenCompras
 * @property MejoresAlumnosCarreras[] $mejoresAlumnosCarreras
 * @property PersonasDatosActualizables[] $personasDatosActualizables
 * @property PersonasPW $personasPW
 * @property PersonasParentesco[] $personasParentescos
 * @property PersonasTitulos[] $personasTitulos
 * @property PersonasTitulosModificados[] $personasTitulosModificados
 * @property PostulantesAdmisionesEspeciales[] $postulantesAdmisionesEspeciales
 * @property ProgramacionesHospital[] $programacionesHospitals
 * @property SeguimientoAProfesionales $seguimientoAProfesionales
 * @property SeguimientoAProfesionalesTitulos[] $seguimientoAProfesionalesTitulos
 * @property TBuDatosBeca[] $tBuDatosBecas
 * @property TBuDatosPadres[] $tBuDatosPadres
 * @property TBuDeporte[] $tBuDeportes
 * @property TBuInternos $tBuInternos
 * @property TBuInvestigacion[] $tBuInvestigacions
 * @property TBuTrabajo[] $tBuTrabajos
 * @property Universitarios[] $universitarios
 * @property UniversitariosSuspendidos[] $universitariosSuspendidos
 * @property VentaValoresCaja[] $ventaValoresCajas
 */
class Persona extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Personas';
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
            [['IdPersona', 'Nombres', 'IdLugarNacimiento', 'CodigoUsuario', 'IdLugarEmisionCI'], 'required'],
            [['FechaNacimiento', 'FechaHoraRegistro'], 'safe'],
            [['IdLugarNacimiento', 'Valido', 'IdLugarEmisionCI'], 'integer'],
            [['Observaciones'], 'string'],
            [['IdPersona'], 'string', 'max' => 15],
            [['Paterno', 'Materno'], 'string', 'max' => 30],
            [['Nombres'], 'string', 'max' => 40],
            [['Sexo'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['CodigoNacionalidad'], 'string', 'max' => 2],
            [['FechaNacimiento', 'Materno', 'Nombres', 'Paterno'], 'unique', 'targetAttribute' => ['FechaNacimiento', 'Materno', 'Nombres', 'Paterno']],
            [['IdPersona'], 'unique'],
            [['IdLugarNacimiento'], 'exist', 'skipOnError' => true, 'targetClass' => Lugares::class, 'targetAttribute' => ['IdLugarNacimiento' => 'IdLugar']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuarios::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'IdPersona' => 'Id Persona',
            'Paterno' => 'Paterno',
            'Materno' => 'Materno',
            'Nombres' => 'Nombres',
            'FechaNacimiento' => 'Fecha Nacimiento',
            'Sexo' => 'Sexo',
            'IdLugarNacimiento' => 'Id Lugar Nacimiento',
            'CodigoUsuario' => 'Codigo Usuario',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
            'Observaciones' => 'Observaciones',
            'Valido' => 'Valido',
            'CodigoNacionalidad' => 'Codigo Nacionalidad',
            'IdArchivo' => 'Id Archivo',
            'IdLugarEmisionCI' => 'Id Lugar Emision Ci',
        ];
    }

    /**
     * Gets query for [[Autoridades]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAutoridades()
    {
        return $this->hasOne(Autoridades::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[AutorizacionesCursos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAutorizacionesCursos()
    {
        return $this->hasMany(AutorizacionesCursos::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[CHCargaHorariaExtraordinarias]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCHCargaHorariaExtraordinarias()
    {
        return $this->hasMany(CHCargaHorariaExtraordinaria::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[CHComisionesSuplenciasExtraordinarias]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCHComisionesSuplenciasExtraordinarias()
    {
        return $this->hasMany(CHComisionesSuplenciasExtraordinarias::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[CHControlAsistenciaDocentes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCHControlAsistenciaDocentes()
    {
        return $this->hasMany(CHControlAsistenciaDocentes::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[CHDetalleContratos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCHDetalleContratos()
    {
        return $this->hasMany(CHDetalleContratos::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[CargaHorariaPropuestas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCargaHorariaPropuestas()
    {
        return $this->hasMany(CargaHorariaPropuesta::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[CiegoConocimientos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCiegoConocimientos()
    {
        return $this->hasMany(CiegoConocimiento::class, ['IdPersona' => 'IdPersona']);
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
     * Gets query for [[DeclaracionFamilias]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDeclaracionFamilias()
    {
        return $this->hasMany(DeclaracionFamilia::class, ['IdRegistrador' => 'IdPersona']);
    }

    /**
     * Gets query for [[DetalleCalificacionesModalidadesGraduacions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDetalleCalificacionesModalidadesGraduacions()
    {
        return $this->hasMany(DetalleCalificacionesModalidadesGraduacion::class, ['IdPersonaMonitor' => 'IdPersona']);
    }

    /**
     * Gets query for [[DetalleFamilia]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDetalleFamilia()
    {
        return $this->hasOne(DetalleFamilia::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[Diplomas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDiplomas()
    {
        return $this->hasMany(Diplomas::class, ['CI' => 'IdPersona']);
    }

    /**
     * Gets query for [[DistribucionPersonasAulas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDistribucionPersonasAulas()
    {
        return $this->hasMany(DistribucionPersonasAulas::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[DocentesCursos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDocentesCursos()
    {
        return $this->hasMany(DocentesCursos::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[FLTramitesPersonas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFLTramitesPersonas()
    {
        return $this->hasMany(FLTramitesPersonas::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[IdLugarNacimiento]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIdLugarNacimiento()
    {
        return $this->hasOne(Lugares::class, ['IdLugar' => 'IdLugarNacimiento']);
    }

    /**
     * Gets query for [[ImpresionCarnetsAdmisions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getImpresionCarnetsAdmisions()
    {
        return $this->hasMany(ImpresionCarnetsAdmision::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[MateriasDocentes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMateriasDocentes()
    {
        return $this->hasMany(MateriasDocentes::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[MatriculasOrdenCompras]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMatriculasOrdenCompras()
    {
        return $this->hasMany(MatriculasOrdenCompra::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[MejoresAlumnosCarreras]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMejoresAlumnosCarreras()
    {
        return $this->hasMany(MejoresAlumnosCarreras::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[PersonasDatosActualizables]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPersonasDatosActualizables()
    {
        return $this->hasMany(PersonasDatosActualizables::class, ['IDPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[PersonasPW]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPersonasPW()
    {
        return $this->hasOne(PersonasPW::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[PersonasParentescos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPersonasParentescos()
    {
        return $this->hasMany(PersonasParentesco::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[PersonasTitulos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPersonasTitulos()
    {
        return $this->hasMany(PersonasTitulos::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[PersonasTitulosModificados]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPersonasTitulosModificados()
    {
        return $this->hasMany(PersonasTitulosModificados::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[PostulantesAdmisionesEspeciales]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPostulantesAdmisionesEspeciales()
    {
        return $this->hasMany(PostulantesAdmisionesEspeciales::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[ProgramacionesHospitals]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProgramacionesHospitals()
    {
        return $this->hasMany(ProgramacionesHospital::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[SeguimientoAProfesionales]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSeguimientoAProfesionales()
    {
        return $this->hasOne(SeguimientoAProfesionales::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[SeguimientoAProfesionalesTitulos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSeguimientoAProfesionalesTitulos()
    {
        return $this->hasMany(SeguimientoAProfesionalesTitulos::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[TBuDatosBecas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTBuDatosBecas()
    {
        return $this->hasMany(TBuDatosBeca::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[TBuDatosPadres]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTBuDatosPadres()
    {
        return $this->hasMany(TBuDatosPadres::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[TBuDeportes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTBuDeportes()
    {
        return $this->hasMany(TBuDeporte::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[TBuInternos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTBuInternos()
    {
        return $this->hasOne(TBuInternos::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[TBuInvestigacions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTBuInvestigacions()
    {
        return $this->hasMany(TBuInvestigacion::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[TBuTrabajos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTBuTrabajos()
    {
        return $this->hasMany(TBuTrabajo::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[Universitarios]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUniversitarios()
    {
        return $this->hasMany(Universitarios::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[UniversitariosSuspendidos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUniversitariosSuspendidos()
    {
        return $this->hasMany(UniversitariosSuspendidos::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[VentaValoresCajas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVentaValoresCajas()
    {
        return $this->hasMany(VentaValoresCaja::class, ['IdPersona' => 'IdPersona']);
    }
}
