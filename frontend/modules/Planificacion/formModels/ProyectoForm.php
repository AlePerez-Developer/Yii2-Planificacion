<?php

namespace app\modules\Planificacion\formModels;

use yii\base\Model;

/**
 *
 * @property string $idPrograma
 * @property string $codigo
 * @property string $descripcion
 */



class ProyectoForm extends Model
{
    public string $idPrograma;
    public string $codigo;
    public string $descripcion;

    public function rules(): array
    {
        return [
            [['idPrograma', 'codigo', 'descripcion'], 'required'],
            [['codigo'], 'string', 'max' => 20],
            [['descripcion'], 'string', 'max' => 500],
            [['idPrograma'], 'string', 'max' => 36],
            [['codigo', 'descripcion'], 'trim'],
        ];
    }
}