<?php

namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\services\ObjetivoInstitucionalService;
use app\modules\Planificacion\formModels\ObjetivoInstitucionalForm;
use app\modules\Planificacion\services\ObjetivoEstrategicoService;
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
class ObjInstitucionalController extends BaseController
{
    private ObjetivoInstitucionalService $service;
    private ObjetivoEstrategicoService $serviceEstrategico;

    public function __construct($id, $module,
                                ObjetivoInstitucionalService $service,
                                ObjetivoEstrategicoService $serviceEstrategico,
        $config = [])
    {
        $this->service = $service;
        $this->serviceEstrategico = $serviceEstrategico;
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
                        'actions' => [
                            'index','listar-todo','verificar-codigo','guardar', 'actualizar', 'eliminar','cambiar-estado','buscar',
                            'listar-areas-estrategicas','listar-politicas-estrategicas','listar-obj-institucionals-s2'
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function () {
                            Yii::$app->contexto->validarPeiActivo();
                            return true;
                        },

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
        return $this->render('ObjInstitucionales');
    }

    /**
     * Accion para listar todos los registros del modelo.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     * @noinspection PhpUnused
     */
    public function actionListarTodo(): array
    {
        return $this->withTryCatch(fn() => $this->service->listarTodo());
    }

    /**
     * Accion para listar todos los registros del modelo para el llenado de Select2.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     * @noinspection PhpUnused
     *
     */
    public function actionListarObjInstitucionalsS2(): array
    {
        return [];
        /*$search = '%' . str_replace(" ","%", $_POST['q'] ?? '') . '%';
        return $this->withTryCatch(fn() => $this->service->listarObjInstitucionalsS2($search)) ;*/
    }


    /**
     * accion para agregar un nuevo registro.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     * @noinspection PhpUnused
     */
    public function actionGuardar(): array
    {
        return $this->withTryCatch(function () {
            $request = Yii::$app->request;
            $form = new ObjetivoInstitucionalForm();
            $form->load($request->post(), '');
            if (!$form->load($request->post(), '') || !$form->validate()
                || !$this->serviceEstrategico->validarId($form->idObjEstrategico)
            ) {
                throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], $form->getErrors(), 400);
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
            $form = new ObjetivoInstitucionalForm();

            if (!$form->load($request->post(), '') || !$form->validate()
                || !$this->serviceEstrategico->validarId($form->idObjEstrategico)
            ) {
                throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], $form->getErrors(), 400);
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
        $id = Yii::$app->request->post('idObjInstitucional');
        if (!$id) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'],'Codigo de objetivo no enviado.',404);
        }
        return $id;
    }

    /**
     * accion para verificar un codigo ingresado
     *
     * @return bool
     * @noinspection PhpUnused
     */
    public function actionVerificarCodigo(): bool
    {
        $id = Yii::$app->request->post('idObjInstitucional');
        if (!isset($id)) {
            return false;
        }

        $idObjEstrategico = Yii::$app->request->post('idObjEstrategico');
        if (!isset($idObjEstrategico)) {
            return false;
        }

        $codigo = Yii::$app->request->post('codigo');
        if (!isset($codigo)) {
            return false;
        }

        return $this->service->verificarCodigo($id, $idObjEstrategico, $codigo);
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