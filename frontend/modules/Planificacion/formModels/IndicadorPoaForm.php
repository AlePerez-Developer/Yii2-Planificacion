<?php

namespace app\modules\Planificacion\formModels;

use yii\base\Model;

/**
 *
 * @property string $idObjEspecifico
 * @property int $codigo
 * @property int $meta
 * @property string $descripcion
 * @property int $lineaBase
  * @property string $idTipoResultado
 * @property string $idCategoriaIndicador
 * @property string $idUnidadIndicador
  */

class IndicadorPoaForm extends Model
{
    public string $idObjEspecifico;
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
            [['idObjEspecifico', 'idTipoResultado', 'idCategoriaIndicador', 'idUnidadIndicador'], 'string', 'max' => 36],
            [['idObjEspecifico', 'codigo', 'meta', 'descripcion', 'lineaBase', 'idTipoResultado', 'idCategoriaIndicador', 'idUnidadIndicador'], 'required'],
            [['codigo', 'meta', 'lineaBase'], 'integer'],
            [['descripcion'], 'string', 'max' => 500],
        ];
    }
}
