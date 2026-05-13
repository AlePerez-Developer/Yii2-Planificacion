<?php

namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\services\LlavePresupuestariaService;
use app\modules\Planificacion\formModels\LlavePresupuestariaForm;
use yii\web\BadRequestHttpException;
use app\controllers\BaseController;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use Yii;


/**
 * @noinspection PhpUnused
 */

class LlavePresupuestariaController extends BaseController
{
    private LlavePresupuestariaService $service;


    public function __construct(
        $id,
        $module,
        LlavePresupuestariaService $service,

        $config = []
    )
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
                'actions' => [
                    'listar-todo' => ['get', 'post'],
                    'guardar' => ['post'],
                    'actualizar' => ['post'],
                    'cambiar-estado' => ['post'],
                    'eliminar' => ['post'],
                    'buscar' => ['post'],
                    'finalizar' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @throws BadRequestHttpException
     */
    public function beforeAction($action): bool
    {
        if ($action->id === 'listar-todo') {
            $this->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }

    /**
     * accion index.
     *
     * @return string
     */
    public function actionIndex(): string
    {
        return $this->render('index');
    }

    /**
     * Accion para listar todos los registros del modelo.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     * @noinspection PhpUnused
     */
    public function actionListarTodo(): array
    {
        return $this->withTryCatch(fn() => $this->service->listarTodo());
    }

    /**
     * Accion para agregar un nuevo registro.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     * @noinspection PhpUnused
     */
    public function actionGuardar(): array
    {
        return $this->withTryCatch(function () {
            $request = Yii::$app->request;

            $form = new LlavePresupuestariaForm();

            if (!$form->load($request->post(), '') || !$form->validate()) {
                throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], $form->getErrors(), 400);
            }

            return $this->service->validarGuardar($form);
        });
    }

    /**
     * Accion para actualizar los valores de un registro existente.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     * @noinspection PhpUnused
     */
    public function actionActualizar(): array
    {
        return $this->withTryCatch(function () {
            $request = Yii::$app->request;

            $id = $this->obtenerId();
            $form = new LlavePresupuestariaForm();

            if (!$form->load($request->post(), '') || !$form->validate()) {
                throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], $form->getErrors(), 400);
            }

            return $this->service->validarActualizar($id, $form);
        });
    }

    /**
     * Accion para alternar el estado de un registro V/C.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     * @noinspection PhpUnused
     */
    public function actionCambiarEstado(): array
    {
        return $this->withTryCatch(function () {
            $id = $this->obtenerId();
            return $this->service->cambiarEstado($id);
        });
    }

    /**
     * accion para soft delete de un registro
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     * @noinspection PhpUnused
     */
    public function actionEliminar(): array
    {
        return $this->withTryCatch(function () {
            $id = $this->obtenerId();
            return $this->service->eliminar($id);
        });
    }

    /**
     * accion para soft delete de un registro
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     * @noinspection PhpUnused
     */
    public function actionFinalizar(): array
    {
        return $this->withTryCatch(function () {
            $id = $this->obtenerId();
            return $this->service->finalizar($id);
        });
    }

    /**
     * Accion para buscar un registro en específico
     *
     * @return array
     * @noinspection PhpUnused
     */
    public function actionBuscar(): array
    {
        return $this->withTryCatch(function () {
            $id = $this->obtenerId();
            return $this->service->obtenerModeloCompleto($id);
        });
    }

    /**
     * Obtiene y válida si se recibio el codigo por el request
     *
     * return string
     * @throws ValidationException
     */
    private function obtenerId(): string
    {
        $id = Yii::$app->request->post('idLlavePresupuestaria');
        if (!$id) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'Codigo de Llave presupuestaria no enviado.', 404);
        }
        return $id;
    }

    /**
     * Verifica si la llave resultante ya existe
     *
     * @return boolean
     * @noinspection PhpUnused
     * @throws ValidationException
     */
    public function actionVerificarLlave(): bool
    {
        $id = $this->obtenerId();

        $parametros = ['idDa', 'idUe', 'idPrograma', 'idProyecto', 'idActividad'];

        foreach ($parametros as $param) {
            $$param = Yii::$app->request->post($param);
            if (!isset($$param)) {
                return false;
            }
        }

        return $this->service->VerificarLlave($id, $idDa, $idUe, $idProyecto, $idActividad);
    }
}