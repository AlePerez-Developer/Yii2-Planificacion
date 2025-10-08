<?php

namespace app\modules\Planificacion\formModels;

use yii\base\Model;
use app\modules\Planificacion\models\Programa;

class ActividadForm extends Model
{
    public $codigo = '';
    public $descripcion = '';
    public $programa_id = null;
    public $codigoActividad = null;

    public function rules(): array
    {
        return [
            [['programa_id', 'codigo', 'descripcion'], 'required'],
            [['codigoActividad', 'programa_id'], 'integer'],
            [['codigo'], 'string', 'max' => 20],
            [['descripcion'], 'string', 'max' => 250],
            [['codigo', 'descripcion'], 'trim'],
            ['programa_id', 'exist', 'skipOnError' => true, 'targetClass' => Programa::class, 'targetAttribute' => ['programa_id' => 'CodigoPrograma']],
        ];
    }
}
