<?php

namespace app\modules\Planificacion\controllers;

use app\controllers\BaseController;
use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\formModels\ProgramacionIndicadorMetaAnualGlobalForm;
use app\modules\Planificacion\services\ProgramacionIndicadorMetaAnualGlobalService;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use Yii;

/**
 * @noinspection PhpUnused
 */
class IndicadorEstrategicoProgramacionGlobalController extends BaseController
{
    protected array $accionesSinValidacion = ['index'];

    private ProgramacionIndicadorMetaAnualGlobalService $service;

    public function __construct($id, $module, ProgramacionIndicadorMetaAnualGlobalService $service, $config = [])
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
                    'listar-indicadores' => ['post'],
                    'listar-gestiones' => ['post'],
                    'guardar-meta' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @throws BadRequestHttpException
     */
    public function beforeAction($action): bool
    {
        if (in_array($action->id, ['listar-indicadores', 'listar-gestiones'], true)) {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    public function actionIndex(): string
    {
        return $this->render('index');
    }

    /**
     * @noinspection PhpUnused
     */
    public function actionListarIndicadores(): array
    {
        return $this->withTryCatch(fn() => $this->service->listarIndicadores());
    }

    /**
     * @noinspection PhpUnused
     */
    public function actionListarGestiones(): array
    {
        return $this->withTryCatch(function () {
            $idIndicador = $this->obtenerIdIndicador();
            return $this->service->listarGestionesPorIndicador($idIndicador);
        });
    }

    /**
     * @noinspection PhpUnused
     */
    public function actionGuardarMeta(): array
    {
        return $this->withTryCatch(function () {
            $form = new ProgramacionIndicadorMetaAnualGlobalForm();
            if (!$form->load(Yii::$app->request->post(), '') || !$form->validate()) {
                throw new ValidationException(
                    Yii::$app->params['ERROR_ENVIO_DATOS'],
                    $form->getErrors(),
                    400
                );
            }
            return $this->service->guardarMeta($form);
        });
    }

    /**
     * @throws ValidationException
     */
    private function obtenerIdIndicador(): string
    {
        $id = Yii::$app->request->post('idIndicador');
        if (!$id) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'Id de indicador estratégico no enviado.',
                404
            );
        }
        return (string)$id;
    }
}
