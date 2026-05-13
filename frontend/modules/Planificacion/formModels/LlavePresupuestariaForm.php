<?php
namespace app\modules\Planificacion\formModels;

use yii\base\Model;

/**
 *
 * @property string $idDa
 * @property string $idUe
 * @property string $idProyecto
 * @property string $idActividad
 * @property string $llave
 * @property string $descripcion
 * @property int $esOrganizacional
 * @property string $fechaInicio
 * @property string $fechaFin
 *
 */

class LlavePresupuestariaForm extends Model
{
    public string $idDa;
    public string $idUe;
    public string $idProyecto;
    public string $idActividad;
    public string $llave;
    public string $descripcion = '';
    public string $fechaInicio;
    public int $esOrganizacional;

    public function rules(): array
    {
        return [
            [['idDa', 'idUe', 'idProyecto', 'idActividad', 'descripcion', 'fechaInicio', 'esOrganizacional'], 'required'],
            [['idDa', 'idUe', 'idProyecto', 'idActividad'], 'string', 'max' => 36],
            [['esOrganizacional'], 'integer'],
            [['fechaInicio', 'fechaFin'], 'safe'],
            [['descripcion'], 'string', 'max' => 500],
            [['llave'], 'string', 'max' => 200],
            [['descripcion'], 'trim'],
        ];
    }
}
