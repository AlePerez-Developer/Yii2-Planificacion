<?php
namespace app\modules\Planificacion\formModels;

use yii\base\Model;

class LlavePresupuestariaForm extends Model
{
    public ?int $codigoUnidad = null;
    public ?int $codigoPrograma = null;
    public ?int $codigoProyecto = null;
    public ?int $codigoActividad = null;
    public string $descripcion = '';
    public ?float $techoPresupuestario = null;
    public ?string $fechaInicio = null;
    public ?string $fechaFin = null;

    public function rules(): array
    {
        return [
            [['codigoUnidad', 'codigoPrograma', 'codigoProyecto', 'codigoActividad', 'descripcion', 'techoPresupuestario', 'fechaInicio'], 'required'],
            [['codigoUnidad', 'codigoPrograma', 'codigoProyecto', 'codigoActividad'], 'integer'],
            [['techoPresupuestario'], 'number'],
            [['fechaInicio', 'fechaFin'], 'safe'],
            [['descripcion'], 'string', 'max' => 250],
            [['descripcion'], 'trim'],
        ];
    }
}
