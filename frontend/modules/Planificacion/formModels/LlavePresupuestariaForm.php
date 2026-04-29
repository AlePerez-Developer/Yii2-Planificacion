<?php
namespace app\modules\Planificacion\formModels;

use yii\base\Model;

/**
 *
 * @property string $idUnidad
 * @property string $idPrograma
 * @property string $idProyecto
 * @property string $idActividad
 * @property string $descripcion
 * @property float $techoPresupuestario
 * @property string $fechaInicio
 * @property string $fechaFin
 * @property int $esOrganizacional
 *
 */

class LlavePresupuestariaForm extends Model
{
    public string $idUnidad;
    public string $idPrograma;
    public string $idProyecto;
    public string $idActividad;
    public string $descripcion = '';
    public float $techoPresupuestario;
    public string $fechaInicio;
    public string $fechaFin;
    public int $esOrganizacional;

    public function rules(): array
    {
        return [
            [['idUnidad', 'idPrograma', 'idProyecto', 'idActividad', 'descripcion', 'techoPresupuestario', 'fechaInicio'], 'required'],
            [['idUnidad', 'idPrograma', 'idProyecto', 'idActividad'], 'string', 'max' => 36],
            [['techoPresupuestario'], 'number'],
            [['fechaInicio', 'fechaFin'], 'safe'],
            [['descripcion'], 'string', 'max' => 250],
            [['descripcion'], 'trim'],
        ];
    }
}
