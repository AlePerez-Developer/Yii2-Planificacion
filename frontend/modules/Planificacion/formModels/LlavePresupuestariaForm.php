<?php
namespace app\modules\Planificacion\formModels;

use yii\base\Model;

class LlavePresupuestariaForm extends Model
{
    public string $idUnidadEjecutora;
    public string $idPrograma;
    public string $idProyecto;
    public string $idActividad;
    public string $llave = '';
    public string $fechaInicio;
    public int $esOrganizacional;

    public function rules(): array
    {
        return [
            [['idUnidadEjecutora', 'idPrograma', 'idProyecto', 'idActividad', 'fechaInicio', 'esOrganizacional'], 'required'],
            [['idUnidadEjecutora', 'idPrograma', 'idProyecto', 'idActividad'], 'string', 'max' => 36],
            [['esOrganizacional'], 'integer'],
            [['fechaInicio'], 'safe'],
            [['llave'], 'string', 'max' => 200],
        ];
    }
}
