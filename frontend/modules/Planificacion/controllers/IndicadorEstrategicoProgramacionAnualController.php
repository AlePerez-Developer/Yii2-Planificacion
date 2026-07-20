<?php

namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\services\IndicadorEstrategicoProgramacionAnualService;
use app\modules\Planificacion\common\exceptions\ValidationException;
use app\controllers\BaseController;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use Yii;

/**
 * @noinspection PhpUnused
 */
class IndicadorEstrategicoProgramacionAnualController extends BaseController
{
    private IndicadorEstrategicoProgramacionAnualService $service;
    public function __construct($id, $module,
                                IndicadorEstrategicoProgramacionAnualService $service,
        $config = [])
    {
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
                        'actions' => [],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function beforeAction($action): bool
    {
        if ($action->id == "listar-todo")
            $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    /**
     * accion index.
     *
     * @param string $id
     * @return string
     */
    public function actionIndex(string $id): string
    {
        return $this->render('index', ['idObjEstrategico' => $id]);
    }

    /**
     * Accion para listar todos los registros del modelo.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     * @noinspection PhpUnused
     */
    public function actionListarIndicadores(): array
    {
        return $this->withTryCatch(function () {
            $id = $this->obtenerIdObjEstrategico();
            return $this->service->listarIndicadoresbyObjConProgramacion($id);
        });
    }

    /**
     * Accion para listar todos los registros del modelo.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     * @noinspection PhpUnused
     */
    public function actionListarGestiones(): array
    {
        return $this->withTryCatch(function () {
            $id = $this->obtenerIdIndicador();
            return $this->service->listarGestionesbyPei(yii::$app->contexto->getPei(), $id);
        });
    }

    /**
     * Accion para listar todos los registros del modelo.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     * @noinspection PhpUnused
     */
    public function actionListarProgramacion(): array
    {
        return $this->withTryCatch(function () {
            $pei = Yii::$app->contexto->getPei();

            $codigoIndicador = $this->obtenerCodigoIndicador();
            $gestion = $this->obtenerGestion();

            return $this->service->listarProgramacionbyGestion($codigoIndicador, $gestion, $pei);
        });
    }

    /**
     * Accion para listar todos los registros del modelo.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     * @noinspection PhpUnused
     */
    public function actionListarLlaves(): array
    {
        return $this->withTryCatch(function () {
            $pei = Yii::$app->contexto->getPei();

            $codigoIndicador = $this->obtenerCodigoIndicador();
            $gestion = $this->obtenerGestion();

            return $this->service->listarLlavesPresupuestarias($codigoIndicador, $gestion, $pei);
        });
    }

    /**
     * Agrega o elimina una llave presupuestaria a la programacion
     * @return array
     */
    public function actionCambiarEstado(): array
    {
        return $this->withTryCatch(function () {
            $pei = Yii::$app->contexto->getPei();

            $codigoIndicador = $this->obtenerCodigoIndicador();
            $gestion = $this->obtenerGestion();
            $idLlavePresupuestaria = $this->obtenerIdLlave();

            return $this->service->cambiarEstado($idLlavePresupuestaria, $codigoIndicador, $gestion, $pei);
        });
    }

    /**
     * Elimina una programacion
     * @return array
     */
    public function actionEliminar(): array
    {
        return $this->withTryCatch(function () {
            $idProgramacion = $this->obtenerIdProgramacion();

            return $this->service->quitarProgramacion($idProgramacion);
        });
    }

    /**
     * Accion para registrar nu nuevo valor de meta.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     * @noinspection PhpUnused
     */
    public function actionGuardarMeta(): array
    {
        return $this->withTryCatch(function () {

            $idProgramacion = $this->obtenerIdProgramacion();
            $meta = $this->obtenerMeta();

            return $this->service->guardarMeta($idProgramacion, $meta);
        });
    }

    /**
     * Accion para calcular la meta global de un indicador
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     * @noinspection PhpUnused
     */
    public function actionCalcularMeta(): array
    {
        return $this->withTryCatch(function () {
            $idIndicadorEstrategico = $this->obtenerIdIndicador();
            return $this->service->calcularMeta($idIndicadorEstrategico);
        });
    }

    /**
     * Accion para calcular la meta global de un indicador en una gestion
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     * @noinspection PhpUnused
     */
    public function actionCalcularMetaGestion(): array
    {
        return $this->withTryCatch(function () {
            $pei = Yii::$app->contexto->getPei();
            $idIndicadorEstrategico = $this->obtenerIdIndicador();
            $gestion = $this->obtenerGestion();

            return $this->service->calcularMetaGestion($idIndicadorEstrategico, $gestion, $pei);
        });
    }

    /**
     * Obtiene y válida si se recibio el codigo por el request
     *
     * return string
     * @throws ValidationException
     */
    private function obtenerIdObjEstrategico(): string
    {
        $id = Yii::$app->request->post('idObjEstrategico');
        if (!$id) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'Codigo de objetivo no enviado.', 404);
        }
        return $id;
    }

    /**
     * Obtiene y valida el codigo del indicador enviado por el request.
     *
     * @return string
     * @throws ValidationException
     */
    private function obtenerIdIndicador(): string
    {
        $id = Yii::$app->request->post('idIndicadorEstrategico');
        if (!$id) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'Id de indicador no enviado.', 400);
        }
        return (string)$id;
    }

    /**
     * Obtiene y valida el idLlave enviado por el request.
     *
     * @return string
     * @throws ValidationException
     */
    private function obtenerIdLlave(): string
    {
        $id = Yii::$app->request->post('idLlavePresupuestaria');
        if (!$id) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'Id de indicador no enviado.', 400);
        }
        return (string)$id;
    }

    /**
     * Obtiene y valida el idProgramacion enviado por el request.
     *
     * @return string
     * @throws ValidationException
     */
    private function obtenerIdProgramacion(): string
    {
        $id = Yii::$app->request->post('idProgramacion');
        if (!$id) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'Id de prrgramacion no enviado.', 400);
        }
        return (string)$id;
    }

    /**
     * Obtiene y valida el idProgramacion enviado por el request.
     *
     * @return string
     * @throws ValidationException
     */
    private function obtenerMeta(): string
    {
        $id = Yii::$app->request->post('meta');
        if (!$id) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'La meta no fue enviada.', 400);
        }
        return (string)$id;
    }

    /**
     * Obtiene y valida el codigo del indicador enviado por el request.
     *
     * @return int
     * @throws ValidationException
     */
    private function obtenerCodigoIndicador(): int
    {
        $codigo = Yii::$app->request->post('codigoIndicador');
        if (!$codigo || !filter_var($codigo, FILTER_VALIDATE_INT)) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'Codigo de indicador no enviado.', 400);
        }
        return (int)$codigo;
    }

    /**
     * Obtiene y valida la gestion enviada por el request.
     *
     * @return int
     * @throws ValidationException
     */
    private function obtenerGestion(): int
    {
        $gestion = Yii::$app->request->post('gestion');
        if (!$gestion || !filter_var($gestion, FILTER_VALIDATE_INT)) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'Gestion no enviada.', 400);
        }
        return (int)$gestion;
    }
}