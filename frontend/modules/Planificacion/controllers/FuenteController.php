<?php

namespace app\modules\Planificacion\controllers;

use app\controllers\BaseController;
use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\formModels\FuenteForm;
use app\modules\Planificacion\services\FuenteService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class FuenteController extends BaseController
{
    public function __construct(
        $id,
        $module,
        private FuenteService $service,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [[
                    'allow' => true,
                    'roles' => ['@'],
                ]],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'listar-todo' => ['POST'],
                    'listar-fuentes-s2' => ['POST'],
                    'guardar' => ['POST'],
                    'actualizar' => ['POST'],
                    'buscar' => ['POST'],
                    'cambiar-estado' => ['POST'],
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

    public function actionListarFuentesS2(): array
    {
        return $this->withTryCatch(fn() => $this->service->listarS2());
    }

    public function actionGuardar(): array
    {
        return $this->withTryCatch(fn() => $this->service->guardar($this->cargarFormulario()));
    }

    public function actionActualizar(): array
    {
        return $this->withTryCatch(
            fn() => $this->service->actualizar($this->obtenerId(), $this->cargarFormulario())
        );
    }

    public function actionBuscar(): array
    {
        return $this->withTryCatch(fn() => $this->service->obtenerModelo($this->obtenerId()));
    }

    public function actionCambiarEstado(): array
    {
        return $this->withTryCatch(fn() => $this->service->cambiarEstado($this->obtenerId()));
    }

    public function actionEliminar(): array
    {
        return $this->withTryCatch(fn() => $this->service->eliminar($this->obtenerId()));
    }

    private function cargarFormulario(): FuenteForm
    {
        $form = new FuenteForm();
        if (!$form->load(Yii::$app->request->post(), '') || !$form->validate()) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                $form->getErrors(),
                400
            );
        }
        return $form;
    }

    private function obtenerId(): string
    {
        $id = (string)Yii::$app->request->post('idFuente', '');
        if ($id === '') {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'No se recibió la fuente.',
                400
            );
        }
        return $id;
    }
}
