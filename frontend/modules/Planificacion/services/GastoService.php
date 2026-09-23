<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\dao\GastoDao;
use app\modules\Planificacion\formModels\GastoForm;
use app\modules\Planificacion\models\Gasto;
use common\models\Estado;
use Yii;

class GastoService
{
    public function listarTodo(): array
    {
        $data = Gasto::listAll()->asArray()->all();
        return ResponseHelper::success($data, 'Listado de gastos obtenido.');
    }

    public function listarS2(): array
    {
        $data = Gasto::find()
            ->select([
                'id' => 'IdGasto',
                'text' => 'Descripcion',
                'codigo' => 'CodigoGasto',
                'entidadTransferencia' => 'EntidadTransferencia',
            ])
            ->where(['CodigoEstado' => Estado::ESTADO_VIGENTE])
            ->orderBy(['CodigoGasto' => SORT_ASC])
            ->asArray()
            ->all();

        return ResponseHelper::success($data);
    }

    public function guardar(GastoForm $form): array
    {
        $modelo = new Gasto([
            'CodigoGasto' => mb_strtoupper(trim($form->codigoGasto), 'UTF-8'),
            'Descripcion' => mb_strtoupper(trim($form->descripcion), 'UTF-8'),
            'EntidadTransferencia' => mb_strtoupper(trim($form->entidadTransferencia), 'UTF-8'),
            'CodigoEstado' => Estado::ESTADO_VIGENTE,
            'CodigoUsuario' => Yii::$app->user->identity->CodigoUsuario,
        ]);

        return $this->procesar($modelo);
    }

    public function actualizar(string $id, GastoForm $form): array
    {
        $modelo = $this->obtenerModeloValidado($id);
        $modelo->CodigoGasto = mb_strtoupper(trim($form->codigoGasto), 'UTF-8');
        $modelo->Descripcion = mb_strtoupper(trim($form->descripcion), 'UTF-8');
        $modelo->EntidadTransferencia = mb_strtoupper(trim($form->entidadTransferencia), 'UTF-8');
        $modelo->CodigoUsuario = Yii::$app->user->identity->CodigoUsuario;

        return $this->procesar($modelo);
    }

    public function cambiarEstado(string $id): array
    {
        $modelo = $this->obtenerModeloValidado($id);
        $modelo->cambiarEstado();
        $this->guardarModelo($modelo);

        return ResponseHelper::success($modelo->CodigoEstado, 'Estado actualizado.');
    }

    public function eliminar(string $id): array
    {
        $modelo = $this->obtenerModeloValidado($id);
        if (GastoDao::enUso($modelo)) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_EN_USO'],
                'El gasto se encuentra en uso y no puede ser eliminado.',
                500
            );
        }
        $modelo->eliminar();
        return $this->procesar($modelo);
    }

    public function obtenerModelo(string $id): array
    {
        $modelo = $this->obtenerModeloValidado($id);
        return ResponseHelper::success($modelo->getAttributes([
            'IdGasto',
            'CodigoGasto',
            'Descripcion',
            'EntidadTransferencia',
        ]));
    }

    public function verificarCodigo(string $id, string $codigo): bool
    {
        return GastoDao::verificarCodigo($id, $codigo);
    }

    private function obtenerModeloValidado(string $id): Gasto
    {
        $modelo = Gasto::listOne($id);
        if ($modelo === null) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],
                'No se encontró el gasto solicitado.',
                404
            );
        }
        return $modelo;
    }

    private function procesar(Gasto $modelo): array
    {
        $this->guardarModelo($modelo);
        return ResponseHelper::success($modelo, 'Gasto procesado correctamente.');
    }

    private function guardarModelo(Gasto $modelo): void
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
    }
}
