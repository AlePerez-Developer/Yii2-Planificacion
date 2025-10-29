<?php

namespace app\modules\Planificacion\controllers;

use app\controllers\BaseController;
use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\formModels\EstadoPoaForm;
use app\modules\Planificacion\services\EstadoPoaService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;

class EstadoPoaController extends BaseController
{
    protected array $accionesSinValidacion = ['index', 'listar-todo'];

    private EstadoPoaService $estadoPoaService;

    public function __construct($id, $module, EstadoPoaService $estadoPoaService, $config = [])
    {
        $this->estadoPoaService = $estadoPoaService;
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

    public function actionIndex(): string
    {
        return $this->render('EstadoPoa');
    }

    public function actionListarTodo(): array
    {
        return $this->withTryCatch(fn () => $this->estadoPoaService->listarEstadosPoa());
    }

    public function actionGuardar(): array
    {
        return $this->withTryCatch(function () {
            $request = Yii::$app->request;
            $form = new EstadoPoaForm();

            if (!$form->load($request->post(), '') || !$form->validate()) {
                throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], $form->getErrors(), 400);
            }

            return $this->estadoPoaService->guardarEstadoPoa($form);
        });
    }

    public function actionActualizar(): array
    {
        return $this->withTryCatch(function () {
            $request = Yii::$app->request;
            $codigoEstadoPoa = $this->obtenerCodigo();
            $form = new EstadoPoaForm();

            if (!$form->load($request->post(), '') || !$form->validate()) {
                throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], $form->getErrors(), 400);
            }

            return $this->estadoPoaService->actualizarEstadoPoa($codigoEstadoPoa, $form);
        });
    }

    public function actionCambiarEstado(): array
    {
        return $this->withTryCatch(function () {
            $codigoEstadoPoa = $this->obtenerCodigo();
            return $this->estadoPoaService->cambiarEstado($codigoEstadoPoa);
        });
    }

    public function actionEliminar(): array
    {
        return $this->withTryCatch(function () {
            $codigoEstadoPoa = $this->obtenerCodigo();
            return $this->estadoPoaService->eliminarEstadoPoa($codigoEstadoPoa);
        });
    }

    public function actionBuscar(): array
    {
        return $this->withTryCatch(function () {
            $codigoEstadoPoa = $this->obtenerCodigo();
            return $this->estadoPoaService->obtenerModelo($codigoEstadoPoa);
        });
    }

    /**
     * @throws ValidationException
     */
    private function obtenerCodigo(): int
    {
        $codigo = (int)Yii::$app->request->post('codigoEstadoPoa');
        if (!$codigo) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'Código de Estado POA no enviado.',
                404
            );
        }

        return $codigo;
    }
}
