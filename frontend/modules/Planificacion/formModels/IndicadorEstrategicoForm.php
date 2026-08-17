<?php

namespace app\modules\Planificacion\formModels;

use yii\base\Model;

/**
  *
 * @property  string $idPei
 * @property string $idObjEstrategico
 * @property int $codigo
 * @property int $meta
 * @property string $descripcion
 * @property int $lineaBase
 * @property string $accionDescripcion
 * @property string $idTipoResultado
 * @property string $idCategoriaIndicador
 * @property string $idUnidadIndicador
 * @property string $idAccionEstrategica
 */

class IndicadorEstrategicoForm extends Model
{
    public string $idPei;
    public string $idObjEstrategico;
    public int $codigo;
    public int $meta;
    public string $descripcion;
    public int $lineaBase;
    public string $idTipoResultado;
    public string $idCategoriaIndicador;
    public string $idUnidadIndicador;
    public string $idAccionEstrategica;


    public function rules(): array
    {
        return [
            [['idPei', 'idObjEstrategico', 'idTipoResultado', 'idCategoriaIndicador', 'idUnidadIndicador', 'idAccionEstrategica'], 'string', 'max' => 36],
            [['idPei', 'idObjEstrategico', 'codigo', 'meta', 'descripcion', 'lineaBase', 'idTipoResultado', 'idCategoriaIndicador', 'idUnidadIndicador', 'idAccionEstrategica'], 'required'],
            [['codigo', 'meta', 'lineaBase'], 'integer'],
            [['descripcion'], 'string', 'max' => 500],
        ];
    }

}