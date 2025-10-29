<?php
namespace app\modules\Planificacion\formModels;

use yii\base\Model;

/**
 * @property string $idAreaEstrategica
 * @property string $idPoliticaEstrategica
 * @property integer $codigo
 * @property string $objetivo
 * @property string $producto
 * @property string $descripcion
 * @property string $formula
 * @property string $idPei
 */

class ObjetivoEstrategicoForm extends Model
{
    public string $idPei;
    public string $idAreaEstrategica;
    public string $idPoliticaEstrategica;
    public int $codigo;
    public string $objetivo;
    public string $producto;
    public string $descripcion;
    public string $formula;

    public function rules(): array
    {
        return [
            [['idAreaEstrategica', 'idPoliticaEstrategica', 'codigo', 'objetivo', 'producto', 'descripcion', 'formula', 'idPei'], 'required'],
            [['idAreaEstrategica', 'idPoliticaEstrategica', 'idPei'], 'string', 'max' => 36],
            [['objetivo', 'producto', 'descripcion', 'formula'], 'string', 'max' => 500],
            [['codigo'], 'integer','min' => 1, 'max' => 9,]
        ];
    }
}
