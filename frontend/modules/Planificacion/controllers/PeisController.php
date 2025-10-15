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

/**
 * @noinspection PhpUnused
 */
class PeisController extends BaseController
{
    private PeiService $service;
    public function __construct($id, $module, PeiService $service, $config = [])
    {
        $this->service = $service;
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
        Yii::$app->contexto->setPei(1);
        return $this->render('peis');
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
     * accion para agregar un nuevo registro.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     * @noinspection PhpUnused
     */
    public function actionGuardar(): array
    {
        return $this->withTryCatch( function() {
            $request = Yii::$app->request;

            $form = new PeiForm();

            if (!$form->load($request->post(), '') || !$form->validate()) {
                throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'],$form->getErrors(),400);
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
        return $this->withTryCatch(function() {
            $request = Yii::$app->request;

            $id = $this->obtenerId();
            $form = new PeiForm();

            if (!$form->load($request->post(), '') || !$form->validate()) {
                throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'],$form->getErrors(),400);
            }

            return $this->service->actualizar($id,$form);
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
        return $this->withTryCatch(function() {
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
        return $this->withTryCatch(function() {
            $id = $this->obtenerId();
            return $this->service->obtenerModelo($id);
        });
    }

    /**
     * obtiene y valida si se recibio el codigo por el request
     *
     * return string
     * @throws ValidationException
     */
    private function obtenerId(): string
    {
        $id = Yii::$app->request->post('idPei');
        if (!$id) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'],'Codigo Pei no enviado.',404);
        }
        return $id;
    }

    /**
     * @throws MpdfException
     * @noinspection PhpUnused
     */
    public function actionReporte(): void
    {
        $mpdf = new Mpdf();
        $mpdf->SetMargins(0, 0,32);

        $mpdf->Output();
    }
}