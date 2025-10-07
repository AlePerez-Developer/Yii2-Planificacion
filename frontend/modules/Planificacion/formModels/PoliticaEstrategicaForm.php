<?php
namespace app\modules\Planificacion\formModels;

use yii\base\Model;

class PoliticaEstrategicaForm extends Model
{
    public int $codigoAreaEstrategica;
    public int $codigo;
    public string $descripcion;

    public function rules(): array
    {
        return [
            [['codigoAreaEstrategica', 'codigo', 'descripcion'], 'required'],
            [['codigoAreaEstrategica', 'codigo'], 'integer'],
            [['descripcion'], 'string', 'max' => 500],
            [['codigo', 'descripcion'], 'trim'],
        ];
    }
}
