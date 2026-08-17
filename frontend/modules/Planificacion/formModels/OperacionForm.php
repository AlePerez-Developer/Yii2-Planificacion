<?php

namespace app\modules\Planificacion\formModels;

use app\modules\Planificacion\models\Operacion;
use yii\base\Model;

class OperacionForm extends Model
{
    public string $codigo;
    public string $idObjEspecifico;
    public string $idIndicador;
    public string $idLlavePresupuestaria;
    public ?string $descripcion = null;
    public string $tipoOperacion;

    public function rules(): array
    {
        return [
            [['codigo', 'idObjEspecifico', 'idIndicador', 'idLlavePresupuestaria', 'tipoOperacion'], 'required'],
            [['idObjEspecifico', 'idIndicador', 'idLlavePresupuestaria'], 'string', 'max' => 36],
            [['codigo'], 'match', 'pattern' => '/^\d{2}$/', 'message' => 'El código debe tener exactamente dos dígitos.'],
            [['descripcion'], 'string', 'max' => 300],
            [['descripcion'], 'default', 'value' => null],
            [['tipoOperacion'], 'in', 'range' => [
                Operacion::TIPO_FUNCIONAMIENTO,
                Operacion::TIPO_INVERSION,
            ]],
        ];
    }
}
