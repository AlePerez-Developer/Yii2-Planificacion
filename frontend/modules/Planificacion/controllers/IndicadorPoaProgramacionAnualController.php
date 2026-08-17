<?php

namespace app\modules\Planificacion\controllers;

use app\controllers\BaseController;
use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\formModels\ProgramacionIndicadorPoaGestionForm;
use app\modules\Planificacion\models\ObjetivoEspecifico;
use app\modules\Planificacion\services\IndicadorPoaProgramacionAnualService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class IndicadorPoaProgramacionAnualController extends BaseController
{
    public function __construct(
        $id,
        $module,
        private IndicadorPoaProgramacionAnualService $service,
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
                    'listar-objetivos-especificos-s2' => ['POST'],
                    'listar-relaciones' => ['POST'],
                    'listar-llaves-s2' => ['POST'],
                    'listar-indicadores-s2' => ['POST'],
                    'guardar' => ['POST'],
                    'actualizar-meta' => ['POST'],
                    'eliminar' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $this->obtenerContextoActivo();
        return $this->render('index');
    }

    public function actionListarObjetivosEspecificosS2(): array
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

    public function actionListarRelaciones(): array
    {
        [$idUnidadEjecutora, $idGestion] = $this->obtenerContextoActivo();

        return $this->withTryCatch(fn() => $this->service->listarRelaciones(
            $this->obtenerIdObjetivo(),
            $idUnidadEjecutora,
            $idGestion
        ));
    }

    public function actionListarLlavesS2(): array
    {
        [$idUnidadEjecutora] = $this->obtenerContextoActivo();
        return $this->service->listarLlaves($idUnidadEjecutora);
    }

    public function actionListarIndicadoresS2(): array
    {
        [, $idGestion] = $this->obtenerContextoActivo();
        return $this->service->listarIndicadores($idGestion);
    }

    public function actionGuardar(): array
    {
        return $this->withTryCatch(function () {
            [$idUnidadEjecutora, $idGestion] = $this->obtenerContextoActivo();
            return $this->service->guardar(
                $this->cargarFormulario(),
                $idUnidadEjecutora,
                $idGestion
            );
        });
    }

    public function actionEliminar(): array
    {
        return $this->withTryCatch(function () {
            [$idUnidadEjecutora, $idGestion] = $this->obtenerContextoActivo();
            $idProgramacion = $this->obtenerIdProgramacion();

            return $this->service->eliminar(
                $idProgramacion,
                $this->obtenerIdObjetivo(),
                $idUnidadEjecutora,
                $idGestion
            );
        });
    }

    public function actionActualizarMeta(): array
    {
        return $this->withTryCatch(function () {
            [$idUnidadEjecutora, $idGestion] = $this->obtenerContextoActivo();
            $meta = filter_var(
                Yii::$app->request->post('metaProgramada'),
                FILTER_VALIDATE_INT
            );

            if ($meta === false || $meta < 0) {
                throw new ValidationException(
                    Yii::$app->params['ERROR_ENVIO_DATOS'],
                    'La meta debe ser un entero mayor o igual a cero.',
                    400
                );
            }

            return $this->service->actualizarMeta(
                $this->obtenerIdProgramacion(),
                $this->obtenerIdObjetivo(),
                $meta,
                $idUnidadEjecutora,
                $idGestion
            );
        });
    }

    private function cargarFormulario(): ProgramacionIndicadorPoaGestionForm
    {
        $form = new ProgramacionIndicadorPoaGestionForm();

        if (!$form->load(Yii::$app->request->post(), '') || !$form->validate()) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                $form->getErrors(),
                400
            );
        }

        return $form;
    }

    private function obtenerIdObjetivo(): string
    {
        $id = (string)Yii::$app->request->post('idObjEspecifico', '');

        if ($id === '') {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'Debe seleccionar un objetivo específico.',
                400
            );
        }

        return $id;
    }

    private function obtenerIdProgramacion(): string
    {
        $id = (string)Yii::$app->request->post('idProgramacion', '');

        if ($id === '') {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'No se recibió la programación.',
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

        if ($idUnidadEjecutora === '' || $idGestion === '') {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'Debe seleccionar una gestión y una unidad ejecutora.',
                400
            );
        }

        return [$idUnidadEjecutora, $idGestion];
    }
}
