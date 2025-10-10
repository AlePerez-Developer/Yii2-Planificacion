<?php

namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\services\PoliticaEstrategicaService;
use app\modules\Planificacion\formModels\PoliticaEstrategicaForm;
use app\modules\Planificacion\services\AreaEstrategicaService;
use yii\web\BadRequestHttpException;
use app\controllers\BaseController;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use Yii;

class PoliticaEstrategicaController extends BaseController
{
    private PoliticaEstrategicaService $service;

    public function __construct($id, $module, PoliticaEstrategicaService $service, $config = [])
    {
        $this->service = $service;
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
                'actions' => [],
            ],
        ];
    }

    /**
     * @throws BadRequestHttpException
     */
    public function beforeAction($action): bool
    {
        if ($action->id == 'listar-todo') {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    public function actionIndex(): string
    {
        return $this->render('PoliticasEstrategicas');
    }

    /**
     * accion para listar todos los registros del modelo.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     */
    public function actionListarTodo(): array
    {
        return $this->withTryCatch(fn() => $this->service->listarPoliticas());
    }

    /**
     * accion para listar todos los registros del modelo.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     */
    public function actionListarAreasEstrategicas(): array
    {
        $search = '%' . str_replace(" ","%", $_POST['q'] ?? '') . '%';

        $serviceAreaEstrategica = new AreaEstrategicaService();

        return $this->withTryCatch(fn() => $serviceAreaEstrategica->listarAreasS2($search));
    }

    /**
     * accion para agregar un nuevo registro.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     */
    public function actionGuardar(): array
    {
        return $this->withTryCatch(function () {
            $request = Yii::$app->request;

            $form = new PoliticaEstrategicaForm();
            if (!$form->load($request->post(), '') || !$form->validate()) {
                throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], $form->getErrors(), 400);
            }
            return $this->service->guardar($form);
        });
    }

    /**
     * accion para actualizar los valores de un registro existente.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     */
    public function actionActualizar(): array
    {
        return $this->withTryCatch(function () {
            $request = Yii::$app->request;

            $codigo = $this->obtenerCodigo();
            $form = new PoliticaEstrategicaForm();

            if (!$form->load($request->post(), '') || !$form->validate()) {
                throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], $form->getErrors(), 400);
            }

            return $this->service->actualizar($codigo, $form);
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
            $codigo = $this->obtenerCodigo();
            return $this->service->cambiarEstado($codigo);
        });
    }

    /**
     * accion para soft delete de un registro
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     */
    public function actionEliminar(): array
    {
        return $this->withTryCatch(function () {
            $codigo = $this->obtenerCodigo();
            return $this->service->eliminar($codigo);
        });
    }

    /**
     * accion para buscar un registro en especifico
     *
     * @return array
     */
    public function actionBuscar(): array
    {
        return $this->withTryCatch(function () {
            $codigo = $this->obtenerCodigo();
            return $this->service->obtenerModelo($codigo);
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
        $request = Yii::$app->request->post();
        $codigo = (int)($request['codigoPoliticaEstrategica'] ?? $request['CodigopoliticaEstrategica'] ?? $request['codigo'] ?? $request['Codigo'] ?? 0);
        if (!$codigo) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'Código Área Estratégica no enviado.', 404);
        }
        return $codigo;
    }
}
