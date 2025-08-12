<?php
namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\formModels\PeiForm;
use app\modules\Planificacion\services\PeiService;
use yii\web\BadRequestHttpException;
use app\controllers\BaseController;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use Mpdf\MpdfException;
use Mpdf\Mpdf;
use Yii;

class PeisController extends BaseController
{
    private PeiService $peiService;
    public function __construct($id, $module, PeiService $peiService, $config = [])
    {
        $this->peiService = $peiService;
        parent::__construct($id, $module, $config);
    }
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
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
                'class' => VerbFilter::className(),
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
        if ($action->id == "listar-todo")
            $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    /**
     * accion index.
     *
     * @return string
     */
    public function actionIndex(): string
    {
        return $this->render('peis');
    }

    /**
     * accion para listar todos los registros del modelo.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     */
    public function actionListarTodo(): array
    {
        return $this->withTryCatch(fn() => $this->peiService->listarPeis());
    }

    /**
     * accion para agregar un nuevo registro.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     */
    public function actionGuardar(): array
    {
        return $this->withTryCatch( function() {
            $request = Yii::$app->request;

            $form = new PeiForm();

            if (!$form->load($request->post(), '') || !$form->validate()) {
                throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'],$form->getErrors(),400);
            }

            return $this->peiService->guardarPei($form);
        });
    }

    /**
     * accion para actualizar los valores de un registro existente.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     */
    public function actionActualizar(): array
    {
        return $this->withTryCatch(function() {
            $request = Yii::$app->request;

            $codigoPei = $this->obtenerCodigo();
            $form = new PeiForm();

            if (!$form->load($request->post(), '') || !$form->validate()) {
                throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'],$form->getErrors(),400);
            }

            return $this->peiService->actualizarPei($codigoPei,$form);
        });
    }

    /**
     * accion para alternar el estado de un registro V/C.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     */
    public function actionCambiarEstado(): array
    {
        return $this->withTryCatch(function() {
            $codigoPei = $this->obtenerCodigo();
            return $this->peiService->cambiarEstado($codigoPei);
        });
    }

    /**
     * accion para soft delete de un registro
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     */
    public function actionEliminar(): array
    {
        return $this->withTryCatch(function() {
            $codigoPei = $this->obtenerCodigo();
            return $this->peiService->eliminarPei($codigoPei);
        });
    }

    /**
     * accion para buscar un registro en especifico
     *
     * @return array
     */
    public function actionBuscar(): array
    {
        return $this->withTryCatch(function() {
            $codigoPei = $this->obtenerCodigo();
            return $this->peiService->obtenerModelo($codigoPei);
        });
    }

    /**
     * obtiene y valida si se recibio el codigo por el request
     *
     * return int
     * @throws ValidationException
     */
    private function obtenerCodigo(): int
    {
        $codigo = (int)Yii::$app->request->post('codigoPei');
        if (!$codigo) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'],'Codigo Pei no enviado.',404);
        }
        return $codigo;
    }

    /**
     * @throws MpdfException
     */
    public function actionReporte(): void
    {
        $mpdf = new Mpdf();
        $mpdf->SetMargins(0, 0,32);

        $mpdf->Output();
    }
}