<?php

namespace app\modules\Planificacion\controllers;

use app\controllers\BaseController;
use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\services\IndicadorEstrategicoProgramacionTrimestralService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/** @noinspection PhpUnused */
class IndicadorEstrategicoProgramacionTrimestralController extends BaseController
{
    private IndicadorEstrategicoProgramacionTrimestralService $service;

    public function __construct(
        $id,
        $module,
        IndicadorEstrategicoProgramacionTrimestralService $service,
        $config = []
    ) {
        $this->service = $service;
        parent::__construct($id, $module, $config);
    }

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => [],
                'rules' => [
                    [
                        'actions' => [],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => [
                            'index',
                            'listar-indicadores',
                            'obtener-gestion-activa',
                            'listar-programacion',
                            'guardar-meta',
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function (): bool {
                            Yii::$app->contexto->validarPeiActivo();
                            $this->obtenerIdGestionContexto();
                            return true;
                        },
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'listar-indicadores' => ['post'],
                    'obtener-gestion-activa' => ['post'],
                    'listar-programacion' => ['post'],
                    'guardar-meta' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        return $this->render('index');
    }

    public function actionListarIndicadores(): array
    {
        return $this->withTryCatch(function () {
            return $this->service->listarIndicadores(
                $this->obtenerIdObjEstrategico(),
                $this->obtenerIdGestionContexto()
            );
        });
    }

    public function actionObtenerGestionActiva(): array
    {
        return $this->withTryCatch(fn() =>
            $this->service->obtenerGestionActiva($this->obtenerIdGestionContexto())
        );
    }

    public function actionListarProgramacion(): array
    {
        return $this->withTryCatch(function () {
            return $this->service->listarProgramacion(
                $this->obtenerIdIndicadorEstrategico(),
                $this->obtenerIdGestionContexto()
            );
        });
    }

    public function actionGuardarMeta(): array
    {
        return $this->withTryCatch(function () {
            return $this->service->guardarMeta(
                $this->obtenerIdProgramacion(),
                $this->obtenerTrimestre(),
                $this->obtenerMeta()
            );
        });
    }

    /**
     * @throws ValidationException
     */
    private function obtenerIdGestionContexto(): string
    {
        $contexto = Yii::$app->userContext->contexto();
        $idGestion = $contexto?->IdGestion;

        if (!$idGestion) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'No existe una gestión seleccionada en el contexto activo.',
                400
            );
        }

        return (string)$idGestion;
    }

    /**
     * @throws ValidationException
     */
    private function obtenerIdObjEstrategico(): string
    {
        $id = Yii::$app->request->post('idObjEstrategico');
        if (!$id) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'No se envió el objetivo estratégico.',
                400
            );
        }
        return (string)$id;
    }

    /**
     * @throws ValidationException
     */
    private function obtenerIdIndicadorEstrategico(): string
    {
        $id = Yii::$app->request->post('idIndicadorEstrategico');
        if (!$id) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'No se envió el indicador estratégico.',
                400
            );
        }
        return (string)$id;
    }

    /**
     * @throws ValidationException
     */
    private function obtenerIdProgramacion(): string
    {
        $id = Yii::$app->request->post('idProgramacionIndicadorGestio');
        if (!$id) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'No se envió la programación anual.',
                400
            );
        }
        return (string)$id;
    }

    /**
     * @throws ValidationException
     */
    private function obtenerTrimestre(): int
    {
        $trimestre = filter_var(Yii::$app->request->post('trimestre'), FILTER_VALIDATE_INT);
        if ($trimestre === false) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'El trimestre enviado no es válido.',
                400
            );
        }
        return $trimestre;
    }

    private function obtenerMeta(): int
    {
        $meta = filter_var(Yii::$app->request->post('meta'), FILTER_VALIDATE_INT);
        if ($meta === false || $meta < 0) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'La meta trimestral no es válida.',
                400
            );
        }
        return $meta;
    }
}
