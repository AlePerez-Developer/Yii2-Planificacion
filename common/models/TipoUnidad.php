<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "TiposUnidades".
 *
 * @property string $CodigoTipoUnidad
 * @property string $NombreTipoUnidad
 * @property int $Organigrama
 * @property string $CodigoEstado
 * @property string|null $FechaHoraRegistro
 * @property string $CodigoUsuario
 *
 * @property Estados $codigoEstado
 * @property Usuarios $codigoUsuario
 * @property Unidades[] $unidades-soa
 */
class TipoUnidad extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'TiposUnidades';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['CodigoTipoUnidad', 'NombreTipoUnidad', 'Organigrama', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['Organigrama'], 'integer'],
            [['FechaHoraRegistro'], 'safe'],
            [['CodigoTipoUnidad'], 'string', 'max' => 6],
            [['NombreTipoUnidad'], 'string', 'max' => 100],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['NombreTipoUnidad'], 'unique'],
            [['CodigoTipoUnidad'], 'unique'],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estados::className(), 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuarios::className(), 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'CodigoTipoUnidad' => 'Codigo Tipo UnidadSoa',
            'NombreTipoUnidad' => 'Nombre Tipo UnidadSoa',
            'Organigrama' => 'Organigrama',
            'CodigoEstado' => 'Codigo Estado',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
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
        return $this->hasOne(Estados::className(), ['CodigoEstado' => 'CodigoEstado']);
    }

    /**
     * Gets query for [[CodigoUsuario]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCodigoUsuario()
    {
        return $this->hasOne(Usuarios::className(), ['CodigoUsuario' => 'CodigoUsuario']);
    }

    /**
     * Gets query for [[Unidades]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUnidades()
    {
        return $this->hasMany(Unidades::className(), ['CodigoTipoUnidad' => 'CodigoTipoUnidad']);
    }
}
