<?php

namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\services\ObjetivoInstitucionalService;
use app\modules\Planificacion\formModels\ObjetivoInstitucionalForm;
use yii\web\BadRequestHttpException;
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
class ObjInstitucionalController extends BaseController
{
    protected array $accionesSinValidacion = ['index', 'reporte'];

    private ObjetivoInstitucionalService $service;

    public function __construct(
        $id,
        $module,
        ObjetivoInstitucionalService $service,
        $config = []
    )
    {
        $this->service = $service;
        parent::__construct($id, $module, $config);
    }

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => [
                            'index', 'listar-todo', 'listar-obj-institucionales-s2', 'verificar-codigo', 'guardar',
                            'actualizar', 'eliminar', 'cambiar-estado', 'buscar', 'reporte',
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => static function (): bool {
                            Yii::$app->contexto->validarPeiActivo();
                            return true;
                        },
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'listar-todo' => ['POST'],
                    'verificar-codigo' => ['POST'],
                    'guardar' => ['POST'],
                    'actualizar' => ['POST'],
                    'eliminar' => ['POST'],
                    'cambiar-estado' => ['POST'],
                    'buscar' => ['POST'],
                    'reporte' => ['GET'],
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
        return $this->render('ObjInstitucionales');
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
     */
    public function actionListarObjInstitucionalesS2(): array
    {
        $request = Yii::$app->request;

        $q = $this->getSearchParam($request);

        return $this->withTryCatch(fn() => $this->service->listarObjInstitucionalesS2($q));
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
            $form = new ObjetivoInstitucionalForm();

            $contexto = Yii::$app->userContext->contexto();
            $form->idGestion = $contexto?->IdGestion;

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

            $form = new ObjetivoInstitucionalForm();

            $contexto = Yii::$app->userContext->contexto();
            $form->idGestion = $contexto?->IdGestion;

            if (!$form->load($request->post(), '') || !$form->validate()) {
                throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], $form->getErrors(), 400);
            }

            return $this->service->validarActualizar($id, $form);
        });
    }

    /**
     * Accion para alternar / asignar el estado de un registro V/C.
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
        $id = Yii::$app->request->post('idObjInstitucional');
        if (!$id) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'Codigo de objetivo no enviado.', 404);
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
        $id = Yii::$app->request->post('idObjInstitucional');
        if (!isset($id)) {
            return false;
        }

        $idObjEstrategico = Yii::$app->request->post('idObjEstrategico');
        if (!isset($idObjEstrategico)) {
            return false;
        }

        $codigo = Yii::$app->request->post('codigo');
        if (!isset($codigo)) {
            return false;
        }

        return $this->service->verificarCodigo($id, $idObjEstrategico, $codigo);
    }

    /**
     * Genera el PDF del listado de objetivos institucionales.
     *
     * @throws MpdfException
     * @noinspection PhpUnused
     */
    public function actionReporte(): void
    {
        $registros = [];
        if (Yii::$app->contexto->getPei() !== '') {
            $listado = $this->service->listarTodo();
            $registros = $listado['data'] ?? [];
        }

        $usuario = Yii::$app->user->identity;
        $persona = $usuario?->persona ?? null;
        $nombreUsuario = trim(implode(' ', array_filter([
            $persona->Nombres ?? '',
            $persona->Paterno ?? '',
            $persona->Materno ?? '',
        ])));
        if ($nombreUsuario === '') {
            $nombreUsuario = (string)($usuario?->CodigoUsuario ?? '');
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
            . ' (' . htmlspecialchars((string)($usuario?->CodigoUsuario ?? ''), ENT_QUOTES, 'UTF-8') . ')</td>'
            . '<td width="20%" align="center">Página {PAGENO} de {nbpg}</td>'
            . '<td width="40%" align="right">Fecha y hora de impresión: '
            . htmlspecialchars(date('d/m/Y H:i:s'), ENT_QUOTES, 'UTF-8') . '</td>'
            . '</tr></table>'
        );
        $mpdf->WriteHTML($this->renderPartial('reporte', [
            'registros' => $registros,
        ]));
        $mpdf->Output('Objetivos-Institucionales.pdf', 'I');
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
