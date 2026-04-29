<?php

namespace app\modules\Planificacion\formModels;

use yii\base\Model;

/**
 *
 * @property integer $codigo
 * @property string $objetivo
 * @property string $producto
 * @property string $gestion
 * @property string $idObjEstrategico
 *
 */
class ObjetivoInstitucionalForm extends Model
{

    public int $codigo;
    public string $objetivo;
    public string $producto;
    public string $gestion;
    public string $idObjEstrategico;


    public function rules(): array
    {
        return [
            [['codigo', 'objetivo', 'producto', 'idObjEstrategico'], 'required'],
            [['gestion'], 'integer'],
            [['idObjEstrategico'], 'string', 'max' => 36],
            [['objetivo', 'producto' ], 'string', 'max' => 500],
            [['codigo'], 'integer', 'min' => 100, 'max' => 999,]
        ];
    }
}
