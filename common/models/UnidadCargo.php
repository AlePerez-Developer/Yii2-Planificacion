<?php

namespace common\models;

/**
 * This is the model class for table "unidades".
 *
 * @property string $Unidad
 * @property string $Cargo
 * @property string $CodigoEstado
 * @property string|null $FechaHoraRegistro
 * @property string $CodigoUsuario
 */

class UnidadCargo extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'UnidadesCargos';
    }

    /**
     * @inheritdoc$primaryKey
     */
    public static function primaryKey()
    {
        return ['Unidad','Cargo'];
    }


    public function rules()
    {
        return [
            [['Unidad', 'Cargo', 'CodigoEstado', 'CodigoUsuario',], 'required'],
            [['FechaHoraRegistro'], 'safe'],
            [['Unidad', 'Cargo'], 'string', 'max' => 6],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['Unidad'], 'exist', 'skipOnError' => true, 'targetClass' => Unidad::className(), 'targetAttribute' => ['Unidad' => 'CodigoUnidad']],
            [['Cargo'], 'exist', 'skipOnError' => true, 'targetClass' => Cargo::className(), 'targetAttribute' => ['Cargo' => 'CodigoCargo']],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::className(), 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::className(), 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'Unidad' => 'Unidad',
            'Cargo' => 'Cargo',
            'CodigoEstado' => 'Codigo Estado',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
            'CodigoUsuario' => 'Codigo Usuario',
        ];
    }

    public function isUsed()
    {
        return false;
    }

    public function exist()
    {
        $UnidadCargo = UnidadCargo::find()->where(["Unidad" => $this->Unidad])->andWhere(["Cargo" => $this->Cargo])->all();
        if(!empty($UnidadCargo)){
            return true;
        }else{
            return false;
        }
    }

    public function getUnidad()
    {
        return $this->hasOne(Unidad::className(), ['Unidad' => 'CodigoUnidad']);
    }

    public function getCargo()
    {
        return $this->hasOne(Cargo::className(), ['Cargo' => 'CodigoCargo']);
    }

    public function getEstado()
    {
        return $this->hasOne(Estado::className(), ['CodigoEstado' => 'CodigoEstado']);
    }

    public function getUsuario()
    {
        return $this->hasOne(Usuario::className(), ['CodigoUsuario' => 'CodigoUsuario']);
    }
}