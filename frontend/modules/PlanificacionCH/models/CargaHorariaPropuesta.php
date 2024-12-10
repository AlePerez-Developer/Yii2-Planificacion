<?php

namespace app\modules\PlanificacionCH\models;

use common\models\Estado;
use common\models\Usuario;
use Yii;

/**
 * This is the model class for table "CargaHorariaPropuesta".
 *
 * @property string $GestionAcademica
 * @property int $CodigoCarrera
 * @property int $NumeroPlanEstudios
 * @property string $SiglaMateria
 * @property string $Grupo
 * @property string $TipoGrupo
 * @property string $IdPersona
 * @property string $CodigoSede
 * @property int $HorasSemana
 * @property string|null $Observaciones
 * @property string $CodigoEstado
 * @property string|null $FechaHoraRegistro
 * @property string|null $FechaHoraModificacion
 * @property string $CodigoUsuario
 *
 * @property Estados $codigoEstado
 * @property Sedes $codigoSede
 * @property Usuarios $codigoUsuario
 * @property Personas $idPersona
 */
class CargaHorariaPropuesta extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'CargaHorariaPropuesta';
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
            [['GestionAcademica', 'CodigoCarrera', 'NumeroPlanEstudios', 'SiglaMateria', 'Grupo', 'TipoGrupo', 'IdPersona', 'CodigoSede', 'HorasSemana', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['CodigoCarrera', 'NumeroPlanEstudios', 'HorasSemana'], 'integer'],
            [['FechaHoraRegistro', 'FechaHoraModificacion'], 'safe'],
            [['GestionAcademica', 'SiglaMateria'], 'string', 'max' => 6],
            [['Grupo', 'CodigoSede'], 'string', 'max' => 2],
            [['TipoGrupo', 'CodigoEstado'], 'string', 'max' => 1],
            [['IdPersona'], 'string', 'max' => 15],
            [['Observaciones'], 'string', 'max' => 500],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['CodigoCarrera', 'GestionAcademica', 'Grupo', 'NumeroPlanEstudios', 'SiglaMateria', 'TipoGrupo', 'CodigoEstado'], 'unique', 'targetAttribute' => ['CodigoCarrera', 'GestionAcademica', 'Grupo', 'NumeroPlanEstudios', 'SiglaMateria', 'TipoGrupo', 'CodigoEstado']],
            [['IdPersona'], 'exist', 'skipOnError' => true, 'targetClass' => Persona::class, 'targetAttribute' => ['IdPersona' => 'IdPersona']],
            [['CodigoSede'], 'exist', 'skipOnError' => true, 'targetClass' => Sede::class, 'targetAttribute' => ['CodigoSede' => 'CodigoSede']],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            //[['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'GestionAcademica' => 'Gestion Academica',
            'CodigoCarrera' => 'Codigo Carrera',
            'NumeroPlanEstudios' => 'Numero Plan Estudios',
            'SiglaMateria' => 'Sigla Materia',
            'Grupo' => 'Grupo',
            'TipoGrupo' => 'Tipo Grupo',
            'IdPersona' => 'Id Persona',
            'CodigoSede' => 'Codigo Sede',
            'HorasSemana' => 'Horas Semana',
            'Observaciones' => 'Observaciones',
            'CodigoEstado' => 'Codigo Estado',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
            'FechaHoraModificacion' => 'Fecha Hora Modificacion',
            'CodigoUsuario' => 'Codigo Usuario',
        ];
    }

    /**
     * Gets query for [[CodigoEstado]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCodigoEstado()
    {
        return $this->hasOne(Estados::class, ['CodigoEstado' => 'CodigoEstado']);
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

    public function exist(){
        $row = CargaHorariaPropuesta::find()
            ->where(['GestionAcademica' => $this->GestionAcademica])
            ->andWhere(['CodigoCarrera' => $this->CodigoCarrera])
            ->andWhere(['CodigoSede' => $this->CodigoSede])
            ->andWhere(['NumeroPlanEstudios' => $this->NumeroPlanEstudios])
            ->andWhere(['SiglaMateria' => $this->SiglaMateria])
            ->andWhere(['Grupo' => $this->Grupo])
            ->andWhere(['TipoGrupo' => $this->TipoGrupo])
            ->andWhere(['in','CodigoEstado' ,['V','A']])
            ->all();

        if(!empty($row)){
            return true;
        }else{
            return false;
        }
    }

}
