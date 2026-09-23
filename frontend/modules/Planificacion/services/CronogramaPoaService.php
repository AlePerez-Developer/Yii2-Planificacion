<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\dao\CronogramaPoaDao;
use app\modules\Planificacion\formModels\CronogramaPoaForm;
use app\modules\Planificacion\models\CronogramaPoa;
use common\models\Estado;
use Yii;

class CronogramaPoaService
{
    public function listarTodo(): array
    {
        $data = CronogramaPoa::listAll()->asArray()->all();
        return ResponseHelper::success($data, 'Listado de cronogramas obtenido.');
    }

    public function guardar(CronogramaPoaForm $form): array
    {
        [$inicio, $fin] = $this->normalizarFechas($form);
        $this->validarSolape('00000000-0000-0000-0000-000000000000', $inicio, $fin);

        $modelo = new CronogramaPoa([
            'FechaInicio' => $inicio,
            'FechaFin' => $fin,
            'CodigoEstado' => Estado::ESTADO_VIGENTE,
            'CodigoUsuario' => Yii::$app->user->identity->CodigoUsuario,
        ]);

        return $this->procesar($modelo);
    }

    public function actualizar(string $id, CronogramaPoaForm $form): array
    {
        $modelo = $this->obtenerModeloValidado($id);
        [$inicio, $fin] = $this->normalizarFechas($form);
        $this->validarSolape($modelo->IdCronograma, $inicio, $fin);

        $modelo->FechaInicio = $inicio;
        $modelo->FechaFin = $fin;
        $modelo->CodigoUsuario = Yii::$app->user->identity->CodigoUsuario;

        return $this->procesar($modelo);
    }

    public function cambiarEstado(string $id): array
    {
        $modelo = $this->obtenerModeloValidado($id);
        $modelo->cambiarEstado();
        if ($modelo->CodigoEstado === Estado::ESTADO_VIGENTE) {
            $this->validarSolape(
                $modelo->IdCronograma,
                (string)$modelo->FechaInicio,
                (string)$modelo->FechaFin
            );
        }
        $this->guardarModelo($modelo);

        return ResponseHelper::success($modelo->CodigoEstado, 'Estado actualizado.');
    }

    public function eliminar(string $id): array
    {
        $modelo = $this->obtenerModeloValidado($id);
        if (CronogramaPoaDao::enUso($modelo)) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_EN_USO'],
                'El cronograma tiene envíos registrados y no puede ser eliminado.',
                500
            );
        }
        $modelo->eliminar();
        return $this->procesar($modelo);
    }

    public function obtenerModelo(string $id): array
    {
        $modelo = $this->obtenerModeloValidado($id);
        return ResponseHelper::success([
            'IdCronograma' => $modelo->IdCronograma,
            'fechaInicio' => $this->aDatetimeLocal((string)$modelo->FechaInicio),
            'fechaFin' => $this->aDatetimeLocal((string)$modelo->FechaFin),
        ]);
    }

    private function normalizarFechas(CronogramaPoaForm $form): array
    {
        $inicioTs = strtotime(str_replace('T', ' ', $form->fechaInicio));
        $finTs = strtotime(str_replace('T', ' ', $form->fechaFin));
        if ($inicioTs === false || $finTs === false) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'Las fechas del cronograma no son válidas.',
                400
            );
        }
        if ($finTs < $inicioTs) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'La fecha de fin debe ser mayor o igual a la fecha de inicio.',
                400
            );
        }

        return [
            date('Y-m-d H:i:s', $inicioTs),
            date('Y-m-d H:i:s', $finTs),
        ];
    }

    private function aDatetimeLocal(string $fecha): string
    {
        $ts = strtotime($fecha);
        return $ts === false ? '' : date('Y-m-d\TH:i', $ts);
    }

    private function validarSolape(string $id, string $inicio, string $fin): void
    {
        if (CronogramaPoaDao::solapa($id, $inicio, $fin)) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'Ya existe un cronograma vigente que se superpone con las fechas indicadas.',
                400
            );
        }
    }

    private function obtenerModeloValidado(string $id): CronogramaPoa
    {
        $modelo = CronogramaPoa::listOne($id);
        if ($modelo === null) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],
                'No se encontró el cronograma solicitado.',
                404
            );
        }
        return $modelo;
    }

    private function procesar(CronogramaPoa $modelo): array
    {
        $this->guardarModelo($modelo);
        return ResponseHelper::success($modelo, 'Cronograma procesado correctamente.');
    }

    private function guardarModelo(CronogramaPoa $modelo): void
    {
        if (!$modelo->validate()) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                $modelo->getErrors(),
                400
            );
        }
        if (!$modelo->save(false)) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'No se pudo guardar el cronograma.',
                500
            );
        }
    }
}
