<?php

namespace app\modules\Planificacion\models;

use yii\db\ActiveRecord;

/**
 * Modelo para la tabla "PoliticasEstrategicas".
 *
 * @property int $CodigoPoliticaEstrategica
 * @property int $CodigoAreaEstrategica
 * @property int $Codigo
 * @property string $Descripcion
 *
 * @property AreaEstrategica $area
 */
class PoliticaEstrategica extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'PoliticasEstrategicas';
    }

    public function rules(): array
    {
        return [
            [['CodigoAreaEstrategica', 'Codigo', 'Descripcion'], 'required'],
            [['CodigoPoliticaEstrategica', 'CodigoAreaEstrategica', 'Codigo'], 'integer'],
            [['Descripcion'], 'string', 'max' => 500],
            [['CodigoPoliticaEstrategica'], 'unique'],
            [['CodigoAreaEstrategica'], 'exist', 'skipOnError' => true, 'targetClass' => AreaEstrategica::class, 'targetAttribute' => ['CodigoAreaEstrategica' => 'CodigoAreaEstrategica']],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'CodigoPoliticaEstrategica' => 'Código Política',
            'CodigoAreaEstrategica' => 'Área Estratégica',
            'Codigo' => 'Código',
            'Descripcion' => 'Descripción',
        ];
    }

    public function getArea()
    {
        return $this->hasOne(AreaEstrategica::class, ['CodigoAreaEstrategica' => 'CodigoAreaEstrategica']);
    }

    public function exist(): bool
    {
        return self::find()
            ->where(['Codigo' => $this->Codigo, 'CodigoAreaEstrategica' => $this->CodigoAreaEstrategica])
            ->andWhere(['!=', 'CodigoPoliticaEstrategica', $this->CodigoPoliticaEstrategica])
            ->exists();
    }
}
