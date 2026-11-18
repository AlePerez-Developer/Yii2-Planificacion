<?php

namespace app\modules\Planificacion\controllers;

use app\controllers\BaseController;
use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\formModels\ObjetivoEspecificoForm;
use app\modules\Planificacion\services\ObjetivoEspecificoService;
use app\modules\Planificacion\common\traits\ControlaEdicionPoa;
use Mpdf\Mpdf;
use Mpdf\MpdfException;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Request;

class ObjEspecificoController extends BaseController
{
    use ControlaEdicionPoa;

    protected array $accionesSinValidacion = ['index', 'reporte'];

    public function __construct(
        $id,
        $module,
        private ObjetivoEspecificoService $service,
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
                    'actions' => [
                        'index', 'listar-todo', 'listar-obj-especificos-s2',
                        'guardar', 'actualizar', 'buscar', 'eliminar',
                        'cambiar-estado', 'verificar-codigo', 'reporte',
                    ],
                    'allow' => true,
                    'roles' => ['@'],
                ]],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'listar-todo' => ['POST'],
                    'listar-obj-especificos-s2' => ['POST'],
                    'guardar' => ['POST'],
                    'actualizar' => ['POST'],
                    'buscar' => ['POST'],
                    'eliminar' => ['POST'],
                    'cambiar-estado' => ['POST'],
                    'verificar-codigo' => ['POST'],
                    'reporte' => ['GET'],
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
        return $this->withTryCatch(fn() => $this->service->listarTodo());
    }

    public function actionListarObjEspecificosS2(): array
    {
        $request = Yii::$app->request;
        $q = $this->getSearchParam($request);
        return $this->withTryCatch(fn() => $this->service->listarObjEspecificosS2($q));
    }

    public function actionGuardar(): array
    {
        return $this->withTryCatch(function () {
            [$idDa, $idGestion] = $this->obtenerContextoActivo();
            return $this->service->guardar($this->cargarFormulario(), $idDa, $idGestion);
        });
    }

    public function actionActualizar(): array
    {
        return $this->withTryCatch(function () {
            [$idDa, $idGestion] = $this->obtenerContextoActivo();
            return $this->service->actualizar(
                $this->obtenerId(),
                $this->cargarFormulario(),
                $idDa,
                $idGestion
            );
        });
    }

    public function actionBuscar(): array
    {
        [$idDa, $idGestion] = $this->obtenerContextoActivo();
        return $this->withTryCatch(
            fn() => $this->service->obtenerModelo(
                $this->obtenerId(),
                $idDa,
                $idGestion
            )
        );
    }

    public function actionCambiarEstado(): array
    {
        [$idDa, $idGestion] = $this->obtenerContextoActivo();
        $codigoEstado = Yii::$app->request->post('codigoEstado');
        return $this->withTryCatch(
            fn() => $this->service->cambiarEstado(
                $this->obtenerId(),
                $idDa,
                $idGestion,
                $codigoEstado ?: null
            )
        );
    }

    public function actionEliminar(): array
    {
        [$idDa, $idGestion] = $this->obtenerContextoActivo();
        return $this->withTryCatch(
            fn() => $this->service->eliminar(
                $this->obtenerId(),
                $idDa,
                $idGestion
            )
        );
    }

    public function actionVerificarCodigo(): bool
    {
        [$idDa, $idGestion] = $this->obtenerContextoActivo();
        $request = Yii::$app->request;

        return $this->service->verificarCodigo(
            (string)$request->post('idObjEspecifico', '00000000-0000-0000-0000-000000000000'),
            (string)$request->post('idObjInstitucional', ''),
            $idDa,
            $idGestion,
            (string)$request->post('codigo', '')
        );
    }

    /**
     * Genera el PDF del listado de objetivos específicos.
     *
     * @throws MpdfException
     * @noinspection PhpUnused
     */
    public function actionReporte(): void
    {
        $this->obtenerContextoActivo();

        $listado = $this->service->listarTodo();
        $registros = $listado['data'] ?? [];

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
        $mpdf->Output('Objetivos-Especificos.pdf', 'I');
    }

    private function cargarFormulario(): ObjetivoEspecificoForm
    {
        $form = new ObjetivoEspecificoForm();

        if (!$form->load(Yii::$app->request->post(), '') || !$form->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], $form->getErrors(), 400);
        }

        return $form;
    }

    private function obtenerId(): string
    {
        $id = (string)Yii::$app->request->post('idObjEspecifico', '');

        if ($id === '') {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'No se recibió el identificador.', 400);
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
                'Debe seleccionar una gestión y una unidad ejecutora en el contexto activo.',
                400
            );
        }

        $idDa = $this->service->obtenerIdDaDesdeUnidad($idUnidadEjecutora);

        return [$idDa, $idGestion];
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
