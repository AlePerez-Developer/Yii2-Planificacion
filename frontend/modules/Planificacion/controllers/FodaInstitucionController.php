<?php

namespace app\modules\Planificacion\controllers;

use app\controllers\BaseController;
use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\formModels\FodaInstitucionForm;
use app\modules\Planificacion\models\FODAInstitucion;
use app\modules\Planificacion\services\FodaInstitucionService;
use Mpdf\Mpdf;
use Mpdf\MpdfException;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class FodaInstitucionController extends BaseController
{
    protected array $accionesSinValidacion = ['index', 'reporte'];

    public function __construct(
        $id,
        $module,
        private FodaInstitucionService $service,
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
                    'guardar' => ['POST'],
                    'actualizar' => ['POST'],
                    'buscar' => ['POST'],
                    'cambiar-estado' => ['POST'],
                    'eliminar' => ['POST'],
                    'reporte' => ['GET'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $this->obtenerContexto();
        return $this->render('index');
    }

    public function actionListarTodo(): array
    {
        $idGestion = $this->obtenerContexto();
        return $this->withTryCatch(
            fn() => $this->service->listarTodo($idGestion)
        );
    }

    public function actionGuardar(): array
    {
        return $this->withTryCatch(function () {
            $idGestion = $this->obtenerContexto();
            return $this->service->guardar($this->cargarFormulario(), $idGestion);
        });
    }

    public function actionActualizar(): array
    {
        return $this->withTryCatch(function () {
            $idGestion = $this->obtenerContexto();
            return $this->service->actualizar(
                $this->obtenerId(),
                $this->cargarFormulario(),
                $idGestion
            );
        });
    }

    public function actionBuscar(): array
    {
        $idGestion = $this->obtenerContexto();
        return $this->withTryCatch(
            fn() => $this->service->obtener($this->obtenerId(), $idGestion)
        );
    }

    public function actionCambiarEstado(): array
    {
        $idGestion = $this->obtenerContexto();
        return $this->withTryCatch(
            fn() => $this->service->cambiarEstado($this->obtenerId(), $idGestion)
        );
    }

    public function actionEliminar(): array
    {
        $idGestion = $this->obtenerContexto();
        return $this->withTryCatch(
            fn() => $this->service->eliminar($this->obtenerId(), $idGestion)
        );
    }

    /**
     * @throws MpdfException
     */
    public function actionReporte(): void
    {
        $idGestion = $this->obtenerContexto();
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
        $mpdf->WriteHTML($this->renderPartial('reporte', [
            'tipos' => FODAInstitucion::tipos(),
            'registros' => $this->service->listarParaReporte($idGestion),
        ]));
        $mpdf->Output('FODA-Institucion.pdf', 'I');
    }

    private function cargarFormulario(): FodaInstitucionForm
    {
        $form = new FodaInstitucionForm();
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
        $id = (string)Yii::$app->request->post('idFoda', '');
        if ($id === '') {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'No se recibió el registro FODA.',
                400
            );
        }
        return $id;
    }

    private function obtenerContexto(): string
    {
        $contexto = Yii::$app->userContext->contexto();
        $idGestion = (string)($contexto?->IdGestion ?? '');

        if ($idGestion === '') {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'Debe seleccionar una gestión en el contexto activo.',
                400
            );
        }

        return $idGestion;
    }
}
