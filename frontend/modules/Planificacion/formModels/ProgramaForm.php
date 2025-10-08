<?php
namespace app\modules\Planificacion\formModels;

use yii\base\Model;

class ProgramaForm extends Model
{
    public string $codigo = ''; // Inicializada
    public string $descripcion = ''; // Inicializada
    public ?int $codigoPrograma = null; // Inicializada como nullable

    public function rules(): array
    {
        return [
            [['codigo', 'descripcion'], 'required'],
            [['codigo'], 'string', 'max' => 20],
            [['descripcion'], 'string', 'max' => 250],
            [['codigo'], 'trim'],
            ['codigoPrograma', 'integer'],
        ];
    }
}