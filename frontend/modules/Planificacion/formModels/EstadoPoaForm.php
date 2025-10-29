<?php

namespace app\modules\Planificacion\formModels;

use yii\base\Model;

class EstadoPoaForm extends Model
{
    public ?int $codigoEstadoPoa = null;
    public string $descripcion = '';
    public string $abreviacion = '';
    public ?int $etapaActual = null;
    public ?int $etapaPredeterminada = null;
    public ?int $orden = null;

    public function rules(): array
    {
        return [
            [['descripcion', 'abreviacion', 'etapaActual', 'etapaPredeterminada', 'orden'], 'required'],
            [['descripcion'], 'string', 'max' => 200],
            [['abreviacion'], 'string', 'max' => 3],
            [['descripcion', 'abreviacion'], 'trim'],
            [['etapaActual', 'etapaPredeterminada', 'orden', 'codigoEstadoPoa'], 'integer'],
        ];
    }
}
