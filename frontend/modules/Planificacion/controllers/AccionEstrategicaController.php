<?php

namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\services\AccionEstrategicaService;
use app\modules\Planificacion\formModels\AccionEstrategicaForm;
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
class AccionEstrategicaController extends BaseController
{
    protected array $accionesSinValidacion = ['index', 'reporte'];

    private AccionEstrategicaService $service;

    public function __construct($id, $module, AccionEstrategicaService $service, $config = [])
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
                    'listar-todo' => ['get', 'post'],
                    'listar-todo-s2' => ['post'],
                    'guardar' => ['post'],
                    'actualizar' => ['post'],
                    'cambiar-estado' => ['post'],
                    'eliminar' => ['post'],
                    'buscar' => ['post'],
                    'reporte' => ['get'],
                ],
            ],
        ];
    }

    /**
     * @throws BadRequestHttpException
     */
    public function beforeAction($action): bool
    {
        if ($action->id === 'listar-todo' || $action->id === 'listar-todo-s2') {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    public function actionIndex(): string
    {
        return $this->render('index');
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
        $mpdf->Output('AccionesEstrategicas.pdf', 'I');
    }

    /**
     * @noinspection PhpUnused
     */
    public function actionListarTodo(): array
    {
        return $this->withTryCatch(fn() => $this->service->listarTodo());
    }

    /**
     * @noinspection PhpUnused
     */
    public function actionListarTodoS2(): array
    {
        $search = '%' . str_replace(' ', '%', $_POST['q'] ?? '') . '%';
        return $this->withTryCatch(fn() => $this->service->listarAccionesS2($search));
    }

    /**
     * @noinspection PhpUnused
     */
    public function actionGuardar(): array
    {
        return $this->withTryCatch(function () {
            $request = Yii::$app->request;

            $form = new AccionEstrategicaForm();
            $form->load($request->post(), '');
            if (!$form->validate()) {
                throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], $form->getErrors(), 400);
            }
            return $this->service->guardar($form);
        });
    }

    /**
     * @noinspection PhpUnused
     */
    public function actionActualizar(): array
    {
        return $this->withTryCatch(function () {
            $request = Yii::$app->request;

            $id = $this->obtenerId();
            $form = new AccionEstrategicaForm();

            $form->load($request->post(), '');
            if (!$form->validate()) {
                throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], $form->getErrors(), 400);
            }

            return $this->service->actualizar($id, $form);
        });
    }

    /**
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
     * @throws ValidationException
     */
    private function obtenerId(): string
    {
        $id = Yii::$app->request->post('idAccionEstrategica');
        if (!$id) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'Id de Acción Estratégica no enviado.', 404);
        }
        return $id;
    }

}
