<?php

namespace app\modules\PlanificacionCH\models;

use Yii;

/**
 * This is the model class for table "Facultades".
 *
 * @property string $CodigoFacultad
 * @property string $NombreFacultad
 * @property string $NombreCortoFacultad
 *
 * @property Carrera[] $carreras
 * @property Edificio[] $codigoEdificios
 * @property FacultadesEdificio[] $facultadesEdificios
 */
class Facultad extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Facultades';
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
            [['CodigoFacultad', 'NombreFacultad', 'NombreCortoFacultad'], 'required'],
            [['CodigoFacultad'], 'string', 'max' => 2],
            [['NombreFacultad'], 'string', 'max' => 100],
            [['NombreCortoFacultad'], 'string', 'max' => 50],
            [['CodigoFacultad'], 'unique'],
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
            'NombreCortoFacultad' => 'Nombre Corto Facultad',
        ];
    }

    /**
     * Gets query for [[Carreras]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCarreras()
    {
        return $this->hasMany(Carrera::class, ['CodigoFacultad' => 'CodigoFacultad']);
    }

    /**
     * Gets query for [[CodigoEdificios]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCodigoEdificios()
    {
        return $this->hasMany(Edificio::class, ['CodigoEdificio' => 'CodigoEdificio'])->viaTable('FacultadesEdificios', ['CodigoFacultad' => 'CodigoFacultad']);
    }

    /**
     * Gets query for [[FacultadesEdificios]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFacultadesEdificios()
    {
        return $this->hasMany(FacultadesEdificio::class, ['CodigoFacultad' => 'CodigoFacultad']);
    }
}
