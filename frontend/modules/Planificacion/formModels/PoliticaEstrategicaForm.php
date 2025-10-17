<?php
namespace app\modules\Planificacion\formModels;

use yii\base\Model;

class PoliticaEstrategicaForm extends Model
{
    public string $idAreaEstrategica;
    public int $codigo;
    public string $descripcion;

    public function rules(): array
    {
        return [
            [['idAreaEstrategica', 'codigo', 'descripcion'], 'required'],
            [['codigo'], 'integer'],
            [['idAreaEstrategica'], 'string', 'max' => 36],
            [['descripcion'], 'string', 'max' => 500],
            [['codigo', 'descripcion'], 'trim'],
        ];
    }
}
