<?php

namespace common\models;

use app\models\Autoridade;
use app\models\AutorizacionesCurso;
use app\models\CargaHorariaPropuestum;
use app\models\CHCargaHorariaExtraordinarium;
use app\models\CHComisionesSuplenciasExtraordinaria;
use app\models\CHControlAsistenciaDocente;
use app\models\CHDetalleContrato;
use app\models\CiegoConocimiento;
use app\models\DeclaracionFamilium;
use app\models\DetalleCalificacionesModalidadesGraduacion;
use app\models\DetalleFamilium;
use app\models\Diploma;
use app\models\DistribucionPersonasAula;
use app\models\DocentesCurso;
use app\models\FLTramitesPersona;
use app\models\ImpresionCarnetsAdmision;
use app\models\Lugare;
use app\models\MateriasDocente;
use app\models\MatriculasOrdenCompra;
use app\models\MejoresAlumnosCarrera;
use app\models\PersonasDatosActualizable;
use app\models\PersonasParentesco;
use app\models\PersonasPW;
use app\models\PersonasTitulo;
use app\models\PersonasTitulosModificado;
use app\models\PostulantesAdmisionesEspeciale;
use app\models\ProgramacionesHospital;
use app\models\SeguimientoAProfesionale;
use app\models\SeguimientoAProfesionalesTitulo;
use app\models\TBuDatosBeca;
use app\models\TBuDatosPadre;
use app\models\TBuDeporte;
use app\models\TBuInterno;
use app\models\TBuInvestigacion;
use app\models\TBuTrabajo;
use app\models\Universitario;
use app\models\UniversitariosSuspendido;
use app\models\Usuario;
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
 * @property Autoridade $autoridade
 * @property AutorizacionesCurso[] $autorizacionesCursos
 * @property CHCargaHorariaExtraordinarium[] $cHCargaHorariaExtraordinaria
 * @property CHComisionesSuplenciasExtraordinaria[] $cHComisionesSuplenciasExtraordinarias
 * @property CHControlAsistenciaDocente[] $cHControlAsistenciaDocentes
 * @property CHDetalleContrato[] $cHDetalleContratos
 * @property CargaHorariaPropuestum[] $cargaHorariaPropuesta
 * @property CiegoConocimiento[] $ciegoConocimientos
 * @property Usuario $codigoUsuario
 * @property DeclaracionFamilium[] $declaracionFamilia
 * @property DetalleCalificacionesModalidadesGraduacion[] $detalleCalificacionesModalidadesGraduacions
 * @property DetalleFamilium $detalleFamilium
 * @property Diploma[] $diplomas
 * @property DistribucionPersonasAula[] $distribucionPersonasAulas
 * @property DocentesCurso[] $docentesCursos
 * @property FLTramitesPersona[] $fLTramitesPersonas
 * @property Lugare $idLugarNacimiento
 * @property ImpresionCarnetsAdmision[] $impresionCarnetsAdmisions
 * @property MateriasDocente[] $materiasDocentes
 * @property MatriculasOrdenCompra[] $matriculasOrdenCompras
 * @property MejoresAlumnosCarrera[] $mejoresAlumnosCarreras
 * @property PersonasDatosActualizable[] $personasDatosActualizables
 * @property PersonasPW $personasPW
 * @property PersonasParentesco[] $personasParentescos
 * @property PersonasTitulo[] $personasTitulos
 * @property PersonasTitulosModificado[] $personasTitulosModificados
 * @property PostulantesAdmisionesEspeciale[] $postulantesAdmisionesEspeciales
 * @property ProgramacionesHospital[] $programacionesHospitals
 * @property SeguimientoAProfesionale $seguimientoAProfesionale
 * @property SeguimientoAProfesionalesTitulo[] $seguimientoAProfesionalesTitulos
 * @property TBuDatosBeca[] $tBuDatosBecas
 * @property TBuDatosPadre[] $tBuDatosPadres
 * @property TBuDeporte[] $tBuDeportes
 * @property TBuInterno $tBuInterno
 * @property TBuInvestigacion[] $tBuInvestigacions
 * @property TBuTrabajo[] $tBuTrabajos
 * @property Universitario[] $universitarios
 * @property UniversitariosSuspendido[] $universitariosSuspendidos
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
            [['IdLugarNacimiento'], 'exist', 'skipOnError' => true, 'targetClass' => Lugare::class, 'targetAttribute' => ['IdLugarNacimiento' => 'IdLugar']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
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

    public function getNombreCompleto()
    {
        return  $this->Paterno . ' ' . $this->Materno . ' ' . $this->Nombres;
    }

    /**
     * Gets query for [[Autoridade]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAutoridade()
    {
        return $this->hasOne(Autoridade::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[AutorizacionesCursos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAutorizacionesCursos()
    {
        return $this->hasMany(AutorizacionesCurso::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[CHCargaHorariaExtraordinaria]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCHCargaHorariaExtraordinaria()
    {
        return $this->hasMany(CHCargaHorariaExtraordinarium::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[CHComisionesSuplenciasExtraordinarias]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCHComisionesSuplenciasExtraordinarias()
    {
        return $this->hasMany(CHComisionesSuplenciasExtraordinaria::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[CHControlAsistenciaDocentes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCHControlAsistenciaDocentes()
    {
        return $this->hasMany(CHControlAsistenciaDocente::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[CHDetalleContratos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCHDetalleContratos()
    {
        return $this->hasMany(CHDetalleContrato::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[CargaHorariaPropuesta]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCargaHorariaPropuesta()
    {
        return $this->hasMany(CargaHorariaPropuestum::class, ['IdPersona' => 'IdPersona']);
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
        return $this->hasOne(Usuario::class, ['CodigoUsuario' => 'CodigoUsuario']);
    }

    /**
     * Gets query for [[DeclaracionFamilia]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDeclaracionFamilia()
    {
        return $this->hasMany(DeclaracionFamilium::class, ['IdRegistrador' => 'IdPersona']);
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
     * Gets query for [[DetalleFamilium]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDetalleFamilium()
    {
        return $this->hasOne(DetalleFamilium::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[Diplomas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDiplomas()
    {
        return $this->hasMany(Diploma::class, ['CI' => 'IdPersona']);
    }

    /**
     * Gets query for [[DistribucionPersonasAulas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDistribucionPersonasAulas()
    {
        return $this->hasMany(DistribucionPersonasAula::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[DocentesCursos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDocentesCursos()
    {
        return $this->hasMany(DocentesCurso::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[FLTramitesPersonas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFLTramitesPersonas()
    {
        return $this->hasMany(FLTramitesPersona::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[IdLugarNacimiento]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIdLugarNacimiento()
    {
        return $this->hasOne(Lugare::class, ['IdLugar' => 'IdLugarNacimiento']);
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
        return $this->hasMany(MateriasDocente::class, ['IdPersona' => 'IdPersona']);
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
        return $this->hasMany(MejoresAlumnosCarrera::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[PersonasDatosActualizables]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPersonasDatosActualizables()
    {
        return $this->hasMany(PersonasDatosActualizable::class, ['IDPersona' => 'IdPersona']);
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
        return $this->hasMany(PersonasTitulo::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[PersonasTitulosModificados]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPersonasTitulosModificados()
    {
        return $this->hasMany(PersonasTitulosModificado::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[PostulantesAdmisionesEspeciales]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPostulantesAdmisionesEspeciales()
    {
        return $this->hasMany(PostulantesAdmisionesEspeciale::class, ['IdPersona' => 'IdPersona']);
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
     * Gets query for [[SeguimientoAProfesionale]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSeguimientoAProfesionale()
    {
        return $this->hasOne(SeguimientoAProfesionale::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[SeguimientoAProfesionalesTitulos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSeguimientoAProfesionalesTitulos()
    {
        return $this->hasMany(SeguimientoAProfesionalesTitulo::class, ['IdPersona' => 'IdPersona']);
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
        return $this->hasMany(TBuDatosPadre::class, ['IdPersona' => 'IdPersona']);
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
     * Gets query for [[TBuInterno]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTBuInterno()
    {
        return $this->hasOne(TBuInterno::class, ['IdPersona' => 'IdPersona']);
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
        return $this->hasMany(Universitario::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[UniversitariosSuspendidos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUniversitariosSuspendidos()
    {
        return $this->hasMany(UniversitariosSuspendido::class, ['IdPersona' => 'IdPersona']);
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
