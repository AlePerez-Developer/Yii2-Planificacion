<?php
namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\services\AreaEstrategicaService;
use app\modules\Planificacion\services\ObjetivoEstrategicoService;
use app\modules\Planificacion\formModels\ObjetivoEstrategicoForm;
use app\controllers\BaseController;
use app\modules\Planificacion\services\PoliticaEstrategicaService;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use Mpdf\MpdfException;
use Mpdf\Mpdf;
use Yii;

class ObjEstrategicoController extends BaseController
{
    private ObjetivoEstrategicoService $objetivoService;

    public function __construct($id, $module, ObjetivoEstrategicoService $objetivoService, $config = [])
    {
        $this->objetivoService = $objetivoService;
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
                            'index','listar-todo','verificar-codigo','guardar','eliminar','cambiar-estado','buscar',
                            'listar-areas-estrategicas','listar-politicas-estrategicas'
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
        yii::$app->contexto->setPei(2);
        return $this->render('objEstrategico');
    }

    /**
     * accion para listar todos los registros del modelo.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     */
    public function actionListarAreasEstrategicas(): array
    {
        $search = '%' . str_replace(" ","%", $_POST['q'] ?? '') . '%';
        $serviceAreaEstrategica = new AreaEstrategicaService();
        return $this->withTryCatch(fn() => $serviceAreaEstrategica->listarAreasS2($search));
    }

    /**
     * accion para listar todos los registros del modelo.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     */
    public function actionListarPoliticasEstrategicas(): array
    {
        $search = '%' . str_replace(" ","%", $_POST['q'] ?? '') . '%';
        $area = $this->obtenerCodigoArea();
        $servicePoliticaEstrategica = new PoliticaEstrategicaService();
        return $this->withTryCatch(fn() => $servicePoliticaEstrategica->listarPoliticasByArea($area,$search));
    }

    /**
     * accion para listar todos los registros del modelo.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     */
    public function actionListarTodo(): array
    {
        return $this->withTryCatch(fn() => $this->objetivoService->listarObjetivos());
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

            $form = new ObjetivoEstrategicoForm();
            $form->pei = Yii::$app->contexto->getPei();

            if (!$form->load($request->post(), '') || !$form->validate()) {
                throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'],$form->getErrors(),400);
            }

            return $this->objetivoService->guardarObjetivo($form);
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

            $codigoObjEstrategico = $this->obtenerCodigo();
            $form = new ObjetivoEstrategicoForm();

            if (!$form->load($request->post(), '') || !$form->validate()) {
                throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'],$form->getErrors(),400);
            }

            return $this->objetivoService->actualizarObjetivo($codigoObjEstrategico,$form);
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
            $codigoObjEstrategico = $this->obtenerCodigo();
            return $this->objetivoService->cambiarEstado($codigoObjEstrategico);
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
            $codigoObjEstrategico = $this->obtenerCodigo();
            return $this->objetivoService->eliminarObjetivo($codigoObjEstrategico);
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
            $codigoObjEstrategico = $this->obtenerCodigo();
            return $this->objetivoService->obtenerModelo($codigoObjEstrategico);
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
        $codigo = (int)Yii::$app->request->post('codigoObjEstrategico');
        if (!$codigo) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'],'Codigo de objetivo no enviado.',404);
        }
        return $codigo;
    }

    /**
     * obtiene y valida si se recibio el codigo de are por el request
     *
     * return int
     */
    private function obtenerCodigoArea(): int
    {
        $codigo = (int)Yii::$app->request->post('area');
        if (!$codigo) {
            return 0;
        }
        return $codigo;
    }

    public function actionVerificarCodigo(): bool
    {
        return true;
        return $this->objetivoService->verificarCodigo(yii::$app->contexto->getPei(), 0, $_POST["codigo"]);
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
