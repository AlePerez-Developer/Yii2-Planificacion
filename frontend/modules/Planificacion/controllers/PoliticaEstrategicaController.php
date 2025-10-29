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

/**
 * @noinspection PhpUnused
 */
class PoliticaEstrategicaController extends BaseController
{
    private PoliticaEstrategicaService $service;
    private AreaEstrategicaService $serviceAreaEstrategica;

    public function __construct($id, $module, PoliticaEstrategicaService $service, AreaEstrategicaService $serviceAreaEstrategica, $config = [])
    {
        $this->service = $service;
        $this->serviceAreaEstrategica = $serviceAreaEstrategica;
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
     * @noinspection PhpUnused
     */
    public function actionListarTodo(): array
    {
        return $this->withTryCatch(fn() => $this->service->listarTodo());
    }

    /**
     * accion para listar todos los registros del modelo para el llenado de select2.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     * @throws ValidationException
     * @noinspection PhpUnused
 */
    public function actionListarPoliticasS2(): array
    {
        $search = '%' . str_replace(" ","%", $_POST['q'] ?? '') . '%';
        $idAreaEstrategica = $this->obtenerIdAreaEstrategica();
        return $this->withTryCatch(fn() => $this->service->listarPoliticasS2($idAreaEstrategica, $search));
    }

    /**
     * accion para agregar un nuevo registro.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     * @noinspection PhpUnused
     */
    public function actionGuardar(): array
    {
        return $this->withTryCatch(function () {
            $request = Yii::$app->request;
            $form = new PoliticaEstrategicaForm();
            if (!$form->load($request->post(), '') || !$form->validate() || !$this->serviceAreaEstrategica->validarId($form->idAreaEstrategica)) {
                throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], $form->getErrors(), 400);
            }

            return $this->service->guardar($form);
        });
    }

    /**
     * accion para actualizar los valores de un registro existente.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     * @noinspection PhpUnused
     */
    public function actionActualizar(): array
    {
        return $this->withTryCatch(function () {
            $request = Yii::$app->request;

            $id = $this->obtenerId();
            $form = new PoliticaEstrategicaForm();

            if (!$form->load($request->post(), '') || !$form->validate() || !$this->serviceAreaEstrategica->validarId($form->idAreaEstrategica)) {
                throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], $form->getErrors(), 400);
            }

            return $this->service->actualizar($id, $form);
        });
    }

    /**
     * accion para alternar el estado de un registro V/C.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     * @noinspection PhpUnused
     */
    public function actionCambiarEstado(): array
    {
        return $this->withTryCatch(function() {
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
     * accion para buscar un registro en especifico
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
     * obtiene y valida si se recibio el id por el request
     *
     * return string
     * @throws ValidationException
     */
    private function obtenerId(): string
    {
        $id = Yii::$app->request->post('idPoliticaEstrategica');
        if (!$id) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'Id de Politica Estratégica no enviado.', 404);
        }
        return $id;
    }

    /**
     * obtiene y valida si se recibio el id por el request
     *
     * return string
     * @throws ValidationException
     */
    private function obtenerIdAreaEstrategica(): string
    {
        $id = Yii::$app->request->post('idAreaEstrategica');
        if (!$id) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'Id de Área Estratégica no enviado.', 404);
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
        $id = Yii::$app->request->post('idPoliticaEstrategica');
        if (!isset($id)) {
            return false;
        }

        $idAreaEstrategica = Yii::$app->request->post('idAreaEstrategica');
        if (!isset($idAreaEstrategica)) {
            return false;
        }

        $codigo = Yii::$app->request->post('codigo');
        if (!isset($codigo)) {
            return false;
        }

        return $this->service->verificarCodigo($id, $idAreaEstrategica, $codigo);
    }
}
