<?php

namespace app\modules\Planificacion\controllers;

use app\controllers\BaseController;
use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\formModels\ProyectoForm;
use app\modules\Planificacion\models\Programa;
use app\modules\Planificacion\services\ProyectoService;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use Yii;

class ProyectoController extends BaseController
{
    private ProyectoService $proyectoService;

    public function __construct($id, $module, ProyectoService $proyectoService, $config = [])
    {
        $this->proyectoService = $proyectoService;
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
        $programas = Programa::find()->where(['CodigoEstado' => 'V'])->all();
        return $this->render('Proyectos', [
            'programas' => $programas,
        ]);
    }

    public function actionListarTodo(): array
    {
        return $this->withTryCatch(fn() => $this->proyectoService->listarProyectos());
    }

    public function actionGuardar(): array
    {
        return $this->withTryCatch(function () {
            $request = Yii::$app->request;

            $form = new ProyectoForm();

            if (!$form->load($request->post(), '') || !$form->validate()) {
                throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], $form->getErrors(), 400);
            }

            return $this->proyectoService->guardarProyecto($form);
        });
    }

    public function actionActualizar(): array
    {
        return $this->withTryCatch(function () {
            $request = Yii::$app->request;

            $codigoProyecto = $this->obtenerCodigo();
            $form = new ProyectoForm();

            if (!$form->load($request->post(), '') || !$form->validate()) {
                throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], $form->getErrors(), 400);
            }

            return $this->proyectoService->actualizarProyecto($codigoProyecto, $form);
        });
    }

    public function actionCambiarEstado(): array
    {
        return $this->withTryCatch(function () {
            $codigoProyecto = $this->obtenerCodigo();
            return $this->proyectoService->cambiarEstado($codigoProyecto);
        });
    }

    public function actionEliminar(): array
    {
        return $this->withTryCatch(function () {
            $codigoProyecto = $this->obtenerCodigo();
            return $this->proyectoService->eliminarProyecto($codigoProyecto);
        });
    }

    public function actionBuscar(): array
    {
        return $this->withTryCatch(function () {
            $codigoProyecto = $this->obtenerCodigo();
            return $this->proyectoService->obtenerModelo($codigoProyecto);
        });
    }

    /**
     * Obtiene y valida si se recibió el código por el request
     * 
     * @return int
     * @throws ValidationException
     */
    private function obtenerCodigo(): int
    {
        $codigo = (int)Yii::$app->request->post('codigoProyecto');
        if (!$codigo) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'Código Proyecto no enviado.', 404);
        }
        return $codigo;
    }
}