<?php

namespace app\modules\Planificacion\controllers;

use app\controllers\BaseController;
use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\formModels\TechoUnidadForm;
use app\modules\Planificacion\services\TechoUnidadService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class TechoUnidadController extends BaseController
{
    public function __construct(
        $id,
        $module,
        private TechoUnidadService $service,
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
                    'listar-llaves' => ['POST'],
                    'resumen' => ['POST'],
                    'guardar' => ['POST'],
                    'eliminar' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $this->obtenerContexto();
        return $this->render('index');
    }

    public function actionListarLlaves(): array
    {
        [$unidad, $gestion, $estado] = $this->obtenerContexto();
        return $this->withTryCatch(
            fn() => $this->service->listarLlaves($unidad, $gestion, $estado)
        );
    }

    public function actionResumen(): array
    {
        [$unidad, $gestion, $estado] = $this->obtenerContexto();
        return $this->withTryCatch(
            fn() => $this->service->resumen($unidad, $gestion, $estado)
        );
    }

    public function actionGuardar(): array
    {
        return $this->withTryCatch(function () {
            [$unidad, $gestion, $estado] = $this->obtenerContexto();
            return $this->service->guardar(
                $this->cargarFormulario(),
                $unidad,
                $gestion,
                $estado
            );
        });
    }

    public function actionEliminar(): array
    {
        return $this->withTryCatch(function () {
            [$unidad, $gestion, $estado] = $this->obtenerContexto();
            $id = (string)Yii::$app->request->post('idAsignacion', '');
            if ($id === '') {
                throw new ValidationException(
                    Yii::$app->params['ERROR_ENVIO_DATOS'],
                    'No se recibió la asignación.',
                    400
                );
            }
            return $this->service->eliminar($id, $unidad, $gestion, $estado);
        });
    }

    private function cargarFormulario(): TechoUnidadForm
    {
        $form = new TechoUnidadForm();
        if (!$form->load(Yii::$app->request->post(), '') || !$form->validate()) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                $form->getErrors(),
                400
            );
        }
        return $form;
    }

    private function obtenerContexto(): array
    {
        $contexto = Yii::$app->userContext->contexto();
        $unidad = (string)($contexto?->IdUnidadEjecutora ?? '');
        $gestion = (string)($contexto?->IdGestion ?? '');
        $estado = (string)($contexto?->IdEstadoPoa ?? '');

        if ($unidad === '' || $gestion === '' || $estado === '') {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'Debe seleccionar unidad ejecutora, gestión y estado POA.',
                400
            );
        }
        return [$unidad, $gestion, $estado];
    }
}
