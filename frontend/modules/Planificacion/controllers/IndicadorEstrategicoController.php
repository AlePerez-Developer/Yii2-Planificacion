<?php
namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\services\IndicadorEstrategicoService;
use app\modules\Planificacion\formModels\IndicadorEstrategicoForm;
use yii\web\BadRequestHttpException;
use app\controllers\BaseController;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use Mpdf\MpdfException;
use Mpdf\Mpdf;
use Yii;

/**
 * @noinspection PhpUnused
 */
class IndicadorEstrategicoController extends BaseController
{
    protected array $accionesSinValidacion = ['index', 'reporte'];

    private IndicadorEstrategicoService $service;

    public function __construct($id, $module,
                                IndicadorEstrategicoService $service,
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

    /**
     * @throws BadRequestHttpException
     */
    public function beforeAction($action): bool
    {
        if ($action->id == 'listar-todo') {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    public function actionIndex(): string
    {
        return $this->render('indicadorEstrategico');
    }

    /**
     * Accion para listar todos los registros del modelo.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     * @noinspection PhpUnused
     */
    public function actionListarTodo(): array
    {
        return $this->withTryCatch(fn() => $this->service->listarTodo());
    }

    /**
     * Accion para agregar un nuevo registro.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     * @noinspection PhpUnused
     */
    public function actionGuardar(): array
    {
        return $this->withTryCatch(function () {
            $request = Yii::$app->request;

            $form = new IndicadorEstrategicoForm();
            $form->idPei = yii::$app->contexto->getPei();
            if (!$form->load($request->post(), '') || !$form->validate()) {
                throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], $form->getErrors(), 400);
            }

            return $this->service->validarGuardar($form);
        });
    }

    /**
     * Accion para actualizar los valores de un registro existente.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     * @noinspection PhpUnused
     */
    public function actionActualizar(): array
    {
        return $this->withTryCatch(function () {
            $request = Yii::$app->request;

            $id = $this->obtenerId();
            $form = new IndicadorEstrategicoForm();
            $form->idPei = yii::$app->contexto->getPei();
            if (!$form->load($request->post(), '') || !$form->validate()) {
                throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], $form->getErrors(), 400);
            }

            return $this->service->validarActualizar($id, $form);
        });
    }

    /**
     * Accion para alternar el estado de un registro V/C.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     * @noinspection PhpUnused
     */
    public function actionCambiarEstado(): array
    {
        return $this->withTryCatch(function () {
            $id = $this->obtenerId();
            $codigoEstado = Yii::$app->request->post('codigoEstado');
            return $this->service->cambiarEstado($id, $codigoEstado ?: null);
        });
    }

    /**
     * Accion para soft delete de un registro
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     * @noinspection PhpUnused
     */
    public function actionEliminar(): array
    {
        return $this->withTryCatch(function () {
            $id = $this->obtenerId();
            return $this->service->eliminar($id);
        });
    }

    /**
     * Accion para buscar un registro en específico
     *
     * @return array
     * @noinspection PhpUnused
     */
    public function actionBuscar(): array
    {
        return $this->withTryCatch(function () {
            $id = $this->obtenerId();
            return $this->service->obtenerModeloArray($id);
        });
    }

    /**
     * Obtiene y válida si se recibio él, id por el request
     *
     * @return string
     * @throws ValidationException
     */
    private function obtenerId(): string
    {
        $id = Yii::$app->request->post('idIndicadorEstrategico');
        if (!$id) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'Id de indicador estrategico no enviado.', 404);
        }
        return $id;
    }

    /**
     * Accion para buscar un registro en específico
     *
     * @return bool
     * @noinspection PhpUnused
     */
    public function actionVerificarCodigo(): bool
    {
        $id = Yii::$app->request->post('idIndicadorEstrategico');
        if (!isset($id)) {
            return false;
        }

        $codigo = Yii::$app->request->post('codigo');
        if (!isset($codigo)) {
            return false;
        }

        return $this->service->verificarCodigo($id,$codigo);
    }

    /**
     * @throws MpdfException
     * @noinspection PhpUnused
     */
    public function actionReporte(): void
    {
        $listado = $this->service->listarTodo();
        $registros = $listado['data'] ?? [];

        $usuario = Yii::$app->user->identity;
        $persona = $usuario->persona ?? null;
        $nombreUsuario = trim(implode(' ', array_filter([
            $persona->Nombres ?? '',
            $persona->Paterno ?? '',
            $persona->Materno ?? '',
        ])));
        if ($nombreUsuario === '') {
            $nombreUsuario = (string)($usuario->CodigoUsuario ?? '');
        }

        $mpdf = new Mpdf([
            'format' => 'Letter',
            'margin_top' => 32,
            'margin_bottom' => 18,
            'margin_left' => 12,
            'margin_right' => 12,
        ]);
        $mpdf->SetMargins(12, 12, 32);
        $mpdf->SetHTMLHeader('<div class="reporte-header"></div>');
        $mpdf->SetHTMLFooter(
            '<table class="reporte-footer" width="100%">'
            . '<tr>'
            . '<td width="40%">Usuario: ' . htmlspecialchars($nombreUsuario, ENT_QUOTES, 'UTF-8')
            . ' (' . htmlspecialchars((string)($usuario->CodigoUsuario ?? ''), ENT_QUOTES, 'UTF-8') . ')</td>'
            . '<td width="20%" align="center">Página {PAGENO} de {nbpg}</td>'
            . '<td width="40%" align="right">Fecha y hora de impresión: '
            . htmlspecialchars(date('d/m/Y H:i:s'), ENT_QUOTES, 'UTF-8') . '</td>'
            . '</tr></table>'
        );
        $mpdf->WriteHTML($this->renderPartial('reporte', [
            'registros' => $registros,
        ]));
        $mpdf->Output('IndicadoresEstrategicos.pdf', 'I');
    }
}