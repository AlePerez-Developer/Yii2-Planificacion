<?php

namespace app\modules\Planificacion\controllers;

use app\controllers\BaseController;
use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\services\PermisoMenuService;
use app\modules\Planificacion\services\UsuarioSeguridadService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class PermisoMenuController extends BaseController
{
    public function __construct(
        $id,
        $module,
        private PermisoMenuService $service,
        private UsuarioSeguridadService $usuarioService,
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
                    'listar-catalogos' => ['POST'],
                    'listar' => ['POST'],
                    'guardar' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        return $this->render('index');
    }

    public function actionListarCatalogos(): array
    {
        return $this->withTryCatch(fn() => $this->usuarioService->listarCatalogos());
    }

    public function actionListar(): array
    {
        return $this->withTryCatch(fn() => $this->service->listar(
            (string)Yii::$app->request->post('idUsuario', ''),
            (string)Yii::$app->request->post('idUnidadEjecutora', ''),
            (string)Yii::$app->request->post('idGestion', ''),
            (string)Yii::$app->request->post('idEstadoPoa', '')
        ));
    }

    public function actionGuardar(): array
    {
        $items = Yii::$app->request->post('items', []);
        if (!is_array($items)) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'No se recibieron permisos.', 400);
        }
        return $this->withTryCatch(fn() => $this->service->guardar(
            (string)Yii::$app->request->post('idUsuario', ''),
            (string)Yii::$app->request->post('idUnidadEjecutora', ''),
            (string)Yii::$app->request->post('idGestion', ''),
            (string)Yii::$app->request->post('idEstadoPoa', ''),
            $items
        ));
    }
}
