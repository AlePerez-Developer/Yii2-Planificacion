<?php

namespace app\modules\Planificacion\controllers;

use app\controllers\BaseController;
use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\formModels\ProgramaForm;
use app\modules\Planificacion\services\ProgramaService;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use Yii;

class ProgramaController extends BaseController
{
    private ProgramaService $programaService;

    public function __construct($id, $module, ProgramaService $programaService, $config = [])
    {
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
                    // Soporte nuevo y legacy
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
        if ($action->id == 'listar-todo' || $action->id == 'listar-programas') {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    /**
     * Acción index.
     *
     * @return string
     */
    public function actionIndex(): string
    {
        return $this->render('programa');
    }

    /**
     * Acción para listar todos los registros del modelo.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     */
    public function actionListarTodo(): array
    {
        return $this->withTryCatch(fn() => $this->programaService->listarProgramas());
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

            $form = new ProgramaForm();

            if (!$form->load($request->post(), '') || !$form->validate()) {
                throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], $form->getErrors(), 400);
            }

            return $this->programaService->guardarPrograma($form);
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

            $codigoPrograma = $this->obtenerCodigo();
            $form = new ProgramaForm();

            if (!$form->load($request->post(), '') || !$form->validate()) {
                throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], $form->getErrors(), 400);
            }

            return $this->programaService->actualizarPrograma($codigoPrograma, $form);
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
            $codigoPrograma = $this->obtenerCodigo();
            return $this->programaService->cambiarEstado($codigoPrograma);
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
            $codigoPrograma = $this->obtenerCodigo();
            return $this->programaService->eliminarPrograma($codigoPrograma);
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
            $codigoPrograma = $this->obtenerCodigo();
            return $this->programaService->obtenerModelo($codigoPrograma);
        });
    }

    /**
     * Obtiene y valida si se recibió el código por el request.
     *
     * @return int
     * @throws ValidationException
     */
    private function obtenerCodigo(): int
    {
        $codigo = (int)Yii::$app->request->post('codigoPrograma');
        if (!$codigo) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'Código Programa no enviado.', 404);
        }
        return $codigo;
    }
}
