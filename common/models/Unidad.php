<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "unidades".
 *
 * @property string $CodigoUnidad
 * @property string $NombreUnidad
 * @property string $NombreCortoUnidad
 * @property string $CodigoTipoUnidad
 * @property string|null $CodigoUnidadPadre
 * @property string $CodigoEstado
 * @property string|null $FechaHoraRegistro
 * @property string $CodigoUsuario
 */
class Unidad extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'unidades';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['CodigoUnidad', 'NombreUnidad', 'NombreCortoUnidad', 'CodigoTipoUnidad', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['FechaHoraRegistro'], 'safe'],
            [['CodigoUnidad', 'CodigoTipoUnidad', 'CodigoUnidadPadre'], 'string', 'max' => 6],
            [['NombreUnidad'], 'string', 'max' => 150],
            [['NombreCortoUnidad'], 'string', 'max' => 100],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['CodigoUnidad'], 'unique'],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::className(), 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::className(), 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
            [['CodigoTipoUnidad'], 'exist', 'skipOnError' => true, 'targetClass' => TipoUnidad::className(), 'targetAttribute' => ['CodigoTipoUnidad' => 'CodigoTipoUnidad']],
            [['CodigoUnidadPadre'], 'exist', 'skipOnError' => true, 'targetClass' => Unidad::className(), 'targetAttribute' => ['CodigoUnidadPadre' => 'CodigoUnidad']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'CodigoUnidad' => 'Codigo Unidad',
            'NombreUnidad' => 'Nombre Unidad',
            'NombreCortoUnidad' => 'Nombre Corto Unidad',
            'CodigoTipoUnidad' => 'Codigo Tipo Unidad',
            'CodigoUnidadPadre' => 'Codigo Unidad Padre',
            'CodigoEstado' => 'Codigo Estado',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
            'CodigoUsuario' => 'Codigo Usuario',
        ];
    }

    public function exist()
    {
        $unidad = Unidad::find()->where(["NombreUnidad" => $this->NombreUnidad])->andWhere(['!=', 'CodigoUnidad', $this->CodigoUnidad])->andWhere(["CodigoEstado"=>"V"])->all();
        if(!empty($unidad)){
            return true;
        }else{
            return false;
        }
    }

    public function enUso()
    {
        $unidad = Unidad::find()->where(['CodigoUnidadPadre' => $this->CodigoUnidad])->all();
        if( !empty($unidad) || ($this->CodigoUnidadPadre == null) ){
            return true;
        }else{
            return false;
        }
    }

    public function getTipoUnidad()
    {
        return $this->hasOne(TipoUnidad::className(), ['CodigoTipoUnidad' => 'CodigoTipoUnidad']);
    }

    public function getUnidadPadre()
    {
        $value = $this->hasOne(Unidad::className(), ['CodigoUnidad' => 'CodigoUnidadPadre']);
        if ($value)
            return   $value;
        else
            return $this;
    }
}
