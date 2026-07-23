<?php

namespace app\modules\Planificacion\controllers;

use app\controllers\BaseController;
use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\formModels\ObjetivoEspecificoForm;
use app\modules\Planificacion\models\ObjetivoInstitucional;
use app\modules\Planificacion\services\ObjetivoEspecificoService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Request;

class ObjEspecificoController extends BaseController
{
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
                        'cambiar-estado', 'verificar-codigo',
                    ],
                    'allow' => true,
                    'roles' => ['@'],
                ]],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'listar-todo' => ['POST'],
                    'listar-objetivos-especificos-s2' => ['POST'],
                    'guardar' => ['POST'],
                    'actualizar' => ['POST'],
                    'buscar' => ['POST'],
                    'eliminar' => ['POST'],
                    'cambiar-estado' => ['POST'],
                    'verificar-codigo' => ['POST'],
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
        return $this->withTryCatch(fn() => $this->service->listarObjEspecificosS2($q)) ;
    }

    public function actionGuardar(): array
    {
        return $this->withTryCatch(function () {
            [$idLlave, $gestion] = $this->obtenerContextoActivo();
            return $this->service->guardar($this->cargarFormulario(), $idLlave, $gestion);
        });
    }

    public function actionActualizar(): array
    {
        return $this->withTryCatch(function () {
            [$idLlave, $gestion] = $this->obtenerContextoActivo();
            return $this->service->actualizar($this->obtenerId(), $this->cargarFormulario(), $idLlave, $gestion);
        });
    }

    public function actionBuscar(): array
    {
        [$idLlave, $gestion] = $this->obtenerContextoActivo();
        return $this->withTryCatch(fn() => $this->service->obtenerModelo($this->obtenerId(), $idLlave, $gestion));
    }

    public function actionCambiarEstado(): array
    {
        [$idLlave, $gestion] = $this->obtenerContextoActivo();
        return $this->withTryCatch(fn() => $this->service->cambiarEstado($this->obtenerId(), $idLlave, $gestion));
    }

    public function actionEliminar(): array
    {
        [$idLlave, $gestion] = $this->obtenerContextoActivo();
        return $this->withTryCatch(fn() => $this->service->eliminar($this->obtenerId(), $idLlave, $gestion));
    }

    public function actionVerificarCodigo(): bool
    {
        [$idLlave, $gestion] = $this->obtenerContextoActivo();
        $request = Yii::$app->request;

        return $this->service->verificarCodigo(
            (string)$request->post('idObjEspecifico', '00000000-0000-0000-0000-000000000000'),
            (string)$request->post('idObjInstitucional', ''),
            $idLlave,
            $gestion,
            (string)$request->post('codigo', '')
        );
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
        $idLlave = (string)($contexto->IdLlavePresupuestaria ?? '');
        $gestion = (string)($contexto->IdGestion ?? 0);

        if ($idLlave === '' || $gestion <= 0) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'Debe seleccionar una gestión y una llave presupuestaria en el contexto activo.',
                400
            );
        }

        return [$idLlave, $gestion];
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
