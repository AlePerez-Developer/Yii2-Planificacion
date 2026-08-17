<?php

namespace app\modules\Planificacion\controllers;

use app\controllers\BaseController;
use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\formModels\IndicadorPoaForm;
use app\modules\Planificacion\services\IndicadorPoaService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class IndicadorPoaController extends BaseController
{
    public function __construct(
        $id,
        $module,
        private IndicadorPoaService $service,
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
                    'guardar' => ['POST'],
                    'actualizar' => ['POST'],
                    'buscar' => ['POST'],
                    'eliminar' => ['POST'],
                    'cambiar-estado' => ['POST'],
                    'verificar-codigo' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $this->obtenerIdGestion();
        return $this->render('index');
    }

    public function actionListarTodo(): array
    {
        return $this->withTryCatch(
            fn() => $this->service->listarTodo($this->obtenerIdGestion())
        );
    }

    public function actionGuardar(): array
    {
        return $this->withTryCatch(
            fn() => $this->service->guardar(
                $this->cargarFormulario(),
                $this->obtenerIdGestion()
            )
        );
    }

    public function actionActualizar(): array
    {
        return $this->withTryCatch(
            fn() => $this->service->actualizar(
                $this->obtenerId(),
                $this->cargarFormulario(),
                $this->obtenerIdGestion()
            )
        );
    }

    public function actionBuscar(): array
    {
        return $this->withTryCatch(
            fn() => $this->service->obtenerModelo(
                $this->obtenerId(),
                $this->obtenerIdGestion()
            )
        );
    }

    public function actionCambiarEstado(): array
    {
        return $this->withTryCatch(
            fn() => $this->service->cambiarEstado(
                $this->obtenerId(),
                $this->obtenerIdGestion()
            )
        );
    }

    public function actionEliminar(): array
    {
        return $this->withTryCatch(
            fn() => $this->service->eliminar(
                $this->obtenerId(),
                $this->obtenerIdGestion()
            )
        );
    }

    public function actionVerificarCodigo(): bool
    {
        return $this->service->verificarCodigo(
            (string)Yii::$app->request->post(
                'idIndicadorPoa',
                '00000000-0000-0000-0000-000000000000'
            ),
            $this->obtenerIdGestion(),
            (int)Yii::$app->request->post('codigo', 0)
        );
    }

    private function cargarFormulario(): IndicadorPoaForm
    {
        $form = new IndicadorPoaForm();

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
        $id = (string)Yii::$app->request->post('idIndicadorPoa', '');

        if ($id === '') {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'No se recibió el identificador del indicador POA.',
                400
            );
        }

        return $id;
    }

    private function obtenerIdGestion(): string
    {
        $idGestion = (string)(
            Yii::$app->userContext->contexto()?->IdGestion ?? ''
        );

        if ($idGestion === '') {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'Debe seleccionar una gestión en el contexto activo.',
                400
            );
        }

        return $idGestion;
    }
}
