<?php

namespace app\modules\Planificacion\controllers;

use app\controllers\BaseController;
use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\formModels\UsuarioAsignacionForm;
use app\modules\Planificacion\formModels\UsuarioSeguridadForm;
use app\modules\Planificacion\services\UsuarioSeguridadService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class UsuarioSeguridadController extends BaseController
{
    public function __construct(
        $id,
        $module,
        private UsuarioSeguridadService $service,
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
                    'listar-catalogos' => ['POST'],
                    'guardar' => ['POST'],
                    'actualizar' => ['POST'],
                    'buscar' => ['POST'],
                    'cambiar-estado' => ['POST'],
                    'guardar-modulos' => ['POST'],
                    'listar-asignaciones' => ['POST'],
                    'guardar-asignacion' => ['POST'],
                    'eliminar-asignacion' => ['POST'],
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

    public function actionListarCatalogos(): array
    {
        return $this->withTryCatch(fn() => $this->service->listarCatalogos());
    }

    public function actionGuardar(): array
    {
        return $this->withTryCatch(fn() => $this->service->guardar($this->cargarUsuarioForm()));
    }

    public function actionActualizar(): array
    {
        return $this->withTryCatch(
            fn() => $this->service->actualizar($this->obtenerId('idUsuario'), $this->cargarUsuarioForm())
        );
    }

    public function actionBuscar(): array
    {
        return $this->withTryCatch(fn() => $this->service->obtener($this->obtenerId('idUsuario')));
    }

    public function actionCambiarEstado(): array
    {
        return $this->withTryCatch(fn() => $this->service->cambiarEstado($this->obtenerId('idUsuario')));
    }

    public function actionGuardarModulos(): array
    {
        $modulos = Yii::$app->request->post('modulos', []);
        if (!is_array($modulos)) {
            $modulos = [];
        }
        return $this->withTryCatch(
            fn() => $this->service->guardarModulos($this->obtenerId('idUsuario'), $modulos)
        );
    }

    public function actionListarAsignaciones(): array
    {
        return $this->withTryCatch(fn() => $this->service->listarAsignaciones($this->obtenerId('idUsuario')));
    }

    public function actionGuardarAsignacion(): array
    {
        $form = new UsuarioAsignacionForm();
        if (!$form->load(Yii::$app->request->post(), '') || !$form->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], $form->getErrors(), 400);
        }
        return $this->withTryCatch(
            fn() => $this->service->guardarAsignacion($this->obtenerId('idUsuario'), $form)
        );
    }

    public function actionEliminarAsignacion(): array
    {
        return $this->withTryCatch(
            fn() => $this->service->eliminarAsignacion($this->obtenerId('idAsignacion'))
        );
    }

    private function cargarUsuarioForm(): UsuarioSeguridadForm
    {
        $form = new UsuarioSeguridadForm();
        if (!$form->load(Yii::$app->request->post(), '') || !$form->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], $form->getErrors(), 400);
        }
        return $form;
    }

    private function obtenerId(string $campo): string
    {
        $id = (string)Yii::$app->request->post($campo, '');
        if ($id === '') {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], "No se recibió {$campo}.", 400);
        }
        return $id;
    }
}
