<?php

namespace app\modules\Planificacion\formModels;

use yii\base\Model;

/**
 * @property string $idObjEspecifico
 * @property string|null $idIndicadorEstrategico
 * @property string|null $idIndicadorPoa
 * @property string $codigo
 * @property string $descripcion
 * @property int $primerTrimestre
 * @property int $segundoTrimestre
 * @property int $tercerTrimestre
 * @property int $cuartoTrimestre
 */
class OperacionDticForm extends Model
{
    public string $idObjEspecifico;
    public ?string $idIndicadorEstrategico = null;
    public ?string $idIndicadorPoa = null;
    public string $codigo;
    public string $descripcion;
    public int $primerTrimestre = 0;
    public int $segundoTrimestre = 0;
    public int $tercerTrimestre = 0;
    public int $cuartoTrimestre = 0;

    public function rules(): array
    {
        return [
            [['idObjEspecifico', 'codigo', 'descripcion'], 'required'],
            [['idObjEspecifico', 'idIndicadorEstrategico', 'idIndicadorPoa'], 'string', 'max' => 36],
            [['idIndicadorEstrategico', 'idIndicadorPoa'], 'default', 'value' => null],
            [['codigo'], 'match', 'pattern' => '/^\d{2}$/', 'message' => 'El código debe tener exactamente dos dígitos.'],
            [['descripcion'], 'string', 'max' => 500],
            [[
                'primerTrimestre', 'segundoTrimestre', 'tercerTrimestre', 'cuartoTrimestre',
            ], 'integer', 'min' => 0],
        ];
    }
}
