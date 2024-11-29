<?php

namespace app\modules\PlanificacionCH\models;

use app\models\CarrerasAulasPeriodo;
use app\models\CarrerasSede;
use app\models\MateriasHorario;
use app\models\Usuario;
use Yii;

/**
 * This is the model class for table "MateriasDocentes".
 *
 * @property string $GestionAcademica
 * @property string $CodigoModalidadCurso
 * @property int $CodigoCarrera
 * @property int $NumeroPlanEstudios
 * @property string $SiglaMateria
 * @property string $CodigoTipoGrupoMateria
 * @property string $Grupo
 * @property string $IdPersona
 * @property string $CodigoSEA
 * @property int $NumeroEstudiantesLimite
 * @property int $NumeroEstudiantesProgramados
 * @property int $NumeroParciales
 * @property string|null $FechaPrimerParcial
 * @property string|null $FechaSegundoParcial
 * @property string|null $FechaTercerParcial
 * @property string|null $FechaExamenFinal
 * @property string|null $FechaSegundaInstancia
 * @property string|null $FechaPracticasYLab
 * @property string $CodigoUsuario
 * @property string $FechaHoraRegistro
 * @property int|null $NumeroPracticas
 * @property int|null $NumeroLaboratorios
 * @property int $TransferidoCargaHoraria
 * @property string|null $CodigoSede
 * @property int $DependenciaTeoria
 * @property int|null $TipoLlenado
 * @property string|null $CodigoUsuarioActualizacion
 * @property string|null $FechaActualizacion
 *
 * @property CarrerasSede $codigoCarrera
 * @property CarrerasAulasPeriodo[] $codigoCarreras
 * @property Usuario $codigoUsuario
 * @property Persona $idPersona
 * @property MateriasHorario[] $materiasHorarios
 */
class MateriaDocente extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'MateriasDocentes';
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
            [['GestionAcademica', 'CodigoModalidadCurso', 'CodigoCarrera', 'NumeroPlanEstudios', 'SiglaMateria', 'Grupo', 'IdPersona', 'CodigoSEA', 'NumeroEstudiantesLimite', 'NumeroEstudiantesProgramados', 'NumeroParciales', 'CodigoUsuario'], 'required'],
            [['CodigoCarrera', 'NumeroPlanEstudios', 'NumeroEstudiantesLimite', 'NumeroEstudiantesProgramados', 'NumeroParciales', 'NumeroPracticas', 'NumeroLaboratorios', 'TransferidoCargaHoraria', 'DependenciaTeoria', 'TipoLlenado'], 'integer'],
            [['FechaPrimerParcial', 'FechaSegundoParcial', 'FechaTercerParcial', 'FechaExamenFinal', 'FechaSegundaInstancia', 'FechaPracticasYLab', 'FechaHoraRegistro', 'FechaActualizacion'], 'safe'],
            [['GestionAcademica', 'SiglaMateria'], 'string', 'max' => 6],
            [['CodigoModalidadCurso', 'Grupo', 'CodigoSede'], 'string', 'max' => 2],
            [['CodigoTipoGrupoMateria', 'CodigoSEA'], 'string', 'max' => 1],
            [['IdPersona'], 'string', 'max' => 15],
            [['CodigoUsuario', 'CodigoUsuarioActualizacion'], 'string', 'max' => 3],
            [['CodigoCarrera', 'CodigoModalidadCurso', 'CodigoTipoGrupoMateria', 'GestionAcademica', 'Grupo', 'NumeroPlanEstudios', 'SiglaMateria'], 'unique', 'targetAttribute' => ['CodigoCarrera', 'CodigoModalidadCurso', 'CodigoTipoGrupoMateria', 'GestionAcademica', 'Grupo', 'NumeroPlanEstudios', 'SiglaMateria']],
            [['IdPersona'], 'exist', 'skipOnError' => true, 'targetClass' => Persona::class, 'targetAttribute' => ['IdPersona' => 'IdPersona']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
            [['CodigoCarrera', 'CodigoSede'], 'exist', 'skipOnError' => true, 'targetClass' => CarrerasSede::class, 'targetAttribute' => ['CodigoCarrera' => 'CodigoCarrera', 'CodigoSede' => 'CodigoSede']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'GestionAcademica' => 'Gestion Academica',
            'CodigoModalidadCurso' => 'Codigo Modalidad Curso',
            'CodigoCarrera' => 'Codigo Carrera',
            'NumeroPlanEstudios' => 'Numero Plan Estudios',
            'SiglaMateria' => 'Sigla Materia',
            'CodigoTipoGrupoMateria' => 'Codigo Tipo Grupo Materia',
            'Grupo' => 'Grupo',
            'IdPersona' => 'Id Persona',
            'CodigoSEA' => 'Codigo Sea',
            'NumeroEstudiantesLimite' => 'Numero Estudiantes Limite',
            'NumeroEstudiantesProgramados' => 'Numero Estudiantes Programados',
            'NumeroParciales' => 'Numero Parciales',
            'FechaPrimerParcial' => 'Fecha Primer Parcial',
            'FechaSegundoParcial' => 'Fecha Segundo Parcial',
            'FechaTercerParcial' => 'Fecha Tercer Parcial',
            'FechaExamenFinal' => 'Fecha Examen Final',
            'FechaSegundaInstancia' => 'Fecha Segunda Instancia',
            'FechaPracticasYLab' => 'Fecha Practicas Y Lab',
            'CodigoUsuario' => 'Codigo Usuario',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
            'NumeroPracticas' => 'Numero Practicas',
            'NumeroLaboratorios' => 'Numero Laboratorios',
            'TransferidoCargaHoraria' => 'Transferido Carga Horaria',
            'CodigoSede' => 'Codigo Sede',
            'DependenciaTeoria' => 'Dependencia Teoria',
            'TipoLlenado' => 'Tipo Llenado',
            'CodigoUsuarioActualizacion' => 'Codigo Usuario Actualizacion',
            'FechaActualizacion' => 'Fecha Actualizacion',
        ];
    }

    /**
     * Gets query for [[CodigoCarrera]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCodigoCarrera()
    {
        return $this->hasOne(CarrerasSede::class, ['CodigoCarrera' => 'CodigoCarrera', 'CodigoSede' => 'CodigoSede']);
    }

    /**
     * Gets query for [[CodigoCarreras]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCodigoCarreras()
    {
        return $this->hasMany(CarrerasAulasPeriodo::class, ['CodigoCarrera' => 'CodigoCarrera', 'CodigoEdificio' => 'CodigoEdificio', 'CodigoAula' => 'CodigoAula', 'Dia' => 'Dia', 'DeHorasAHoras' => 'DeHorasAHoras'])->viaTable('MateriasHorarios', ['GestionAcademica' => 'GestionAcademica', 'CodigoModalidadCurso' => 'CodigoModalidadCurso', 'CodigoCarrera' => 'CodigoCarrera', 'NumeroPlanEstudios' => 'NumeroPlanEstudios', 'SiglaMateria' => 'SiglaMateria', 'CodigoTipoGrupoMateria' => 'CodigoTipoGrupoMateria', 'Grupo' => 'Grupo']);
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
     * Gets query for [[IdPersona]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIdPersona()
    {
        return $this->hasOne(Persona::class, ['IdPersona' => 'IdPersona']);
    }

    /**
     * Gets query for [[MateriasHorarios]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMateriasHorarios()
    {
        return $this->hasMany(MateriasHorario::class, ['GestionAcademica' => 'GestionAcademica', 'CodigoModalidadCurso' => 'CodigoModalidadCurso', 'CodigoCarrera' => 'CodigoCarrera', 'NumeroPlanEstudios' => 'NumeroPlanEstudios', 'SiglaMateria' => 'SiglaMateria', 'CodigoTipoGrupoMateria' => 'CodigoTipoGrupoMateria', 'Grupo' => 'Grupo']);
    }
}
