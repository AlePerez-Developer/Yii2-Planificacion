<?php

namespace app\modules\Planificacion\formModels;

use yii\base\Model;

/**
 * @property string $idObjInstitucional
 * @property string IdGestion
 * @property string IdLlavePresupuestaria
 * @property string $codigo
 * @property string $objetivo
 * @property string $producto
 * @property string $formula
 * @property string $descripcion
 */
class ObjetivoEspecificoForm extends Model
{
    public string $idObjInstitucional;
    public string $IdLlavePresupuestaria;
    public string $codigo;
    public string $objetivo;
    public string $producto;
    public string $idGestion;
    public string $formula;
    public string $descripcion;

    public function rules(): array
    {
        return [
            [['idObjInstitucional', 'codigo', 'objetivo', 'producto', 'formula', 'descripcion'], 'required'],
            [['idObjInstitucional'], 'string', 'max' => 36],
            [['codigo'], 'match', 'pattern' => '/^\d{2}$/', 'message' => 'El código debe tener exactamente dos dígitos.'],
            [['objetivo', 'producto', 'formula', 'descripcion'], 'string', 'min' => 2, 'max' => 500],
        ];
    }
}
