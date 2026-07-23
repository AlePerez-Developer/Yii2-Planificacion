<?php

namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\services\ObjetivoInstitucionalService;
use app\modules\Planificacion\formModels\ObjetivoInstitucionalForm;
use yii\web\BadRequestHttpException;
use app\controllers\BaseController;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Request;
use Yii;

/**
 * @noinspection PhpUnused
 */
class ObjInstitucionalController extends BaseController
{
    private ObjetivoInstitucionalService $service;

    public function __construct(
        $id,
        $module,
        ObjetivoInstitucionalService $service,
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
                'rules' => [
                    [
                        'actions' => [
                            'index', 'listar-todo', 'listar-obj-institucionales-s2', 'verificar-codigo', 'guardar',
                            'actualizar', 'eliminar', 'cambiar-estado', 'buscar',
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => static function (): bool {
                            Yii::$app->contexto->validarPeiActivo();
                            return true;
                        },
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'listar-todo' => ['POST'],
                    'verificar-codigo' => ['POST'],
                    'guardar' => ['POST'],
                    'actualizar' => ['POST'],
                    'eliminar' => ['POST'],
                    'cambiar-estado' => ['POST'],
                    'buscar' => ['POST'],
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
        return $this->render('ObjInstitucionales');
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
     * Accion para listar todos los registros del modelo para el llenado de Select2.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     * @noinspection PhpUnused
     *
     */
    public function actionListarObjInstitucionalesS2(): array
    {
        $request = Yii::$app->request;

        $q = $this->getSearchParam($request);

        return $this->withTryCatch(fn() => $this->service->listarObjInstitucionalesS2($q)) ;
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
            $form = new ObjetivoInstitucionalForm();

            $contexto = Yii::$app->userContext->contexto();
            $form->idGestion = $contexto?->IdGestion;

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

            $form = new ObjetivoInstitucionalForm();

            $contexto = Yii::$app->userContext->contexto();
            $form->idGestion = $contexto?->IdGestion;

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
     * Accion para soft delete de un registro
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
     * Accion para buscar un registro en específico
     *
     * @return array
     * @noinspection PhpUnused
     */
    public function actionBuscar(): array
    {
        return $this->withTryCatch(function () {
            $id = $this->obtenerId();
            return $this->service->obtenerModelo($id);
        });
    }


    /**
     * Obtiene y válida si se recibio el codigo por el request
     *
     * @return string
     * @throws ValidationException
     */
    private function obtenerId(): string
    {
        $id = Yii::$app->request->post('idObjInstitucional');
        if (!$id) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'Codigo de objetivo no enviado.', 404);
        }
        return $id;
    }

    /**
     * Accion para verificar un codigo ingresado
     *
     * @return bool
     * @noinspection PhpUnused
     */
    public function actionVerificarCodigo(): bool
    {
        $id = Yii::$app->request->post('idObjInstitucional');
        if (!isset($id)) {
            return false;
        }

        $idObjEstrategico = Yii::$app->request->post('idObjEstrategico');
        if (!isset($idObjEstrategico)) {
            return false;
        }

        $codigo = Yii::$app->request->post('codigo');
        if (!isset($codigo)) {
            return false;
        }

        return $this->service->verificarCodigo($id, $idObjEstrategico, $codigo);
    }

    /**
     * Obtiene el parámetro de búsqueda de Select2
     * @param Request $request
     * @return string
     */
    private function getSearchParam(Request $request): string
    {
        $id = $request->post('q');

        if (!$id) {
            return '';
        }

        return $id;
    }
}
