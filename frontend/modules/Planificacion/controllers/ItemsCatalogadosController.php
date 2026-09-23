<?php

namespace app\modules\Planificacion\controllers;

use app\controllers\BaseController;
use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\formModels\ItemCatalogadoForm;
use app\modules\Planificacion\services\ItemsCatalogadosService;
use app\modules\Planificacion\common\traits\ControlaEdicionPoa;
use Mpdf\Mpdf;
use Mpdf\MpdfException;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class ItemsCatalogadosController extends BaseController
{
    use ControlaEdicionPoa;
    protected array $accionesSinValidacion = ['index', 'reporte'];

    public function __construct(
        $id,
        $module,
        private ItemsCatalogadosService $service,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'listar-todo' => ['POST'],
                    'listar-sigma' => ['POST'],
                    'listar-operaciones' => ['POST'],
                    'listar-organismos-s2' => ['POST'],
                    'listar-fuentes-universitarias-s2' => ['POST'],
                    'listar-unidades-medida-s2' => ['POST'],
                    'guardar' => ['POST'],
                    'actualizar' => ['POST'],
                    'buscar' => ['POST'],
                    'eliminar' => ['POST'],
                    'reporte' => ['GET'],
                ],
            ],
        ];
    }

    public function actionIndex(int $formulario): string
    {
        $this->validarFormulario($formulario);
        $this->obtenerContexto();
        return $this->render('index', ['formulario' => $formulario]);
    }

    public function actionListarTodo(): array
    {
        [$unidad, $gestion, $estado] = $this->obtenerContexto();
        return $this->withTryCatch(
            fn() => $this->service->listarTodo($this->postFormulario(), $unidad, $gestion, $estado)
        );
    }

    public function actionListarSigma(): array
    {
        $this->obtenerContexto();
        return $this->withTryCatch(fn() => $this->service->listarSigma($this->postFormulario()));
    }

    public function actionListarOperaciones(): array
    {
        [$unidad, $gestion, $estado] = $this->obtenerContexto();
        return $this->withTryCatch(fn() => $this->service->listarOperaciones($unidad, $gestion, $estado));
    }

    public function actionListarOrganismosS2(): array
    {
        $this->obtenerContexto();
        return $this->withTryCatch(
            fn() => $this->service->listarOrganismos($this->postString('idFuente'))
        );
    }

    public function actionListarFuentesUniversitariasS2(): array
    {
        $this->obtenerContexto();
        return $this->withTryCatch(
            fn() => $this->service->listarFuentesUniversitarias(
                $this->postString('idFuente'),
                $this->postString('idOrganismo')
            )
        );
    }

    public function actionListarUnidadesMedidaS2(): array
    {
        $this->obtenerContexto();
        return $this->withTryCatch(fn() => $this->service->listarUnidadesMedida());
    }

    public function actionGuardar(): array
    {
        return $this->withTryCatch(function () {
            [$unidad, $gestion, $estado] = $this->obtenerContexto();
            return $this->service->guardar($this->cargarFormulario(), $unidad, $gestion, $estado);
        });
    }

    public function actionActualizar(): array
    {
        return $this->withTryCatch(function () {
            [$unidad, $gestion, $estado] = $this->obtenerContexto();
            return $this->service->actualizar(
                $this->obtenerId(),
                $this->cargarFormulario(),
                $unidad,
                $gestion,
                $estado
            );
        });
    }

    public function actionBuscar(): array
    {
        [$unidad, $gestion, $estado] = $this->obtenerContexto();
        return $this->withTryCatch(
            fn() => $this->service->obtenerModelo(
                $this->obtenerId(),
                $this->postFormulario(),
                $unidad,
                $gestion,
                $estado
            )
        );
    }

    public function actionEliminar(): array
    {
        [, $gestion] = $this->obtenerContexto();
        return $this->withTryCatch(
            fn() => $this->service->eliminar($this->obtenerId(), $this->postFormulario(), $gestion)
        );
    }

    /**
     * @throws MpdfException
     */
    public function actionReporte(): void
    {
        $formulario = (int)Yii::$app->request->get('formulario', 0);
        $this->validarFormulario($formulario);
        [$unidad, $gestion, $estado] = $this->obtenerContexto();
        $listado = $this->service->listarTodo($formulario, $unidad, $gestion, $estado);
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
        $mpdf->SetHTMLHeader('<div class="reporte-header"></div>');
        $mpdf->SetHTMLFooter(
            '<table class="reporte-footer" width="100%">'
            . '<tr>'
            . '<td width="40%">Usuario: ' . htmlspecialchars($nombreUsuario, ENT_QUOTES, 'UTF-8') . '</td>'
            . '<td width="30%" align="center">{DATE d/m/Y H:i}</td>'
            . '<td width="30%" align="right">Página {PAGENO}/{nbpg}</td>'
            . '</tr></table>'
        );
        $mpdf->WriteHTML($this->renderPartial('reporte', [
            'formulario' => $formulario,
            'items' => $listado['data']['items'] ?? [],
            'totalFormulario' => $listado['data']['totalFormulario'] ?? 0,
        ]));
        $mpdf->Output("Formulario-{$formulario}-items-catalogados.pdf", 'I');
    }

    private function cargarFormulario(): ItemCatalogadoForm
    {
        $form = new ItemCatalogadoForm();
        $post = Yii::$app->request->post();
        if (isset($post['asignaciones']) && is_string($post['asignaciones'])) {
            $decoded = json_decode($post['asignaciones'], true);
            $post['asignaciones'] = is_array($decoded) ? $decoded : [];
        }
        if (!$form->load($post, '') || !$form->validate()) {
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
        return $this->postString('idItem');
    }

    private function postFormulario(): int
    {
        $formulario = filter_var(Yii::$app->request->post('formulario'), FILTER_VALIDATE_INT);
        $this->validarFormulario((int)$formulario);
        return (int)$formulario;
    }

    private function validarFormulario(int $formulario): void
    {
        if ($formulario < 7 || $formulario > 9) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'Formulario no válido.', 400);
        }
    }

    private function postString(string $campo, bool $requerido = true): string
    {
        $valor = trim((string)Yii::$app->request->post($campo, ''));
        if ($requerido && $valor === '') {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], "Falta {$campo}.", 400);
        }
        return $valor;
    }

    private function obtenerContexto(): array
    {
        $contexto = Yii::$app->userContext->contexto();
        $unidad = (string)($contexto?->IdUnidadEjecutora ?? '');
        $gestion = (string)($contexto?->IdGestion ?? '');
        $estado = (string)($contexto?->IdEstadoPoa ?? '');
        if ($unidad === '' || $gestion === '' || $estado === '') {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'Debe seleccionar el contexto activo completo.',
                400
            );
        }
        return [$unidad, $gestion, $estado];
    }
}
