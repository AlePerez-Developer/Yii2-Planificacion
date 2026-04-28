<?php

namespace app\modules\Planificacion\controllers;

use app\controllers\BaseController;
use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\services\IndicadorEstrategicoService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;

/**
 * @noinspection PhpUnused
 */
class ProgramarIndicadorController extends BaseController
{
    private IndicadorEstrategicoService $serviceIndicador;

    public function __construct($id, $module,
                                IndicadorEstrategicoService $serviceIndicador,
        $config = [])
    {
        $this->serviceIndicador = $serviceIndicador;
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

    public function actionIndex(string $id): string
    {
        return $this->render('Programar', ['id' => $id]);
    }

    /**
     * accion para listar todos los registros del modelo.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     * @noinspection PhpUnused
     * @throws ValidationException
     */
    public function actionListarTodo(): array
    {
        $id = $this->obtenerId();
        return $this->withTryCatch(fn() => $this->serviceIndicador->listarTodobyObj($id));
    }

    /**
     * obtiene y valida si se recibio el id por el request
     *
     * return string
     * @throws ValidationException
     */
    private function obtenerId(): string
    {
        $id = Yii::$app->request->post('idObjEstrategico');
        if (!$id) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'Id de indicador estrategico no enviado.', 404);
        }
        return $id;
    }

    public function actionGestiones(): array
    {

        return [
            ['gestion' => 2026],
            ['gestion' => 2027],
            ['gestion' => 2028],
            ['gestion' => 2029],
            ['gestion' => 2030],
        ];
    }

}