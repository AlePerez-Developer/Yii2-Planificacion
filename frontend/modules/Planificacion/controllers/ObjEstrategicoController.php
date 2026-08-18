<?php
namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\services\ObjetivoEstrategicoService;
use app\modules\Planificacion\services\ReporteForm1Service;
use app\modules\Planificacion\formModels\ObjetivoEstrategicoForm;
use app\controllers\BaseController;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use Mpdf\MpdfException;
use yii\web\Request;
use Mpdf\Mpdf;
use Yii;

/**
 * @noinspection PhpUnused
 */
class ObjEstrategicoController extends BaseController
{
    protected array $accionesSinValidacion = ['index', 'reporte'];

    private ObjetivoEstrategicoService $service;
    private ReporteForm1Service $reporteService;

    public function __construct($id, $module,
                                ObjetivoEstrategicoService $service,
                                ReporteForm1Service $reporteService,
                                $config = [])
    {
        $this->service = $service;
        $this->reporteService = $reporteService;
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
                            'index','listar-todo','verificar-codigo','guardar', 'actualizar', 'eliminar','cambiar-estado','buscar',
                            'listar-areas-estrategicas','listar-politicas-estrategicas','listar-obj-estrategicos-s2','reporte'
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function () {
                            Yii::$app->contexto->validarPeiActivo();
                            return true;
                        },

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
     * @return string
     */
    public function actionIndex(): string
    {
        return $this->render('objEstrategico');
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
     * Accion para listar todos los registros del modelo para el llenado de Select2.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     * @noinspection PhpUnused
     *
     */
    public function actionListarObjEstrategicosS2(): array
    {
        $request = Yii::$app->request;

        $q = $this->getSearchParam($request);

        return $this->withTryCatch(fn() => $this->service->listarObjEstrategicosS2($q)) ;
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
            $form = new ObjetivoEstrategicoForm();

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
        return $this->withTryCatch(function() {
            $request = Yii::$app->request;

            $id = $this->obtenerId();

            $form = new ObjetivoEstrategicoForm();
            $form->idPei = yii::$app->contexto->getPei();

            if (!$form->load($request->post(), '') || !$form->validate())
            {
                throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'],$form->getErrors(),400);
            }

            return $this->service->validarActualizar($id,$form);
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
        return $this->withTryCatch(function() {
            $id = $this->obtenerId();
            return $this->service->cambiarEstado($id);
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
        return $this->withTryCatch(function() {
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
        return $this->withTryCatch(function() {
            $id = $this->obtenerId();
            return $this->service->obtenerModelo($id);
        });
    }

    /**
     * Obtiene y válida si se recibio el codigo por el request
     *
     * @return string
     * @throws ValidationException
     */
    private function obtenerId(): string
    {
        $id = Yii::$app->request->post('idObjEstrategico');
        if (!$id) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'],'Codigo de objetivo no enviado.',404);
        }
        return $id;
    }

    /**
     * Accion para verificar un codigo ingresado
     *
     * @return bool
     * @noinspection PhpUnused
     */
    public function actionVerificarCodigo(): bool
    {
        $id = Yii::$app->request->post('idObjEstrategico');
        if (!isset($id)) {
            return false;
        }

        $idPoliticaEstrategica = Yii::$app->request->post('idPoliticaEstrategica');
        if (!isset($idPoliticaEstrategica)) {
            return false;
        }

        $idAreaEstrategica = Yii::$app->request->post('idAreaEstrategica');
        if (!isset($idAreaEstrategica)) {
            return false;
        }

        $codigo = Yii::$app->request->post('codigo');
        if (!isset($codigo)) {
            return false;
        }

        return $this->service->verificarCodigo($id, $idAreaEstrategica, $idPoliticaEstrategica, $codigo);
    }

    /**
     * @throws MpdfException
     * @noinspection PhpUnused
     */
    public function actionReporte(): void
    {
        [$idGestion, $idUnidad, $idEstadoPoa] = $this->obtenerContextoReporte();
        $reporte = $this->reporteService->listarParaReporte($idGestion, $idUnidad, $idEstadoPoa);

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
            'format' => 'Letter-L',
            'margin_top' => 32,
            'margin_bottom' => 18,
            'margin_left' => 8,
            'margin_right' => 8,
        ]);
        $mpdf->SetMargins(8, 8, 32);
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
        $mpdf->WriteHTML($this->renderPartial('reporte', $reporte));
        $mpdf->Output('Formulario-1-OGI.pdf', 'I');
    }

    /**
     * @return array{0: string, 1: string, 2: string}
     * @throws ValidationException
     */
    private function obtenerContextoReporte(): array
    {
        $contexto = Yii::$app->userContext->contexto();
        $idGestion = (string)($contexto?->IdGestion ?? '');
        $idUnidad = (string)($contexto?->IdUnidadEjecutora ?? '');
        $idEstadoPoa = (string)($contexto?->IdEstadoPoa ?? '');

        if ($idGestion === '' || $idUnidad === '' || $idEstadoPoa === '') {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'Debe seleccionar gestión, unidad ejecutora y estado POA.',
                400
            );
        }

        return [$idGestion, $idUnidad, $idEstadoPoa];
    }

    /**
     * Obtiene el parámetro de búsqueda de Select2
     * @param Request $request
     * @return string
     */
    private function getSearchParam(Request $request): string
    {
        $id = $request->post('q');

        if (!$id) {
            return '';
        }

        return $id;
    }


}
