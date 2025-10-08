<?php

namespace app\modules\Planificacion\formModels;

use yii\base\Model;
use app\modules\Planificacion\models\Programa;

class ProyectoForm extends Model
{
    // No usar tipado estricto para evitar TypeError al cargar POST
    public $codigo = '';
    public $descripcion = '';
    public $programa_id = null; // FK Programa (int)
    public $codigoProyecto = null; // PK opcional al actualizar

    public function rules(): array
    {
        return [
            [['programa_id', 'codigo', 'descripcion'], 'required'],
            [['codigoProyecto', 'programa_id'], 'integer'],
            [['codigo'], 'string', 'max' => 20],
            [['descripcion'], 'string', 'max' => 250],
            [['codigo', 'descripcion'], 'trim'],
            ['programa_id', 'exist', 'skipOnError' => true, 'targetClass' => Programa::class, 'targetAttribute' => ['programa_id' => 'CodigoPrograma']],
        ];
    }
}