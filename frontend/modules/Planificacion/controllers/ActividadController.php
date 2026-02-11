<?php
namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\services\ActividadService;
use app\modules\Planificacion\formModels\ActividadForm;
use app\modules\Planificacion\services\ProgramaService;
use yii\web\BadRequestHttpException;
use app\controllers\BaseController;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use Yii;

/**
 * @noinspection PhpUnused
 */
class ActividadController extends BaseController
{
    private ActividadService $service;
    private ProgramaService $programaService;

    public function __construct($id, $module, ActividadService $service, ProgramaService $programaService, $config = [])
    {
        $this->service = $service;
        $this->programaService = $programaService;
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
        if ($action->id == 'listar-todo') {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    public function actionIndex(): string
    {
        return $this->render('actividad');
    }

    /**
     * Acción para listar todos los registros del modelo.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     */
    public function actionListarTodo(): array
    {
        return $this->withTryCatch(fn() => $this->service->listarTodo());
    }

    /**
     * accion para listar todos los registros del modelo para el llenado de Select2.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     * @noinspection PhpUnused
     *
     */
    public function actionListarActividadesS2(): array
    {
        $search = '%' . str_replace(" ","%", $_POST['q'] ?? '') . '%';
        return $this->withTryCatch(fn() => $this->service->listarActividadesS2($search)) ;
    }

    /**
     * Acción para agregar un nuevo registro.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     */
    public function actionGuardar(): array
    {
        return $this->withTryCatch(function () {
            $request = Yii::$app->request;
            $form = new ActividadForm();

            if (!$form->load($request->post(), '') || !$form->validate() || !$this->programaService->validarId($form->idPrograma)) {
                throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], $form->getErrors(), 400);
            }

            return $this->service->guardar($form);
        });
    }

    /**
     * Acción para actualizar los valores de un registro existente.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     */
    public function actionActualizar(): array
    {
        return $this->withTryCatch(function () {
            $request = Yii::$app->request;

            $id = $this->obtenerId();
            $form = new ActividadForm();

            if (!$form->load($request->post(), '') || !$form->validate() || !$this->programaService->validarId($form->idPrograma)) {
                throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], $form->getErrors(), 400);
            }

            return $this->service->actualizar($id, $form);
        });
    }

    /**
     * Acción para alternar el estado de un registro V/C.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     */
    public function actionCambiarEstado(): array
    {
        return $this->withTryCatch(function () {
            $id = $this->obtenerId();
            return $this->service->cambiarEstado($id);
        });
    }

    /**
     * Acción para soft delete de un registro.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     */
    public function actionEliminar(): array
    {
        return $this->withTryCatch(function () {
            $id = $this->obtenerId();
            return $this->service->eliminar($id);
        });
    }

    /**
     * Acción para buscar un registro en específico.
     *
     * @return array
     */
    public function actionBuscar(): array
    {
        return $this->withTryCatch(function () {
            $id = $this->obtenerId();
            return $this->service->obtenerModelo($id);
        });
    }

    /**
     * Obtiene y valida si se recibió el código por el request.
     *
     * @return string
     * @throws ValidationException
     */
    private function obtenerId(): string
    {
        $id = Yii::$app->request->post('idActividad');
        if (!$id) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'id Actividad no enviado.', 404);
        }
        return $id;
    }

    /**
     * accion para verificar un codigo ingresado
     *
     * @return bool
     * @noinspection PhpUnused
     */
    public function actionVerificarCodigo(): bool
    {
        $id = Yii::$app->request->post('idActividad');
        if (!isset($id)) {
            return false;
        }

        $idPrograma = Yii::$app->request->post('idPrograma');
        if (!isset($idPrograma)) {
            return false;
        }

        $codigo = Yii::$app->request->post('codigo');
        if (!isset($codigo)) {
            return false;
        }

        return $this->service->verificarCodigo($id, $idPrograma, $codigo);
    }
}