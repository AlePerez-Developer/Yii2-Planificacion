<?php

namespace app\modules\Planificacion\formModels;

use yii\base\Model;

/**
 *
 * @property integer $codigo
 * @property string $objetivo
 * @property string $producto
 * @property string $descripcion
 * @property integer $gestion
 * @property string $idObjEstrategico
 *
 */
class ObjetivoInstitucionalForm extends Model
{

    public int $codigo;
    public string $objetivo;
    public string $producto;
    public string $descripcion;
    public string $idObjEstrategico;


    public function rules(): array
    {
        return [
            [['codigo', 'objetivo', 'producto', 'descripcion', 'idObjEstrategico'], 'required'],
            [['idObjEstrategico'], 'string', 'max' => 36],
            [['objetivo', 'producto', 'descripcion', ], 'string', 'max' => 500],
            [['codigo'], 'integer', 'min' => 100, 'max' => 999,]
        ];
    }
}
