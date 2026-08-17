<?php

namespace app\modules\Planificacion\controllers;

use app\controllers\BaseController;
use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\models\ObjetivoEspecifico;
use app\modules\Planificacion\services\IndicadorPoaProgramacionTrimestralService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class IndicadorPoaProgramacionTrimestralController extends BaseController
{
    public function __construct(
        $id,
        $module,
        private IndicadorPoaProgramacionTrimestralService $service,
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
                    'listar-programacion' => ['POST'],
                    'guardar-meta' => ['POST'],
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

    public function actionListarProgramacion(): array
    {
        [$idUnidadEjecutora, $idGestion] = $this->obtenerContextoActivo();

        return $this->withTryCatch(fn() => $this->service->listarProgramacion(
            $this->obtenerIdObjetivo(),
            $idUnidadEjecutora,
            $idGestion
        ));
    }

    public function actionGuardarMeta(): array
    {
        return $this->withTryCatch(function () {
            [$idUnidadEjecutora, $idGestion] = $this->obtenerContextoActivo();
            $idProgramacion = (string)Yii::$app->request->post(
                'idProgramacionIndicadorPoaGestion',
                ''
            );
            $trimestre = filter_var(
                Yii::$app->request->post('trimestre'),
                FILTER_VALIDATE_INT
            );
            $meta = filter_var(
                Yii::$app->request->post('meta'),
                FILTER_VALIDATE_INT
            );

            if (
                $idProgramacion === ''
                || $trimestre === false
                || $trimestre < 1
                || $trimestre > 4
                || $meta === false
                || $meta < 0
            ) {
                throw new ValidationException(
                    Yii::$app->params['ERROR_ENVIO_DATOS'],
                    'La programación, el trimestre o la meta no son válidos.',
                    400
                );
            }

            return $this->service->guardarMeta(
                $idProgramacion,
                $this->obtenerIdObjetivo(),
                $trimestre,
                $meta,
                $idUnidadEjecutora,
                $idGestion
            );
        });
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
