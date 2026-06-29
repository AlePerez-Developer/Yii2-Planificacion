<?php

namespace app\modules\Planificacion\controllers;

use app\controllers\BaseController;
use app\modules\Planificacion\models\IndicadorEstrategico;
use app\modules\Planificacion\services\IndicadorEstrategicoAccionService;
use app\modules\Planificacion\services\IndicadorEstrategicoService;
use app\modules\Planificacion\services\ObjetivoEstrategicoService;
use app\modules\Planificacion\common\exceptions\ValidationException;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Request;

/**
 * @noinspection PhpUnused
 */
class IndicadorEstrategicoAccionController extends BaseController
{
    private IndicadorEstrategicoAccionService $service;
    private IndicadorEstrategicoService $indicadorEstrategicoService;

    public function __construct($id, $module, IndicadorEstrategicoAccionService $service,
                                IndicadorEstrategicoService $indicadorEstrategicoService,
                                $config = [])
    {
        $this->service = $service;
        $this->indicadorEstrategicoService = $indicadorEstrategicoService;
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
        return $this->render('index');
    }

    public function actionListarIndicadores(): array
    {
        return $this->withTryCatch(function () {
            $id = $this->obtenerId();
            return $this->indicadorEstrategicoService->listarTodobyObj($id);
        });
    }

    public function actionGuardarAccion()
    {

        return $this->withTryCatch(function () {
            $id = Yii::$app->request->post('id');
            $accion = Yii::$app->request->post('accion');
            $frase = Yii::$app->request->post('frase');

            $modelo = $this->obtenerModeloValidado($id);
            $modelo->IdAccionEstrategica = $accion;
            $modelo->AccionDescripcion = $frase;

            return $this->validarProcesarModelo($modelo);
        });

    }

    private function obtenerModeloValidado(string $id): ?IndicadorEstrategico
    {
        $modelo = IndicadorEstrategico::findOne($id);
        if (!$modelo) {
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],'No se encontro el registro buscado',404);
        }
        return $modelo;
    }

    public function validarProcesarModelo(IndicadorEstrategico $modelo): array
    {
        if (!$modelo->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'],$modelo->getErrors(),500);
        }

        if (!$modelo->save(false)) {
            Yii::error("Error al guardar los datos del Indicador Estrategico $modelo->Codigo", __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'],$modelo->getErrors(),500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => '',
        ];
    }






    /**
     * Obtiene y válida si se recibio el codigo por el request
     *
     * return string
     * @throws ValidationException
     */
    private function obtenerId(): string
    {
        $id = Yii::$app->request->post('idObjEstrategico');
        if (!$id) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'Codigo de objetivo no enviado.', 404);
        }
        return $id;
    }


    /**
     *  Acción para listar registros
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     * @noinspection PhpUnused
     */
    public function actionListarObjsEstrategicos(): array
    {
        return $this->withTryCatch(function () {
            $request = Yii::$app->request;

            $q = $this->getSearchParam($request);

            return $this->objetivoEstrategicoService->listarObjEstrategicosS2($q);

        });


    }

    /**
     * Obtiene el parámetro de búsqueda de Select2
     * @param Request $request
     * @return string
     */
    private function getSearchParam(Request $request): string
    {
        $id = $request->post('q');

        if (!$id) {
            return '';
        }

        return $id;
    }
}