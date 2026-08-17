<?php

namespace app\modules\Planificacion\models;

use common\models\Estado;
use common\models\Usuario;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property string $IdItem
 * @property string $TipoItem
 * @property string $Descripcion
 * @property string $CodigoEstado
 * @property string $FechaHoraRegistro
 * @property string $CodigoUsuario
 */
class Item extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'Items';
    }

    public function rules(): array
    {
        return [
            [['IdItem'], 'string', 'max' => 36],
            [['TipoItem', 'Descripcion', 'CodigoEstado', 'CodigoUsuario'], 'required'],
            [['TipoItem'], 'string', 'max' => 50],
            [['Descripcion'], 'string', 'max' => 500],
            [['CodigoEstado'], 'string', 'max' => 1],
            [['CodigoUsuario'], 'string', 'max' => 3],
            [['FechaHoraRegistro'], 'safe'],
            [['IdItem'], 'unique'],
            [['CodigoEstado'], 'exist', 'targetClass' => Estado::class, 'targetAttribute' => ['CodigoEstado' => 'CodigoEstado']],
            [['CodigoUsuario'], 'exist', 'targetClass' => Usuario::class, 'targetAttribute' => ['CodigoUsuario' => 'CodigoUsuario']],
        ];
    }

    public function getProgramaciones(): ActiveQuery
    {
        return $this->hasMany(ProgramacionItem::class, ['IdItem' => 'IdItem']);
    }
}
