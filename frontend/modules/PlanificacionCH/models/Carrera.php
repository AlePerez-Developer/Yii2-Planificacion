<?php

namespace app\modules\PlanificacionCH\models;

use app\models\CargaHorarium;
use app\models\Facultade;
use app\models\NivelesAcademico;
use Yii;

/**
 * This is the model class for table "Carreras".
 *
 * @property int $CodigoCarrera
 * @property string $NombreCarrera
 * @property string $NombreCortoCarrera
 * @property string $SiglaCarrera
 * @property string $Direccion
 * @property string $Telefono
 * @property string|null $Fax
 * @property string|null $Email
 * @property int $NumeroPlanesEstudios
 * @property int $NumeroPlanEstudiosDef
 * @property int $NumeroCUsEmitidos
 * @property string $CodigoFacultad
 * @property int $NumeroOrdenImpresion
 * @property string $CodigoEstadoCarrera
 * @property string|null $Numero
 * @property string|null $CodigoArea
 * @property string $TipoGrupo
 * @property string|null $AnnioCreacion
 * @property string|null $Resolucion
 * @property int $CambioNombre
 * @property int $Programa
 * @property int $CarreraIndependiente
 * @property int $ModificaSea
 * @property string $CodigoSede
 * @property string $CodigoNivelAcademico
 *
 * @property CargaHorarium[] $cargaHoraria
 * @property Facultade $codigoFacultad
 * @property NivelesAcademico $codigoNivelAcademico
 * @property Sede $codigoSede
 */
class Carrera extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Carreras';
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
            [['CodigoCarrera', 'NombreCarrera', 'NombreCortoCarrera', 'SiglaCarrera', 'Direccion', 'Telefono', 'CodigoFacultad', 'NumeroOrdenImpresion', 'CambioNombre', 'Programa', 'CarreraIndependiente', 'CodigoNivelAcademico'], 'required'],
            [['CodigoCarrera', 'NumeroPlanesEstudios', 'NumeroPlanEstudiosDef', 'NumeroCUsEmitidos', 'NumeroOrdenImpresion', 'CambioNombre', 'Programa', 'CarreraIndependiente', 'ModificaSea'], 'integer'],
            [['NombreCarrera'], 'string', 'max' => 150],
            [['NombreCortoCarrera', 'Fax', 'Email'], 'string', 'max' => 50],
            [['SiglaCarrera'], 'string', 'max' => 3],
            [['Direccion', 'Telefono'], 'string', 'max' => 100],
            [['CodigoFacultad', 'CodigoArea', 'CodigoSede', 'CodigoNivelAcademico'], 'string', 'max' => 2],
            [['CodigoEstadoCarrera', 'TipoGrupo'], 'string', 'max' => 1],
            [['Numero'], 'string', 'max' => 6],
            [['AnnioCreacion'], 'string', 'max' => 4],
            [['Resolucion'], 'string', 'max' => 15],
            [['CodigoCarrera'], 'unique'],
            [['CodigoFacultad'], 'exist', 'skipOnError' => true, 'targetClass' => Facultade::class, 'targetAttribute' => ['CodigoFacultad' => 'CodigoFacultad']],
            [['CodigoSede'], 'exist', 'skipOnError' => true, 'targetClass' => Sede::class, 'targetAttribute' => ['CodigoSede' => 'CodigoSede']],
            [['CodigoNivelAcademico'], 'exist', 'skipOnError' => true, 'targetClass' => NivelesAcademico::class, 'targetAttribute' => ['CodigoNivelAcademico' => 'CodigoNivelAcademico']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'CodigoCarrera' => 'Codigo Carrera',
            'NombreCarrera' => 'Nombre Carrera',
            'NombreCortoCarrera' => 'Nombre Corto Carrera',
            'SiglaCarrera' => 'Sigla Carrera',
            'Direccion' => 'Direccion',
            'Telefono' => 'Telefono',
            'Fax' => 'Fax',
            'Email' => 'Email',
            'NumeroPlanesEstudios' => 'Numero Planes Estudios',
            'NumeroPlanEstudiosDef' => 'Numero Plan Estudios Def',
            'NumeroCUsEmitidos' => 'Numero C Us Emitidos',
            'CodigoFacultad' => 'Codigo Facultad',
            'NumeroOrdenImpresion' => 'Numero Orden Impresion',
            'CodigoEstadoCarrera' => 'Codigo Estado Carrera',
            'Numero' => 'Numero',
            'CodigoArea' => 'Codigo Area',
            'TipoGrupo' => 'Tipo Grupo',
            'AnnioCreacion' => 'Annio Creacion',
            'Resolucion' => 'Resolucion',
            'CambioNombre' => 'Cambio Nombre',
            'Programa' => 'Programa',
            'CarreraIndependiente' => 'Carrera Independiente',
            'ModificaSea' => 'Modifica Sea',
            'CodigoSede' => 'Codigo Sede',
            'CodigoNivelAcademico' => 'Codigo Nivel Academico',
        ];
    }

    /**
     * Gets query for [[CargaHoraria]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCargaHoraria()
    {
        return $this->hasMany(CargaHorarium::class, ['CodigoCarrera' => 'CodigoCarrera']);
    }

    /**
     * Gets query for [[CodigoFacultad]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCodigoFacultad()
    {
        return $this->hasOne(Facultade::class, ['CodigoFacultad' => 'CodigoFacultad']);
    }

    /**
     * Gets query for [[CodigoNivelAcademico]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCodigoNivelAcademico()
    {
        return $this->hasOne(NivelesAcademico::class, ['CodigoNivelAcademico' => 'CodigoNivelAcademico']);
    }

    /**
     * Gets query for [[CodigoSede]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCodigoSede()
    {
        return $this->hasOne(Sede::class, ['CodigoSede' => 'CodigoSede']);
    }
}
