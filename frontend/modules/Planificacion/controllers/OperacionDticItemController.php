<?php

namespace app\modules\Planificacion\controllers;

use app\controllers\BaseController;
use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\formModels\ProgramacionItemOperacionDticForm;
use app\modules\Planificacion\models\LlavePresupuestaria;
use app\modules\Planificacion\services\ProgramacionItemOperacionDticService;
use common\models\Estado;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class OperacionDticItemController extends BaseController
{
    public function __construct(
        $id,
        $module,
        private ProgramacionItemOperacionDticService $service,
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
                    'listar' => ['POST'],
                    'guardar' => ['POST'],
                    'quitar' => ['POST'],
                ],
            ],
        ];
    }

    public function actionListar(): array
    {
        [$idLlave, $idGestion, $idEstadoPoa, $codigoEstadoPoa] = $this->obtenerContextoActivo();

        return $this->withTryCatch(fn() => $this->service->listar(
            $this->obtenerIdOperacion(),
            $idLlave,
            $idGestion,
            $idEstadoPoa,
            $codigoEstadoPoa
        ));
    }

    public function actionGuardar(): array
    {
        return $this->withTryCatch(function () {
            [$idLlave, $idGestion, $idEstadoPoa, $codigoEstadoPoa] = $this->obtenerContextoActivo();

            return $this->service->guardar(
                $this->cargarFormulario(),
                $idLlave,
                $idGestion,
                $idEstadoPoa,
                $codigoEstadoPoa
            );
        });
    }

    public function actionQuitar(): array
    {
        [$idLlave, $idGestion, $idEstadoPoa, $codigoEstadoPoa] = $this->obtenerContextoActivo();
        $idProgramacionItem = (string)Yii::$app->request->post('idProgramacionItem', '');

        if ($idProgramacionItem === '') {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'No se recibió el ítem programado.',
                400
            );
        }

        return $this->withTryCatch(fn() => $this->service->quitar(
            $this->obtenerIdOperacion(),
            $idProgramacionItem,
            $idLlave,
            $idGestion,
            $idEstadoPoa,
            $codigoEstadoPoa
        ));
    }

    private function cargarFormulario(): ProgramacionItemOperacionDticForm
    {
        $form = new ProgramacionItemOperacionDticForm();

        if (!$form->load(Yii::$app->request->post(), '') || !$form->validate()) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                $form->getErrors(),
                400
            );
        }

        return $form;
    }

    private function obtenerIdOperacion(): string
    {
        $id = (string)Yii::$app->request->post('idOperacion', '');

        if ($id === '') {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'No se recibió la operación.',
                400
            );
        }

        return $id;
    }

    private function obtenerContextoActivo(): array
    {
        $contexto = Yii::$app->userContext->contexto();
        $idGestion = (string)($contexto?->IdGestion ?? '');
        $idEstadoPoa = (string)($contexto?->IdEstadoPoa ?? '');
        $idUnidad = (string)($contexto?->IdUnidadEjecutora ?? '');
        $codigoEstadoPoa = filter_var(
            $contexto?->estadoPoa?->Codigo,
            FILTER_VALIDATE_INT
        );

        $llave = LlavePresupuestaria::find()
            ->where(['IdUnidadEjecutora' => $idUnidad, 'esOrganizacional' => 1])
            ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->one();

        if (
            $idGestion === ''
            || $idEstadoPoa === ''
            || $idUnidad === ''
            || $codigoEstadoPoa === false
            || $llave === null
        ) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'El contexto activo no contiene una gestión, estado POA y llave válidos.',
                400
            );
        }

        return [
            $llave->IdLlavePresupuestaria,
            $idGestion,
            $idEstadoPoa,
            (int)$codigoEstadoPoa,
        ];
    }
}
