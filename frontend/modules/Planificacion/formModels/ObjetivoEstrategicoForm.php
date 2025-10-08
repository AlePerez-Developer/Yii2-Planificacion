<?php
namespace app\modules\Planificacion\formModels;

use yii\base\Model;

/**
 * This is the model class for table "PEIs".
 * @property int $areaEstrategica
 * @property int $politicaEstrategica
 * @property string $codigoObjetivo
 * @property string $objetivo
 * @property string $resultado
 * @property string $indicadorDescripcion
 * @property string $indicadorFormula
 * @property int $pei
 */

class ObjetivoEstrategicoForm extends Model
{
    public int $areaEstrategica;
    public int $politicaEstrategica;
    public string $codigoObjetivo;
    public string $objetivo;
    public string $producto;
    public string $indicadorDescripcion;
    public string $indicadorFormula;
    public int $pei;

    public function rules(): array
    {
        return [
            [['areaEstrategica','politicaEstrategica','codigoObjetivo', 'objetivo','producto','indicadorDescripcion','indicadorFormula'], 'required'],
            [['areaEstrategica', 'politicaEstrategica', 'pei'], 'integer'],
            [['codigoObjetivo'], 'string', 'max' => 1],
            [['objetivo','producto','indicadorDescripcion','indicadorFormula'], 'string', 'max' => 450],
        ];
    }
}
