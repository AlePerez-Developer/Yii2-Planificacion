<?php

namespace common\models\seguridad;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "Usuarios".
 *
 * @property string $IdUsuario
 * @property string $IdPersona
 * @property string $CodigoUsuario
 * @property string|null $Nick
 * @property string|null $Llave
 * @property string|null $UltimoAcceso
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 *

 */
class Usuario extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'seguridad.Usuarios';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['IdUsuario'], 'string'],
            [['IdPersona', 'CodigoUsuario', 'CodigoEstado'], 'required'],
            [['UltimoAcceso', 'FechaHoraRegistro'], 'safe'],
            [['IdPersona'], 'string', 'max' => 15],
            [['CodigoUsuario'], 'string', 'max' => 50],
            [['Nick'], 'string', 'max' => 100],
            [['Llave'], 'string', 'max' => 40],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['IdUsuario'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'IdUsuario' => 'Id Usuario',
            'IdPersona' => 'Id Persona',
            'CodigoUsuario' => 'Codigo Usuario',
            'Nick' => 'Nick',
            'Llave' => 'Llave',
            'UltimoAcceso' => 'Ultimo Acceso',
            'CodigoEstado' => 'Codigo Estado',
            'FechaHoraRegistro' => 'Fecha Hora Registro',
        ];
    }
}
