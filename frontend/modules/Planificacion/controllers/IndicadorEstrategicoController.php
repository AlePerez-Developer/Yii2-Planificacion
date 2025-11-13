<?php
namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\services\CatCategoriaIndicadorService;
use app\modules\Planificacion\services\IndicadorEstrategicoService;
use app\modules\Planificacion\formModels\IndicadorEstrategicoForm;
use app\modules\Planificacion\services\ObjetivoEstrategicoService;
use app\modules\Planificacion\services\CatUnidadIndicadorService;
use app\modules\Planificacion\services\CatTipoResultadoService;
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
class IndicadorEstrategicoController extends BaseController
{
    private IndicadorEstrategicoService $service;
    private ObjetivoEstrategicoService $serviceObjEstrategico;
    private CatCategoriaIndicadorService $serviceCategoriaIndicador;
    private CatTipoResultadoService $serviceTipoResultado;
    private CatUnidadIndicadorService  $serviceUnidadIndicador;

    public function __construct($id, $module,
                                IndicadorEstrategicoService $service,
                                ObjetivoEstrategicoService $serviceObjEstrategico,
                                CatCategoriaIndicadorService $serviceCategoriaIndicador,
                                CatTipoResultadoService $serviceTipoResultado,
                                CatUnidadIndicadorService $serviceUnidadIndicador,
                                $config = [])
    {
        $this->service = $service;
        $this->serviceObjEstrategico = $serviceObjEstrategico;
        $this->serviceCategoriaIndicador = $serviceCategoriaIndicador;
        $this->serviceTipoResultado = $serviceTipoResultado;
        $this->serviceUnidadIndicador = $serviceUnidadIndicador;
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
        if ($action->id == 'listar-todo') {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    public function actionIndex(): string
    {
        return $this->render('indicadorEstrategico');
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
        return $this->withTryCatch(function () {
            $request = Yii::$app->request;

            $form = new IndicadorEstrategicoForm();
            $form->load($request->post(), '');

            if (!$form->validate() &&
                $this->serviceObjEstrategico->validarId($form->idObjEstrategico) &&
                $this->serviceUnidadIndicador->validarId($form->idUnidadIndicador) &&
                $this->serviceTipoResultado->validarId($form->idTipoResultado) &&
                $this->serviceCategoriaIndicador->validarId($form->idCategoriaIndicador)
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
        return $this->withTryCatch(function () {
            $request = Yii::$app->request;

            $id = $this->obtenerId();
            $form = new IndicadorEstrategicoForm();

            $form->load($request->post(), '');
            if (!$form->validate()) {
                throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], $form->getErrors(), 400);
            }

            return $this->service->actualizar($id, $form);
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
        return $this->withTryCatch(function () {
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
        return $this->withTryCatch(function () {
            $id = $this->obtenerId();
            return $this->service->obtenerModelo($id);
        });
    }

    /**
     * obtiene y valida si se recibio el id por el request
     *
     * return string
     * @throws ValidationException
     */
    private function obtenerId(): string
    {
        $id = Yii::$app->request->post('idIndicadorEstrategico');
        if (!$id) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'Id de indicador estrategico no enviado.', 404);
        }
        return $id;
    }

    /**
     * accion para buscar un registro en especifico
     *
     * @return bool
     * @noinspection PhpUnused
     */
    public function actionVerificarCodigo(): bool
    {
        $id = Yii::$app->request->post('idIndicadorEstrategico');
        if (!isset($id)) {
            return false;
        }

        $codigo = Yii::$app->request->post('codigo');
        if (!isset($codigo)) {
            return false;
        }

        return $this->service->verificarCodigo($id,$codigo);
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