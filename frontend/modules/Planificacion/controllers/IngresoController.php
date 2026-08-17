<?php

namespace app\modules\Planificacion\controllers;

use app\controllers\BaseController;
use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\formModels\IngresoForm;
use app\modules\Planificacion\services\IngresoService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class IngresoController extends BaseController
{
    public function __construct(
        $id,
        $module,
        private IngresoService $service,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'listar-todo' => ['POST'],
                    'resumen' => ['POST'],
                    'guardar' => ['POST'],
                    'actualizar' => ['POST'],
                    'buscar' => ['POST'],
                    'cambiar-estado' => ['POST'],
                    'eliminar' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $this->obtenerContexto();
        return $this->render('index');
    }

    public function actionListarTodo(): array
    {
        [$unidad, $gestion, $estado] = $this->obtenerContexto();
        return $this->withTryCatch(
            fn() => $this->service->listarTodo($unidad, $gestion, $estado)
        );
    }

    public function actionResumen(): array
    {
        [$unidad, $gestion, $estado] = $this->obtenerContexto();
        return $this->withTryCatch(
            fn() => $this->service->resumen($unidad, $gestion, $estado)
        );
    }

    public function actionGuardar(): array
    {
        return $this->withTryCatch(function () {
            [$unidad, $gestion, $estado] = $this->obtenerContexto();
            return $this->service->guardar(
                $this->cargarFormulario(),
                $unidad,
                $gestion,
                $estado
            );
        });
    }

    public function actionActualizar(): array
    {
        return $this->withTryCatch(function () {
            [$unidad, $gestion, $estado] = $this->obtenerContexto();
            return $this->service->actualizar(
                $this->obtenerId(),
                $this->cargarFormulario(),
                $unidad,
                $gestion,
                $estado
            );
        });
    }

    public function actionBuscar(): array
    {
        [$unidad, $gestion, $estado] = $this->obtenerContexto();
        return $this->withTryCatch(fn() => $this->service->obtener(
            $this->obtenerId(),
            $unidad,
            $gestion,
            $estado
        ));
    }

    public function actionCambiarEstado(): array
    {
        [$unidad, $gestion, $estado] = $this->obtenerContexto();
        return $this->withTryCatch(fn() => $this->service->cambiarEstado(
            $this->obtenerId(),
            $unidad,
            $gestion,
            $estado
        ));
    }

    public function actionEliminar(): array
    {
        [$unidad, $gestion, $estado] = $this->obtenerContexto();
        return $this->withTryCatch(fn() => $this->service->eliminar(
            $this->obtenerId(),
            $unidad,
            $gestion,
            $estado
        ));
    }

    private function cargarFormulario(): IngresoForm
    {
        $form = new IngresoForm();
        if (!$form->load(Yii::$app->request->post(), '') || !$form->validate()) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                $form->getErrors(),
                400
            );
        }
        return $form;
    }

    private function obtenerId(): string
    {
        $id = (string)Yii::$app->request->post('idIngreso', '');
        if ($id === '') {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'No se recibió el ingreso.',
                400
            );
        }
        return $id;
    }

    private function obtenerContexto(): array
    {
        $contexto = Yii::$app->userContext->contexto();
        $unidad = (string)($contexto?->IdUnidadEjecutora ?? '');
        $gestion = (string)($contexto?->IdGestion ?? '');
        $estado = (string)($contexto?->IdEstadoPoa ?? '');

        if ($unidad === '' || $gestion === '' || $estado === '') {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'Debe seleccionar unidad ejecutora, gestión y estado POA.',
                400
            );
        }
        return [$unidad, $gestion, $estado];
    }
}
