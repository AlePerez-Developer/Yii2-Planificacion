<?php

namespace app\modules\Planificacion\controllers;

use app\controllers\BaseController;
use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\formModels\ObjetivoInstitucionalForm;
use app\modules\Planificacion\services\ObjetivoEstrategicoService;
use app\modules\Planificacion\services\ObjetivoInstitucionalService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class ObjInstitucionalController extends BaseController
{
    private ObjetivoInstitucionalService $service;
    private ObjetivoEstrategicoService $objetivoEstrategicoService;

    public function __construct(
        $id,
        $module,
        ObjetivoInstitucionalService $service,
        ObjetivoEstrategicoService $objetivoEstrategicoService,
        $config = []
    ) {
        $this->service = $service;
        $this->objetivoEstrategicoService = $objetivoEstrategicoService;
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
                            'index', 'listar-todo', 'verificar-codigo', 'guardar',
                            'actualizar', 'eliminar', 'cambiar-estado', 'buscar',
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
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        return $this->render('ObjInstitucionales');
    }

    public function actionListarTodo(): array
    {
        return $this->withTryCatch(fn() => $this->service->listarTodo());
    }

    public function actionGuardar(): array
    {
        return $this->withTryCatch(function (): array {
            $form = $this->cargarFormulario();
            return $this->service->guardar($form);
        });
    }

    public function actionActualizar(): array
    {
        return $this->withTryCatch(function (): array {
            $id = $this->obtenerId();
            $form = $this->cargarFormulario();
            return $this->service->actualizar($id, $form);
        });
    }

    public function actionCambiarEstado(): array
    {
        return $this->withTryCatch(
            fn() => $this->service->cambiarEstado($this->obtenerId())
        );
    }

    public function actionEliminar(): array
    {
        return $this->withTryCatch(
            fn() => $this->service->eliminar($this->obtenerId())
        );
    }

    public function actionBuscar(): array
    {
        return $this->withTryCatch(
            fn() => $this->service->obtenerModelo($this->obtenerId())
        );
    }

    public function actionVerificarCodigo(): bool
    {
        $request = Yii::$app->request;

        $id = (string)$request->post('idObjInstitucional', '00000000-0000-0000-0000-000000000000');
        $idObjEstrategico = (string)$request->post('idObjEstrategico', '');
        $codigo = (string)$request->post('codigo', '');

        if ($idObjEstrategico === '' || !preg_match('/^\d{2}$/', $codigo)) {
            return false;
        }

        return $this->service->verificarCodigo($id, $idObjEstrategico, $codigo);
    }

    /**
     * @throws ValidationException
     */
    private function cargarFormulario(): ObjetivoInstitucionalForm
    {
        $form = new ObjetivoInstitucionalForm();

        if (!$form->load(Yii::$app->request->post(), '') || !$form->validate()) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                $form->getErrors(),
                400
            );
        }

        if (!$this->objetivoEstrategicoService->validarId($form->idObjEstrategico)) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                ['idObjEstrategico' => ['El objetivo estratégico seleccionado no es válido.']],
                400
            );
        }

        return $form;
    }

    /**
     * @throws ValidationException
     */
    private function obtenerId(): string
    {
        $id = (string)Yii::$app->request->post('idObjInstitucional', '');

        if ($id === '') {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'No se recibió el identificador del objetivo institucional.',
                400
            );
        }

        return $id;
    }
}
