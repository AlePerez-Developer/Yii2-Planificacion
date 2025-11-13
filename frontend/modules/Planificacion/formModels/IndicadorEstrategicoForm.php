<?php

namespace app\modules\Planificacion\formModels;

use yii\base\Model;

/**
  *
 * @property string $IdObjEstrategico
 * @property int $Codigo
 * @property int $Meta
 * @property string $Descripcion
 * @property int $LineaBase
 * @property string $IdTipoResultado
 * @property string $IdCategoriaIndicador
 * @property string $IdUnidadIndicador
 */

class IndicadorEstrategicoForm extends Model
{
    public string $idObjEstrategico;
    public int $codigo;
    public int $meta;
    public string $descripcion;
    public int $lineaBase;
    public string $idTipoResultado;
    public string $idCategoriaIndicador;
    public string $idUnidadIndicador;


    public function rules(): array
    {
        return [
            [['IdObjEstrategico', 'IdTipoResultado', 'IdCategoriaIndicador', 'IdUnidadIndicador'], 'string', 'max' => 36],
            [['IdObjEstrategico', 'Codigo', 'Meta', 'Descripcion', 'LineaBase', 'IdTipoResultado', 'IdCategoriaIndicador', 'IdUnidadIndicador'], 'required'],
            [['Codigo', 'Meta', 'LineaBase'], 'integer'],
            [['Descripcion'], 'string', 'max' => 500],
        ];
    }

}