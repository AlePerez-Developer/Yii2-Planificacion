<?php
namespace app\modules\Planificacion\controllers;

use app\controllers\BaseController;
use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\formModels\ActividadForm;
use app\modules\Planificacion\models\Programa;
use app\modules\Planificacion\services\ActividadService;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use Yii;

class ActividadController extends BaseController
{
    private ActividadService $actividadService;

    public function __construct($id, $module, ActividadService $actividadService, $config = [])
    {
        $this->actividadService = $actividadService;
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
        return $this->render('Actividades', [
            'programas' => $programas,
        ]);
    }

    public function actionListarTodo(): array
    {
        return $this->withTryCatch(fn() => $this->actividadService->listarActividades());
    }

    public function actionGuardar(): array
    {
        return $this->withTryCatch(function () {
            $request = Yii::$app->request;
            $form = new ActividadForm();

            if (!$form->load($request->post(), '') || !$form->validate()) {
                throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], $form->getErrors(), 400);
            }

            return $this->actividadService->guardarActividad($form);
        });
    }

    public function actionActualizar(): array
    {
        return $this->withTryCatch(function () {
            $request = Yii::$app->request;
            $codigoActividad = $this->obtenerCodigo();
            $form = new ActividadForm();

            if (!$form->load($request->post(), '') || !$form->validate()) {
                throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], $form->getErrors(), 400);
            }

            return $this->actividadService->actualizarActividad($codigoActividad, $form);
        });
    }

    public function actionCambiarEstado(): array
    {
        return $this->withTryCatch(function () {
            $codigoActividad = $this->obtenerCodigo();
            return $this->actividadService->cambiarEstado($codigoActividad);
        });
    }

    public function actionEliminar(): array
    {
        return $this->withTryCatch(function () {
            $codigoActividad = $this->obtenerCodigo();
            return $this->actividadService->eliminarActividad($codigoActividad);
        });
    }

    public function actionBuscar(): array
    {
        return $this->withTryCatch(function () {
            $codigoActividad = $this->obtenerCodigo();
            return $this->actividadService->obtenerModelo($codigoActividad);
        });
    }

    private function obtenerCodigo(): int
    {
        $req = Yii::$app->request->post();
        $codigo = (int)($req['codigoActividad'] ?? $req['CodigoActividad'] ?? $req['codigo'] ?? $req['Codigo'] ?? 0);
        if (!$codigo) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'Código Actividad no enviado.', 404);
        }
        return $codigo;
    }
}