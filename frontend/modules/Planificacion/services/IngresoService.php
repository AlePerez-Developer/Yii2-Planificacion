<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\dao\IngresoDao;
use app\modules\Planificacion\dao\TechoUnidadDao;
use app\modules\Planificacion\formModels\IngresoForm;
use app\modules\Planificacion\models\Ingreso;
use common\models\Estado;
use Yii;

class IngresoService
{
    public function listarTodo(
        string $idUnidadEjecutora,
        string $idGestion,
        string $idEstadoPoa
    ): array {
        $data = Ingreso::listAll($idUnidadEjecutora, $idGestion, $idEstadoPoa)
            ->orderBy(['I.FechaHoraRegistro' => SORT_DESC])
            ->asArray()
            ->all();

        return ResponseHelper::success($data, 'Ingresos obtenidos.');
    }

    public function resumen(
        string $idUnidadEjecutora,
        string $idGestion,
        string $idEstadoPoa
    ): array {
        return ResponseHelper::success([
            'TotalIngresos' => IngresoDao::total(
                $idUnidadEjecutora,
                $idGestion,
                $idEstadoPoa
            ),
            'TotalTechos' => TechoUnidadDao::total(
                $idUnidadEjecutora,
                $idGestion
            ),
        ]);
    }

    public function guardar(
        IngresoForm $form,
        string $idUnidadEjecutora,
        string $idGestion,
        string $idEstadoPoa
    ): array {
        $modelo = new Ingreso([
            'IdUnidadEjecutora' => $idUnidadEjecutora,
            'IdGestion' => $idGestion,
            'IdEstadoPoa' => $idEstadoPoa,
            'Cantidad' => $form->cantidad,
            'Precio' => $form->precio,
            'Descripcion' => $this->normalizarDescripcion($form->descripcion),
            'CodigoEstado' => Estado::ESTADO_VIGENTE,
            'CodigoUsuario' => Yii::$app->user->identity->CodigoUsuario,
        ]);

        return $this->procesar($modelo);
    }

    public function actualizar(
        string $id,
        IngresoForm $form,
        string $idUnidadEjecutora,
        string $idGestion,
        string $idEstadoPoa
    ): array {
        $modelo = $this->obtenerModelo(
            $id,
            $idUnidadEjecutora,
            $idGestion,
            $idEstadoPoa
        );

        if ($modelo->CodigoEstado === Estado::ESTADO_VIGENTE) {
            $totalSinActual = IngresoDao::total(
                $idUnidadEjecutora,
                $idGestion,
                $idEstadoPoa,
                $id
            );
            $this->validarTechos(
                $totalSinActual + ($form->cantidad * $form->precio),
                true,
                $idUnidadEjecutora,
                $idGestion
            );
        }

        $modelo->Cantidad = $form->cantidad;
        $modelo->Precio = $form->precio;
        $modelo->Descripcion = $this->normalizarDescripcion($form->descripcion);
        $modelo->CodigoUsuario = Yii::$app->user->identity->CodigoUsuario;

        return $this->procesar($modelo);
    }

    public function obtener(
        string $id,
        string $idUnidadEjecutora,
        string $idGestion,
        string $idEstadoPoa
    ): array {
        $modelo = $this->obtenerModelo(
            $id,
            $idUnidadEjecutora,
            $idGestion,
            $idEstadoPoa
        );

        return ResponseHelper::success([
            'IdIngreso' => $modelo->IdIngreso,
            'Cantidad' => $modelo->Cantidad,
            'Precio' => $modelo->Precio,
            'Descripcion' => $modelo->Descripcion,
        ]);
    }

    public function cambiarEstado(
        string $id,
        string $idUnidadEjecutora,
        string $idGestion,
        string $idEstadoPoa
    ): array {
        $modelo = $this->obtenerModelo(
            $id,
            $idUnidadEjecutora,
            $idGestion,
            $idEstadoPoa
        );

        if ($modelo->CodigoEstado === Estado::ESTADO_VIGENTE) {
            $totalRestante = IngresoDao::total(
                $idUnidadEjecutora,
                $idGestion,
                $idEstadoPoa,
                $id
            );
            $existenRestantes = IngresoDao::existen(
                $idUnidadEjecutora,
                $idGestion,
                $idEstadoPoa,
                $id
            );
            $this->validarTechos(
                $totalRestante,
                $existenRestantes,
                $idUnidadEjecutora,
                $idGestion
            );
        } else {
            $totalNuevo = IngresoDao::total(
                $idUnidadEjecutora,
                $idGestion,
                $idEstadoPoa
            ) + ((int)$modelo->Cantidad * (float)$modelo->Precio);
            $this->validarTechos(
                $totalNuevo,
                true,
                $idUnidadEjecutora,
                $idGestion
            );
        }

        $modelo->cambiarEstado();
        $modelo->CodigoUsuario = Yii::$app->user->identity->CodigoUsuario;
        return $this->procesar($modelo);
    }

    public function eliminar(
        string $id,
        string $idUnidadEjecutora,
        string $idGestion,
        string $idEstadoPoa
    ): array {
        $modelo = $this->obtenerModelo(
            $id,
            $idUnidadEjecutora,
            $idGestion,
            $idEstadoPoa
        );

        if ($modelo->CodigoEstado === Estado::ESTADO_VIGENTE) {
            $totalRestante = IngresoDao::total(
                $idUnidadEjecutora,
                $idGestion,
                $idEstadoPoa,
                $id
            );
            $existenRestantes = IngresoDao::existen(
                $idUnidadEjecutora,
                $idGestion,
                $idEstadoPoa,
                $id
            );
            $this->validarTechos(
                $totalRestante,
                $existenRestantes,
                $idUnidadEjecutora,
                $idGestion
            );
        }

        $modelo->eliminar();
        $modelo->CodigoUsuario = Yii::$app->user->identity->CodigoUsuario;
        return $this->procesar($modelo);
    }

    private function validarTechos(
        float $totalIngresos,
        bool $existenIngresos,
        string $idUnidadEjecutora,
        string $idGestion
    ): void {
        if (!$existenIngresos) {
            return;
        }

        $totalTechos = TechoUnidadDao::total($idUnidadEjecutora, $idGestion);
        if ($totalTechos > $totalIngresos) {
            throw new ValidationException(
                Yii::$app->params['ERROR_VALIDACION_MODELO'],
                "Los techos asignados ({$totalTechos}) superan el nuevo total de ingresos ({$totalIngresos}).",
                422
            );
        }
    }

    private function obtenerModelo(
        string $id,
        string $idUnidadEjecutora,
        string $idGestion,
        string $idEstadoPoa
    ): Ingreso {
        $modelo = Ingreso::listAll($idUnidadEjecutora, $idGestion, $idEstadoPoa)
            ->andWhere(['I.IdIngreso' => $id])
            ->one();

        if ($modelo === null) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],
                'No se encontró el ingreso solicitado.',
                404
            );
        }

        return $modelo;
    }

    private function procesar(Ingreso $modelo): array
    {
        if (!$modelo->validate()) {
            throw new ValidationException(
                Yii::$app->params['ERROR_VALIDACION_MODELO'],
                $modelo->getErrors(),
                422
            );
        }
        if (!$modelo->save(false)) {
            throw new ValidationException(
                Yii::$app->params['ERROR_EJECUCION_SQL'],
                $modelo->getErrors(),
                500
            );
        }

        return ResponseHelper::success($modelo, 'Ingreso procesado correctamente.');
    }

    private function normalizarDescripcion(?string $descripcion): ?string
    {
        $descripcion = trim((string)$descripcion);
        return $descripcion === '' ? null : mb_strtoupper($descripcion, 'UTF-8');
    }
}
