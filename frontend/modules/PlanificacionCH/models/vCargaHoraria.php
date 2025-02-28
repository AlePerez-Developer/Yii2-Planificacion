<?php

namespace app\modules\PlanificacionCH\models;

use Yii;

/**
 * This is the model class for table "vCargasHorariasUltimo".
 *
 * @property string $CodigoFacultad
 * @property string $NombreFacultad
 * @property string $NombreCarrera
 * @property string $IdPersona
 * @property string $Paterno
 * @property string $Materno
 * @property string $Nombres
 * @property string|null $CodigoCarrera
 * @property string|null $NumeroPlanEstudios
 * @property string|null $SiglaMateria
 * @property string $NombreMateria
 * @property int $Curso
 * @property string $CodigoTipoMateria
 * @property string $Grupo
 * @property string $TipoGrupo
 * @property string $FechaInicio
 * @property string|null $FechaFin
 * @property int|null $HorasSemana
 * @property string $EstadoFuncionario
 * @property string|null $CodigoUsuario
 * @property string|null $FechaHoraRegistro
 * @property int $IdFuncionario
 * @property string $CodigoSectorTrabajo
 * @property string|null $SectorTrabajo
 * @property string|null $CodigoCondicionLaboral
 * @property string|null $CondicionLaboral
 * @property string|null $CodigoTipoDocenteMateria
 * @property int $IdCargo
 * @property string|null $CodigoMateriaDocente
 * @property string $NroItem
 * @property int $NumeroPlanEstudiosAcad
 * @property int|null $HorasSemanaAcad
 * @property string|null $CodigoAperturaProgramatica
 * @property float|null $AniosAntiguedad
 * @property int|null $Sede
 */
class vCargaHoraria extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vCargasHorariasUltimo';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('dbrrhh');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['CodigoFacultad', 'NombreFacultad', 'NombreCarrera', 'IdPersona', 'Paterno', 'Materno', 'Nombres', 'NombreMateria', 'Curso', 'CodigoTipoMateria', 'Grupo', 'TipoGrupo', 'FechaInicio', 'EstadoFuncionario', 'IdFuncionario', 'CodigoSectorTrabajo', 'IdCargo', 'NroItem', 'NumeroPlanEstudiosAcad'], 'required'],
            [['Curso', 'HorasSemana', 'IdFuncionario', 'IdCargo', 'NumeroPlanEstudiosAcad', 'HorasSemanaAcad', 'Sede'], 'integer'],
            [['FechaInicio', 'FechaFin', 'FechaHoraRegistro'], 'safe'],
            [['AniosAntiguedad'], 'number'],
            [['CodigoFacultad', 'Grupo'], 'string', 'max' => 2],
            [['NombreFacultad'], 'string', 'max' => 100],
            [['NombreCarrera'], 'string', 'max' => 150],
            [['IdPersona'], 'string', 'max' => 15],
            [['Paterno', 'Materno'], 'string', 'max' => 25],
            [['Nombres', 'CondicionLaboral'], 'string', 'max' => 50],
            [['CodigoCarrera', 'NumeroPlanEstudios', 'SiglaMateria', 'CodigoMateriaDocente'], 'string', 'max' => 12],
            [['NombreMateria'], 'string', 'max' => 250],
            [['CodigoTipoMateria', 'TipoGrupo', 'EstadoFuncionario', 'CodigoTipoDocenteMateria'], 'string', 'max' => 1],
            [['CodigoUsuario', 'CodigoSectorTrabajo', 'CodigoCondicionLaboral'], 'string', 'max' => 3],
            [['SectorTrabajo'], 'string', 'max' => 30],
            [['NroItem'], 'string', 'max' => 10],
            [['CodigoAperturaProgramatica'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'CodigoFacultad' => 'Codigo Facultad',
            'NombreFacultad' => 'Nombre Facultad',
            'NombreCarrera' => 'Nombre Carrera',
            'IdPersona' => 'Id Persona',
            'Paterno' => 'Paterno',
            'Materno' => 'Materno',
            'Nombres' => 'Nombres',
            'CodigoCarrera' => 'Codigo Carrera',
            'NumeroPlanEstudios' => 'Numero Plan Estudios',
            'SiglaMateria' => 'Sigla Materia',
            'NombreMateria' => 'Nombre Materia',
            'Curso' => 'Curso',
            'CodigoTipoMateria' => 'Codigo Tipo Materia',
            'Grupo' => 'Grupo',
            'TipoGrupo' => 'Tipo Grupo',
            'FechaInicio' => 'Fecha Inicio',
            'FechaFin' => 'Fecha Fin',
            'HorasSemana' => 'Horas Semana',
            'EstadoFuncionario' => 'Estado Funcionario',
            'CodigoUsuario' => 'Codigo Usuario',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
            'IdFuncionario' => 'Id Funcionario',
            'CodigoSectorTrabajo' => 'Codigo Sector Trabajo',
            'SectorTrabajo' => 'Sector Trabajo',
            'CodigoCondicionLaboral' => 'Codigo Condicion Laboral',
            'CondicionLaboral' => 'Condicion Laboral',
            'CodigoTipoDocenteMateria' => 'Codigo Tipo Docente Materia',
            'IdCargo' => 'Id Cargo',
            'CodigoMateriaDocente' => 'Codigo Materia Docente',
            'NroItem' => 'Nro Item',
            'NumeroPlanEstudiosAcad' => 'Numero Plan Estudios Acad',
            'HorasSemanaAcad' => 'Horas Semana Acad',
            'CodigoAperturaProgramatica' => 'Codigo Apertura Programatica',
            'AniosAntiguedad' => 'Anios Antiguedad',
            'Sede' => 'Sede',
        ];
    }
}
