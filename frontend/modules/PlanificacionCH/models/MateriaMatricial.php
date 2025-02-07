<?php

namespace app\modules\PlanificacionCH\models;

use Yii;

/**
 * This is the model class for table "MateriasMatriciales".
 *
 * @property string $GestionAcademica
 * @property string $CodigoModalidadCurso
 * @property int $CodigoCarrera
 * @property string $SiglaMateria
 * @property int $NumeroPlanEstudios
 * @property string $Grupo
 * @property string $CodigoTipoGrupoMateria
 * @property string $GestionAcademicaCH
 * @property string $CodigoModalidadCursoCH
 * @property int $CodigoCarreraCH
 * @property string $SiglaMateriaCH
 * @property int $NumeroPlanEstudiosCH
 * @property string $GrupoCH
 * @property string $CodigoTipoGrupoMateriaCH
 * @property int $ProgramacionAgrupada
 * @property string $FechaHoraRegistro
 * @property string|null $Observaciones
 */
class MateriaMatricial extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'MateriasMatriciales';
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
            [['GestionAcademica', 'CodigoModalidadCurso', 'CodigoCarrera', 'SiglaMateria', 'NumeroPlanEstudios', 'Grupo', 'CodigoTipoGrupoMateria', 'GestionAcademicaCH', 'CodigoModalidadCursoCH', 'CodigoCarreraCH', 'SiglaMateriaCH', 'NumeroPlanEstudiosCH', 'GrupoCH', 'CodigoTipoGrupoMateriaCH'], 'required'],
            [['CodigoCarrera', 'NumeroPlanEstudios', 'CodigoCarreraCH', 'NumeroPlanEstudiosCH', 'ProgramacionAgrupada'], 'integer'],
            [['FechaHoraRegistro'], 'safe'],
            [['GestionAcademica', 'SiglaMateria', 'GestionAcademicaCH', 'SiglaMateriaCH'], 'string', 'max' => 6],
            [['CodigoModalidadCurso', 'Grupo', 'CodigoModalidadCursoCH', 'GrupoCH'], 'string', 'max' => 2],
            [['CodigoTipoGrupoMateria', 'CodigoTipoGrupoMateriaCH'], 'string', 'max' => 1],
            [['Observaciones'], 'string', 'max' => 1000],
            [['CodigoCarrera', 'CodigoModalidadCurso', 'CodigoTipoGrupoMateria', 'GestionAcademica', 'Grupo', 'NumeroPlanEstudios', 'SiglaMateria'], 'unique', 'targetAttribute' => ['CodigoCarrera', 'CodigoModalidadCurso', 'CodigoTipoGrupoMateria', 'GestionAcademica', 'Grupo', 'NumeroPlanEstudios', 'SiglaMateria']],
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
            'SiglaMateria' => 'Sigla Materia',
            'NumeroPlanEstudios' => 'Numero Plan Estudios',
            'Grupo' => 'Grupo',
            'CodigoTipoGrupoMateria' => 'Codigo Tipo Grupo Materia',
            'GestionAcademicaCH' => 'Gestion Academica Ch',
            'CodigoModalidadCursoCH' => 'Codigo Modalidad Curso Ch',
            'CodigoCarreraCH' => 'Codigo Carrera Ch',
            'SiglaMateriaCH' => 'Sigla Materia Ch',
            'NumeroPlanEstudiosCH' => 'Numero Plan Estudios Ch',
            'GrupoCH' => 'Grupo Ch',
            'CodigoTipoGrupoMateriaCH' => 'Codigo Tipo Grupo Materia Ch',
            'ProgramacionAgrupada' => 'Programacion Agrupada',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
            'Observaciones' => 'Observaciones',
        ];
    }
}
