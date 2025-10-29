<?php

namespace app\modules\Planificacion\controllers;

use app\controllers\BaseController;
use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\formModels\LlavePresupuestariaForm;
use app\modules\Planificacion\models\Actividad;
use app\modules\Planificacion\models\Programa;
use app\modules\Planificacion\models\Proyecto;
use app\modules\Planificacion\models\Unidad;
use app\modules\Planificacion\services\LlavePresupuestariaService;
use common\models\Estado;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;

class LlavePresupuestariaController extends BaseController
{
    private LlavePresupuestariaService $llaveService;

    public function __construct($id, $module, LlavePresupuestariaService $llaveService, $config = [])
    {
        $this->llaveService = $llaveService;
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

    public function actionIndex(): string
    {
        $unidades = Unidad::find()
            ->where(['CodigoEstado' => Estado::ESTADO_VIGENTE])
            ->orderBy(['Da' => SORT_ASC, 'Ue' => SORT_ASC])
            ->all();

        $programas = Programa::find()
            ->where(['CodigoEstado' => Estado::ESTADO_VIGENTE])
            ->orderBy(['CodigoPrograma' => SORT_ASC])
            ->all();

        $proyectos = Proyecto::find()
            ->where(['CodigoEstado' => Estado::ESTADO_VIGENTE])
            ->orderBy(['Programa' => SORT_ASC, 'Codigo' => SORT_ASC])
            ->all();

        $actividades = Actividad::find()
            ->where(['CodigoEstado' => Estado::ESTADO_VIGENTE])
            ->orderBy(['Programa' => SORT_ASC, 'Codigo' => SORT_ASC])
            ->all();

        return $this->render('llavePresupuestaria', compact('unidades', 'programas', 'proyectos', 'actividades'));
    }

    public function actionListarTodo(): array
    {
        return $this->withTryCatch(fn () => $this->llaveService->listarLlaves());
    }

    public function actionGuardar(): array
    {
        return $this->withTryCatch(function () {
            $request = Yii::$app->request;
            $form = new LlavePresupuestariaForm();

            if (!$form->load($request->post(), '') || !$form->validate()) {
                throw new ValidationException(
                    Yii::$app->params['ERROR_ENVIO_DATOS'] ?? 'errorEnvio',
                    $form->getErrors(),
                    400
                );
            }

            return $this->llaveService->guardarLlave($form);
        });
    }

    public function actionActualizar(): array
    {
        return $this->withTryCatch(function () {
            $request = Yii::$app->request;
            $claves = $this->obtenerClaves('Original');

            $form = new LlavePresupuestariaForm();
            if (!$form->load($request->post(), '') || !$form->validate()) {
                throw new ValidationException(
                    Yii::$app->params['ERROR_ENVIO_DATOS'] ?? 'errorEnvio',
                    $form->getErrors(),
                    400
                );
            }

            return $this->llaveService->actualizarLlave(
                $claves['unidad'],
                $claves['programa'],
                $claves['proyecto'],
                $claves['actividad'],
                $form
            );
        });
    }

    public function actionCambiarEstado(): array
    {
        return $this->withTryCatch(function () {
            $claves = $this->obtenerClaves();

            return $this->llaveService->cambiarEstado(
                $claves['unidad'],
                $claves['programa'],
                $claves['proyecto'],
                $claves['actividad']
            );
        });
    }

    public function actionEliminar(): array
    {
        return $this->withTryCatch(function () {
            $claves = $this->obtenerClaves();

            return $this->llaveService->eliminarLlave(
                $claves['unidad'],
                $claves['programa'],
                $claves['proyecto'],
                $claves['actividad']
            );
        });
    }

    public function actionFinalizar(): array
    {
        return $this->withTryCatch(function () {
            $claves = $this->obtenerClaves();

            return $this->llaveService->finalizarLlave(
                $claves['unidad'],
                $claves['programa'],
                $claves['proyecto'],
                $claves['actividad']
            );
        });
    }

    public function actionBuscar(): array
    {
        return $this->withTryCatch(function () {
            $claves = $this->obtenerClaves();

            return $this->llaveService->obtenerModelo(
                $claves['unidad'],
                $claves['programa'],
                $claves['proyecto'],
                $claves['actividad']
            );
        });
    }

    /**
     * @throws ValidationException
     */
    private function obtenerClaves(string $sufijo = ''): array
    {
        $post = Yii::$app->request->post();
        $append = $sufijo ? $sufijo : '';

        $unidad = (int)($post['codigoUnidad' . $append] ?? 0);
        $programa = (int)($post['codigoPrograma' . $append] ?? 0);
        $proyecto = (int)($post['codigoProyecto' . $append] ?? 0);
        $actividad = (int)($post['codigoActividad' . $append] ?? 0);

        if (!$unidad || !$programa || !$proyecto || !$actividad) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'] ?? 'errorEnvio',
                'Identificadores de la llave presupuestaria incompletos.',
                404
            );
        }

        return [
            'unidad' => $unidad,
            'programa' => $programa,
            'proyecto' => $proyecto,
            'actividad' => $actividad,
        ];
    }
}
