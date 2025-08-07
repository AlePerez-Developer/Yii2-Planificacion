<?php
namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\services\PeiService;
use app\modules\Planificacion\formModels\PeiForm;
use app\controllers\BaseController;
use yii\web\BadRequestHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use Mpdf\MpdfException;
use Mpdf\Mpdf;
use Throwable;
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
        if ($action->id == "listar-peis")
            $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionIndex(): string
    {
        return $this->render('peis');
    }

    public function actionListarPeis(): array
    {
        return $this->withTryCatch(fn() => $this->peiService->listarPeis(),'peis');
    }

    public function actionGuardar(): array
    {
        return $this->withTryCatch( function() {
            $request = Yii::$app->request;

            $form = new PeiForm();

            if (!$form->load($request->post(), '') || !$form->validate()) {
                Yii::$app->response->statusCode = 400;
                return [
                    'respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS'] ?? 'Error en el envio de datos',
                    'errors' => $form->getErrors(),
                ];
            }

            $resultado = $this->peiService->guardarPei($form);

            if (!$resultado['success']) {
                Yii::$app->response->statusCode = $resultado['code'];
                return [
                    'respuesta' => $resultado['mensaje'] ?? "ocurrio un error no definido en el procesado",
                    'errors' => $resultado['errors'],
                ];
            }

            return $resultado['success'];
        });
    }

    /**
     * @throws Throwable
     */
    public function actionActualizar(): array
    {
        $request = Yii::$app->request;

        $codigoPei = (int)$request->post('codigoPei');
        if (!$codigoPei) {
            Yii::$app->response->statusCode = 400;
            return [
                'respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS'] ?? 'Código PEI no enviado',
                'errors' => ['Se esperaba el campo codigoPei']
            ];
        }

        return $this->withTryCatch(function() use($codigoPei){
            $request = Yii::$app->request;

            $form = new PeiForm();

            if (!$form->load($request->post(), '') || !$form->validate()) {
                Yii::$app->response->statusCode = 400;
                return [
                    'respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS'] ?? 'Error en el envio de datos',
                    'errors' => $form->getErrors(),
                ];
            }

            $resultado = $this->peiService->actualizarPei($codigoPei,$form);

            if (!$resultado['success']) {
                Yii::$app->response->statusCode = $resultado['code'];
                return [
                    'respuesta' => $resultado['mensaje'] ?? "ocurrio un error no definido en el procesado",
                    'errors' => $resultado['errors'],
                ];
            }

            return $resultado['success'];
        });
    }

    public function actionCambiarEstado(): array
    {
        $request = Yii::$app->request;

        $codigoPei = (int)$request->post('codigoPei');
        if (!$codigoPei) {
            Yii::$app->response->statusCode = 400;
            return [
                'respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS'] ?? 'Código PEI no enviado',
                'errors' => ['Se esperaba el campo codigoPei']
            ];
        }

        return $this->withTryCatch(function() use($codigoPei) {
            $resultado = $this->peiService->cambiarEstado($codigoPei);

            if (!$resultado['success']) {
                Yii::$app->response->statusCode = $resultado['code'];
                return [
                    'respuesta' => $resultado['mensaje'] ?? "ocurrio un error no definido en el procesado",
                    'errors' => $resultado['errors'],
                ];
            }

            return $resultado['estado'];
        }, 'estado');
    }

    public function actionEliminar(): array
    {
        $request = Yii::$app->request;

        $codigoPei = (int)$request->post('codigoPei');
        if (!$codigoPei) {
            Yii::$app->response->statusCode = 400;
            return [
                'respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS'] ?? 'Código PEI no enviado',
                'errors' => ['Se esperaba el campo codigoPei']
            ];
        }

        return $this->withTryCatch(function() use($codigoPei) {
            $resultado = $this->peiService->eliminarPei($codigoPei);

            if (!$resultado['success']) {
                Yii::$app->response->statusCode = $resultado['code'];
                return [
                    'respuesta' => $resultado['mensaje'] ?? "ocurrio un error no definido en el procesado",
                    'errors' => $resultado['errors'],
                ];
            }

            return $resultado['success'];
        });
    }

    public function actionBuscar(): array
    {
        $request = Yii::$app->request;

        $codigoPei = (int)$request->post('codigoPei');
        if (!$codigoPei) {
            Yii::$app->response->statusCode = 400;
            return [
                'respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS'] ?? 'Código PEI no enviado',
                'errors' => ['Se esperaba el campo codigoPei']
            ];
        }

        return $this->withTryCatch(function() use($codigoPei){
            $pei = $this->peiService->listarPei($codigoPei);

            if (!$pei) {
                Yii::$app->response->statusCode = 404;
                return [
                    'respuesta' => Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'] ?? 'PEI no encontrado',
                    'errors' => null,
                ];
            }

            return $pei->getAttributes(array('CodigoPei', 'DescripcionPei', 'FechaAprobacion', 'GestionInicio', 'GestionFin'));
        }, 'pei');
    }

    /**
     * @throws MpdfException
     */
    public function actionReporte()
    {
        $mpdf = new Mpdf();
        $mpdf->SetMargins(0, 0,32);

        $mpdf->Output();
    }
}