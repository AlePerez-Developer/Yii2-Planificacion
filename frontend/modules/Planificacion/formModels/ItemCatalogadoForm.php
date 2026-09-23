<?php

namespace app\modules\Planificacion\formModels;

use yii\base\Model;

class ItemCatalogadoForm extends Model
{
    public string $idItem = '';
    public string $idSigma = '';
    public string $idFuente = '';
    public string $idOrganismo = '';
    public string $idFuenteUniversitaria = '';
    public string $idUnidadMedida = '';
    public string $descripcion = '';
    public float|string $precio = 0;
    public int $formulario = 0;
    public array $asignaciones = [];

    public function rules(): array
    {
        return [
            [[
                'idSigma',
                'idFuente',
                'idOrganismo',
                'idFuenteUniversitaria',
                'idUnidadMedida',
                'descripcion',
                'precio',
                'formulario',
            ], 'required'],
            [['idItem', 'idSigma', 'idFuente', 'idOrganismo', 'idFuenteUniversitaria', 'idUnidadMedida'], 'string', 'max' => 36],
            [['descripcion'], 'string', 'max' => 500],
            [['precio'], 'number', 'min' => 0.01],
            [['precio'], 'match', 'pattern' => '/^\d+(?:\.\d{1,2})?$/', 'message' => 'El precio debe tener máximo dos decimales.'],
            [['formulario'], 'integer', 'min' => 7, 'max' => 9],
            [['asignaciones'], 'validateAsignaciones'],
        ];
    }

    public function validateAsignaciones(string $attribute): void
    {
        if (!is_array($this->asignaciones) || $this->asignaciones === []) {
            $this->addError($attribute, 'Debe asignar el ítem al menos a una operación.');
            return;
        }

        $hayCantidad = false;
        foreach ($this->asignaciones as $index => $asignacion) {
            $idOperacion = trim((string)($asignacion['idOperacion'] ?? ''));
            $cantidad = $asignacion['cantidad'] ?? 0;
            if ($idOperacion === '') {
                $this->addError($attribute, "La asignación #{$index} no tiene operación.");
                continue;
            }
            if (!is_numeric($cantidad) || (float)$cantidad < 0) {
                $this->addError($attribute, 'La cantidad debe ser un número mayor o igual a cero.');
                continue;
            }
            if ((float)$cantidad > 0) {
                $hayCantidad = true;
            }
        }

        if (!$hayCantidad) {
            $this->addError($attribute, 'Debe ingresar cantidad en al menos una operación.');
        }
    }
}
