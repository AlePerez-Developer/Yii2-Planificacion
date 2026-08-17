<?php

namespace app\modules\Planificacion\controllers;

use app\controllers\BaseController;
use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\formModels\OperacionForm;
use app\modules\Planificacion\models\EstadoPoa;
use app\modules\Planificacion\models\ObjetivoEspecifico;
use app\modules\Planificacion\services\OperacionService;
use common\models\Estado;
use common\models\seguridad\EstadosPoa as EstadoPoaSeguridad;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class OperacionController extends BaseController
{
    public function __construct(
        $id,
        $module,
        private OperacionService $service,
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
                    'listar-objetivos-s2' => ['POST'],
                    'listar-indicadores-programados-s2' => ['POST'],
                    'listar-llaves-s2' => ['POST'],
                    'guardar' => ['POST'],
                    'actualizar' => ['POST'],
                    'buscar' => ['POST'],
                    'guardar-meta-trimestral' => ['POST'],
                    'cambiar-estado' => ['POST'],
                    'eliminar' => ['POST'],
                    'verificar-codigo' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $this->obtenerContextoActivo();
        return $this->render('index');
    }

    public function actionListarTodo(): array
    {
        [$idUnidadEjecutora, $idGestion, $idEstadoPoa] = $this->obtenerContextoActivo();
        return $this->withTryCatch(
            fn() => $this->service->listarTodo(
                $idUnidadEjecutora,
                $idGestion,
                $idEstadoPoa
            )
        );
    }

    public function actionListarObjetivosS2(): array
    {
        $this->obtenerContextoActivo();
        $objetivos = ObjetivoEspecifico::listAll()
            ->orderBy(['Compuesto' => SORT_ASC])
            ->asArray()
            ->all();

        $data = array_map(static fn(array $objetivo): array => [
            'id' => $objetivo['IdObjEspecifico'],
            'text' => $objetivo['Objetivo'],
            'compuesto' => $objetivo['Compuesto'],
        ], $objetivos);

        return ResponseHelper::success($data);
    }

    public function actionListarIndicadoresProgramadosS2(): array
    {
        [$idUnidadEjecutora, $idGestion] = $this->obtenerContextoActivo();
        return $this->withTryCatch(
            fn() => $this->service->listarIndicadoresProgramados(
                $idUnidadEjecutora,
                $idGestion
            )
        );
    }

    public function actionListarLlavesS2(): array
    {
        [$idUnidadEjecutora] = $this->obtenerContextoActivo();
        return $this->withTryCatch(
            fn() => $this->service->listarLlaves($idUnidadEjecutora)
        );
    }

    public function actionGuardar(): array
    {
        return $this->withTryCatch(function () {
            [$idUnidadEjecutora, $idGestion, $idEstadoPoa] =
                $this->obtenerContextoActivo();

            return $this->service->guardar(
                $this->cargarFormulario(),
                $idUnidadEjecutora,
                $idGestion,
                $idEstadoPoa
            );
        });
    }

    public function actionActualizar(): array
    {
        return $this->withTryCatch(function () {
            [$idUnidadEjecutora, $idGestion, $idEstadoPoa] =
                $this->obtenerContextoActivo();

            return $this->service->actualizar(
                $this->obtenerId(),
                $this->cargarFormulario(),
                $idUnidadEjecutora,
                $idGestion,
                $idEstadoPoa
            );
        });
    }

    public function actionBuscar(): array
    {
        [$idUnidadEjecutora, $idGestion, $idEstadoPoa] = $this->obtenerContextoActivo();
        return $this->withTryCatch(fn() => $this->service->obtenerModelo(
            $this->obtenerId(),
            $idUnidadEjecutora,
            $idGestion,
            $idEstadoPoa
        ));
    }

    public function actionGuardarMetaTrimestral(): array
    {
        return $this->withTryCatch(function () {
            [$idUnidadEjecutora, $idGestion, $idEstadoPoa] = $this->obtenerContextoActivo();
            $trimestre = filter_var(
                Yii::$app->request->post('trimestre'),
                FILTER_VALIDATE_INT
            );
            $meta = filter_var(
                Yii::$app->request->post('meta'),
                FILTER_VALIDATE_INT
            );

            if (
                $trimestre === false
                || $trimestre < 1
                || $trimestre > 4
                || $meta === false
                || $meta < 0
            ) {
                throw new ValidationException(
                    Yii::$app->params['ERROR_ENVIO_DATOS'],
                    'El trimestre o la meta no son válidos.',
                    400
                );
            }

            return $this->service->guardarMetaTrimestral(
                $this->obtenerId(),
                $trimestre,
                $meta,
                $idUnidadEjecutora,
                $idGestion,
                $idEstadoPoa
            );
        });
    }

    public function actionCambiarEstado(): array
    {
        [$idUnidadEjecutora, $idGestion, $idEstadoPoa] = $this->obtenerContextoActivo();
        return $this->withTryCatch(fn() => $this->service->cambiarEstado(
            $this->obtenerId(),
            $idUnidadEjecutora,
            $idGestion,
            $idEstadoPoa
        ));
    }

    public function actionEliminar(): array
    {
        [$idUnidadEjecutora, $idGestion, $idEstadoPoa] = $this->obtenerContextoActivo();
        return $this->withTryCatch(fn() => $this->service->eliminar(
            $this->obtenerId(),
            $idUnidadEjecutora,
            $idGestion,
            $idEstadoPoa
        ));
    }

    public function actionVerificarCodigo(): bool
    {
        [$idUnidadEjecutora, $idGestion, $idEstadoPoa] = $this->obtenerContextoActivo();

        return $this->service->verificarCodigo(
            (string)Yii::$app->request->post(
                'idOperacion',
                '00000000-0000-0000-0000-000000000000'
            ),
            (string)Yii::$app->request->post('idObjEspecifico', ''),
            (string)Yii::$app->request->post('codigo', ''),
            $idUnidadEjecutora,
            $idGestion,
            $idEstadoPoa
        );
    }

    private function cargarFormulario(): OperacionForm
    {
        $form = new OperacionForm();

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
        $idUnidadEjecutora = (string)($contexto?->IdUnidadEjecutora ?? '');
        $idGestion = (string)($contexto?->IdGestion ?? '');
        $idEstadoPoaSeguridad = (string)($contexto?->IdEstadoPoa ?? '');

        if (
            $idUnidadEjecutora === ''
            || $idGestion === ''
            || $idEstadoPoaSeguridad === ''
        ) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'Debe seleccionar gestión, unidad ejecutora y estado POA.',
                400
            );
        }

        return [
            $idUnidadEjecutora,
            $idGestion,
            $this->resolverCodigoEstadoPoa($idEstadoPoaSeguridad),
        ];
    }

    private function resolverCodigoEstadoPoa(string $idEstadoPoa): int
    {
        $estadoSeguridad = EstadoPoaSeguridad::findOne([
            'IdEstadoPoa' => $idEstadoPoa,
        ]);

        if ($estadoSeguridad === null) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'El estado POA del contexto activo no es válido.',
                400
            );
        }

        $codigo = trim((string)$estadoSeguridad->Codigo);
        $estado = ctype_digit($codigo)
            ? EstadoPoa::findOne(['CodigoEstadoPOA' => (int)$codigo])
            : EstadoPoa::find()
                ->where(['CodigoEstado' => Estado::ESTADO_VIGENTE])
                ->andWhere([
                    'or',
                    ['Descripcion' => $estadoSeguridad->Descripcion],
                    ['Abreviacion' => $codigo],
                ])
                ->one();

        if ($estado === null) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'No existe equivalencia del estado POA activo en EstadosPOA.',
                400
            );
        }

        return (int)$estado->CodigoEstadoPOA;
    }
}
