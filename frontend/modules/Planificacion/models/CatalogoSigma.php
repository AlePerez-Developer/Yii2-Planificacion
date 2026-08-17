<?php

namespace app\modules\Planificacion\models;

use common\models\Estado;
use common\models\Usuario;
use yii\db\ActiveRecord;

class CatalogoSigma extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'CatalogoSigma';
    }

    public function rules(): array
    {
        return [
            [['IdSigma', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['IdSigma'], 'string', 'max' => 36],
            [['Clase', 'RamaComercial', 'Especificacion'], 'string', 'max' => 100],
            [['Descripcion'], 'string', 'max' => 300],
            [['IdGasto'], 'string', 'max' => 20],
            [['PrecioReferencial'], 'number', 'min' => 0],
            [['FechaHoraRegistro'], 'safe'],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['IdSigma'], 'unique'],
            [['CodigoEstado'], 'exist', 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
        ];
    }
}
