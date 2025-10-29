<?php

namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\formModels\GastoForm;
use app\modules\Planificacion\services\GastoService;
use yii\web\BadRequestHttpException;
use app\controllers\BaseController;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use Yii;

class GastoController extends BaseController
{
    private GastoService $gastoService;

    public function __construct($id, $module, GastoService $gastoService, $config = [])
    {
        $this->gastoService = $gastoService;
        parent::__construct($id, $module, $config);
    }

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => [],
                'rules' => [
                    [
                        'actions' => [],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => [],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'listar-todo' => ['get', 'post'],
                    'guardar' => ['post'],
                    'actualizar' => ['post'],
                    'cambiar-estado' => ['post'],
                    'eliminar' => ['post'],
                    'buscar' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @throws BadRequestHttpException
     */
    public function beforeAction($action): bool
    {
        if ($action->id == "listar-todo")
            $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    /**
     * accion index.
     *
     * @return string
     */
    public function actionIndex(): string
    {
        return $this->render('gasto');
    }

    /**
     * accion para listar todos los registros del modelo.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     */
    public function actionListarTodo(): array
    {
        return $this->withTryCatch(fn() => $this->gastoService->listarGastos());
    }

    /**
     * accion para agregar un nuevo registro.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     */
    public function actionGuardar(): array
    {
        return $this->withTryCatch(function() {
            $request = Yii::$app->request;

            $form = new GastoForm();

            if (!$form->load($request->post(), '') || !$form->validate()) {
                throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], $form->getErrors(), 400);
            }

            return $this->gastoService->guardarGasto($form);
        });
    }

    /**
     * accion para actualizar los valores de un registro existente.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     */
    public function actionActualizar(): array
    {
        return $this->withTryCatch(function() {
            $request = Yii::$app->request;

            $codigoGasto = $this->obtenerCodigo();
            $form = new GastoForm();

            if (!$form->load($request->post(), '') || !$form->validate()) {
                throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], $form->getErrors(), 400);
            }

            return $this->gastoService->actualizarGasto($codigoGasto, $form);
        });
    }

    /**
     * accion para alternar el estado de un registro V/C.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     */
    public function actionCambiarEstado(): array
    {
        return $this->withTryCatch(function() {
            $codigoGasto = $this->obtenerCodigo();
            return $this->gastoService->cambiarEstado($codigoGasto);
        });
    }

    /**
     * accion para soft delete de un registro
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     */
    public function actionEliminar(): array
    {
        return $this->withTryCatch(function() {
            $codigoGasto = $this->obtenerCodigo();
            return $this->gastoService->eliminarGasto($codigoGasto);
        });
    }

    /**
     * accion para buscar un registro en especifico
     *
     * @return array
     */
    public function actionBuscar(): array
    {
        return $this->withTryCatch(function() {
            $codigoGasto = $this->obtenerCodigo();
            return $this->gastoService->obtenerModelo($codigoGasto);
        });
    }

    /**
     * obtiene y valida si se recibio el codigo por el request
     *
     * return int
     * @throws ValidationException
     */
    private function obtenerCodigo(): int
    {
        $codigo = (int)Yii::$app->request->post('codigoGasto');
        if (!$codigo) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'Codigo Gasto no enviado.', 404);
        }
        return $codigo;
    }
}