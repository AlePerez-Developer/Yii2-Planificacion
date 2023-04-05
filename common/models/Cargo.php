<?php

namespace common\models;

/**
 * This is the model class for table "unidades".
 *
 * @property string $CodigoCargo
 * @property string $NombreCargo
 * @property string $DescripcionCargo
 * @property string $RequisitosPrincipales
 * @property string $RequisitosOpcionales
 * @property string $ArchivoManualFunciones
 * @property string $CodigoSectorTrabajo
 * @property string $CodigoEstado
 * @property string|null $FechaHoraRegistro
 * @property string $CodigoUsuario
 */

class Cargo extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'Cargos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['CodigoCargo', 'NombreCargo', 'CodigoEstado', 'CodigoUsuario', 'CodigoSectorTrabajo'], 'required'],
            [['FechaHoraRegistro'], 'safe'],
            [['CodigoCargo'], 'string', 'max' => 6],
            [['CodigoSectorTrabajo'], 'string', 'max' => 3],
            [['NombreCargo'], 'string', 'max' => 150],
            [['DescripcionCargo','RequisitosPrincipales','RequisitosOpcionales'], 'string', 'max' => 1000],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['CodigoCargo'], 'unique'],
            [['CodigoEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estado::className(), 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::className(), 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
            [['CodigoSectorTrabajo'], 'exist', 'skipOnError' => true, 'targetClass' => SectorTrabajo::className(), 'targetAttribute' => ['CodigoSectorTrabajo' => 'CodigoSectorTrabajo']]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'CodigoCargo' => 'Codigo Cargo',
            'NombreCargo' => 'Nombre Cargo',
            'DescripcionCargo' => 'Descripcion Cargo',
            'RequisitosPrincipales' => 'Requisitos Principales',
            'RequisitosOpcionales' => 'Nombre Cargo',
            'ArchivoManualFunciones' => 'Manual de funciones',
            'CodigoSectorTrabajo' => 'Sector Trabajo',
            'CodigoEstado' => 'Codigo Estado',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
            'CodigoUsuario' => 'Codigo Usuario',
        ];
    }

    public function isUsed()
    {
        /*$items = Item::find()->where(["CodigoCargo" =>$this->CodigoCargo])->all();
        if(!empty($items)){
            return true;
        }else{
            return false;
        }*/
        return false;
    }

    public function exist()
    {
        $cargos = Cargo::find()->where(["NombreCargo" => $this->NombreCargo])->andWhere(["<>", "CodigoCargo", $this->CodigoCargo])->all();
        if(!empty($cargos)){
            return true;
        }else{
            return false;
        }
    }

    public function getSectorTrabajo()
    {
        return $this->hasOne(SectorTrabajo::className(), ['CodigoSectorTrabajo' => 'CodigoSectorTrabajo']);
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