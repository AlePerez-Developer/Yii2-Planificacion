<?php

namespace app\modules\Planificacion\controllers;

use app\controllers\BaseController;
use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\services\ControlEnvioPoaService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class ControlEnvioPoaController extends BaseController
{
    public function __construct(
        $id,
        $module,
        private ControlEnvioPoaService $service,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'listar-todo' => ['POST'],
                    'eliminar' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        return $this->render('index');
    }

    public function actionListarTodo(): array
    {
        return $this->withTryCatch(fn() => $this->service->listarTodo());
    }

    public function actionEliminar(): array
    {
        $id = (string)Yii::$app->request->post('idControlEnvio', '');
        if ($id === '') {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'No se recibió el envío.',
                400
            );
        }
        return $this->withTryCatch(fn() => $this->service->eliminar($id));
    }
}
