<?php

namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\services\IndicadorEstrategicoAccionService;
use app\modules\Planificacion\formModels\IndicadorEstrategicoAccionForm;
use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\services\IndicadorEstrategicoService;
use yii\web\BadRequestHttpException;
use app\controllers\BaseController;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use Yii;


/**
 * @noinspection PhpUnused
 */
class IndicadorEstrategicoAccionController extends BaseController
{
    private IndicadorEstrategicoAccionService $service;
    private IndicadorEstrategicoService $indicadorEstrategicoService;

    public function __construct($id, $module, IndicadorEstrategicoAccionService $service,
                                IndicadorEstrategicoService $indicadorEstrategicoService,
                                $config = [])
    {
        $this->service = $service;
        $this->indicadorEstrategicoService = $indicadorEstrategicoService;
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
                    'logout' => ['post'],
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
    public function actionListarIndicadores(): array
    {
        return $this->withTryCatch(function () {
            $id = $this->obtenerId();
            return $this->indicadorEstrategicoService->listarTodobyObj($id);
        });
    }

    /**
     * Accion para agregar un nuevo registro.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     * @noinspection PhpUnused
     */
    public function actionGuardarAccion(): array
    {
        return $this->withTryCatch(function () {
            $request = Yii::$app->request;

            $id = $this->obtenerIdIndicador();
            $form = new IndicadorEstrategicoAccionForm();

            if (!$form->load($request->post(), '') || !$form->validate()) {
                throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'],$form->getErrors(),400);
            }

            return $this->service->validarActualizar($id,$form);
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
        $id = Yii::$app->request->post('idObjEstrategico');
        if (!$id) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'Codigo de objetivo no enviado.', 404);
        }
        return $id;
    }

    /**
     * Obtiene y válida si se recibio el codigo por el request
     *
     * return string
     * @throws ValidationException
     */
    private function obtenerIdIndicador(): string
    {
        $id = Yii::$app->request->post('idIndicadorEstrategico');
        if (!$id) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'Codigo de objetivo no enviado.', 404);
        }
        return $id;
    }
}