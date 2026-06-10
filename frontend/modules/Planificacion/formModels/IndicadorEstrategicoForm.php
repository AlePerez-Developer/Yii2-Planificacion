<?php

namespace app\modules\Planificacion\formModels;

use yii\base\Model;

/**
  *
 * @property string $idObjEstrategico
 * @property int $codigo
 * @property int $meta
 * @property string $descripcion
 * @property int $lineaBase
 * @property string $idTipoResultado
 * @property string $idCategoriaIndicador
 * @property string $idUnidadIndicador
 */

class IndicadorEstrategicoForm extends Model
{
    public string $idObjEstrategico;
    public int $codigo;
    public int $meta;
    public string $descripcion;
    public int $lineaBase;
    public string $accionDescripcion;
    public string $idTipoResultado;
    public string $idCategoriaIndicador;
    public string $idUnidadIndicador;
    public string $idAccionEstrategica;


    public function rules(): array
    {
        return [
            [['idObjEstrategico', 'idTipoResultado', 'idCategoriaIndicador', 'idUnidadIndicador', 'idAccionEstrategica'], 'string', 'max' => 36],
            [['idObjEstrategico', 'codigo', 'meta', 'descripcion', 'lineaBase', 'accionDescripcion', 'idTipoResultado', 'idCategoriaIndicador', 'idUnidadIndicador', 'idAccionEstrategica'], 'required'],
            [['codigo', 'meta', 'lineaBase'], 'integer'],
            [['descripcion', 'accionDescripcion'], 'string', 'max' => 500],
        ];
    }

}