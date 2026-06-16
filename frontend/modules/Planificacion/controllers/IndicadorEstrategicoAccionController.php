<?php

namespace app\modules\Planificacion\controllers;

use app\controllers\BaseController;
use app\modules\Planificacion\services\ObjetivoEstrategicoService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;

/**
 * @noinspection PhpUnused
 */
class IndicadorEstrategicoAccionController extends BaseController
{
    private ObjetivoEstrategicoService $objetivoEstrategicoService;

    public function __construct($id, $module, ObjetivoEstrategicoService $objetivoEstrategicoService, $config = [])
    {
        $this->objetivoEstrategicoService = $objetivoEstrategicoService;
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

    /**
     *  Acción para listar registros
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     * @noinspection PhpUnused
     */
    public function actionListarObjsEstrategicos(): array
    {
        return $this->withTryCatch(function () {
            $request = $_REQUEST;
            $q = $this->getSearchParam($request);

            return $this->objetivoEstrategicoService->listarObjEstrategicosS2($q);

        });


    }

    private function getSearchParam(array $request)
    {
        $id = Yii::$app->request->post('q');
        if (!$id) {
            $id = '%%';
        }
        return $id;
    }
}