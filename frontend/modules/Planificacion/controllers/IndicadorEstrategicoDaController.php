<?php

namespace app\modules\Planificacion\controllers;

use app\controllers\BaseController;
use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\formModels\ProgramacionIndicadorDaForm;
use app\modules\Planificacion\services\ProgramacionIndicadorDaService;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use Yii;

/**
 * @noinspection PhpUnused
 */
class IndicadorEstrategicoDaController extends BaseController
{
    protected array $accionesSinValidacion = ['index'];

    private ProgramacionIndicadorDaService $service;

    public function __construct($id, $module, ProgramacionIndicadorDaService $service, $config = [])
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
        if (in_array($action->id, ['listar-indicadores', 'listar-das'], true)) {
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
    public function actionListarDas(): array
    {
        return $this->withTryCatch(function () {
            $idIndicador = $this->obtenerIdIndicador();
            return $this->service->listarDasPorIndicador($idIndicador);
        });
    }

    /**
     * @noinspection PhpUnused
     */
    public function actionAsignar(): array
    {
        return $this->withTryCatch(function () {
            $form = $this->obtenerForm();
            return $this->service->asignar($form);
        });
    }

    /**
     * @noinspection PhpUnused
     */
    public function actionEliminar(): array
    {
        return $this->withTryCatch(function () {
            $form = $this->obtenerForm();
            return $this->service->eliminar($form);
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
        return $id;
    }

    /**
     * @throws ValidationException
     */
    private function obtenerForm(): ProgramacionIndicadorDaForm
    {
        $form = new ProgramacionIndicadorDaForm();
        if (!$form->load(Yii::$app->request->post(), '') || !$form->validate()) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                $form->getErrors(),
                400
            );
        }
        return $form;
    }
}
