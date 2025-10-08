<?php
namespace app\modules\Planificacion\controllers;

use app\controllers\BaseController;
use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\services\UnidadService;
use yii\web\BadRequestHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use Yii;

class UnidadController extends BaseController
{
    private UnidadService $unidadService;

    public function __construct($id, $module, UnidadService $unidadService, $config = [])
    {
        $this->unidadService = $unidadService;
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
        return $this->render('unidad');
    }

    public function actionListarTodo(): array
    {
        return $this->withTryCatch(fn() => $this->unidadService->listar());
    }

    

    public function actionGuardar(): array
    {
        return $this->withTryCatch(function () {
            $post = Yii::$app->request->post();
            return $this->unidadService->guardar($post);
        });
    }

    public function actionActualizar(): array
    {
        return $this->withTryCatch(function () {
            $post = Yii::$app->request->post();
            return $this->unidadService->actualizar($post);
        });
    }

    public function actionCambiarEstado(): array
    {
        return $this->withTryCatch(function () {
            $codigo = $this->obtenerCodigo();
            return $this->unidadService->cambiarEstado($codigo);
        });
    }

    public function actionEliminar(): array
    {
        return $this->withTryCatch(function () {
            $codigo = $this->obtenerCodigo();
            return $this->unidadService->eliminar($codigo);
        });
    }

    public function actionBuscar(): array
    {
        return $this->withTryCatch(function () {
            $codigo = $this->obtenerCodigo();
            return $this->unidadService->buscar($codigo);
        });
    }

    

    /**
     * @throws ValidationException
     */
    private function obtenerCodigo(): int
    {
        $codigo = (int)Yii::$app->request->post('codigoUnidad');
        if (!$codigo) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'Codigo Unidad no enviado.', 404);
        }
        return $codigo;
    }
}