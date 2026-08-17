<?php

namespace app\modules\Planificacion\controllers;

use app\controllers\BaseController;
use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\formModels\ItemCatalogadoForm;
use app\modules\Planificacion\models\EstadoPoa;
use app\modules\Planificacion\services\ItemCatalogadoService;
use common\models\Estado;
use common\models\seguridad\EstadosPoa as EstadoPoaSeguridad;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class ItemCatalogadoController extends BaseController
{
    public function __construct($id, $module, private ItemCatalogadoService $service, $config = [])
    {
        parent::__construct($id, $module, $config);
    }

    public function behaviors(): array
    {
        return [
            'access' => ['class' => AccessControl::class, 'rules' => [['allow' => true, 'roles' => ['@']]]],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'listar-operaciones' => ['POST'],
                    'listar-items' => ['POST'],
                    'listar-sigma' => ['POST'],
                    'listar-fuentes' => ['POST'],
                    'listar-organismos' => ['POST'],
                    'guardar' => ['POST'],
                    'eliminar' => ['POST'],
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

    public function actionListarOperaciones(): array
    {
        [$unidad, $gestion, $estado, $codigoEstado] = $this->obtenerContexto();
        return $this->withTryCatch(fn() => $this->service->listarOperaciones(
            $unidad,
            $gestion,
            $estado,
            $codigoEstado,
            $this->postFormulario()
        ));
    }

    public function actionListarItems(): array
    {
        [$unidad, $gestion, $estado, $codigoEstado] = $this->obtenerContexto();
        return $this->withTryCatch(fn() => $this->service->listarItems(
            $this->postString('idOperacion'),
            $this->postFormulario(),
            $unidad,
            $gestion,
            $estado,
            $codigoEstado
        ));
    }

    public function actionListarSigma(): array
    {
        return $this->withTryCatch(fn() => $this->service->listarSigma());
    }

    public function actionListarFuentes(): array
    {
        return $this->withTryCatch(fn() => $this->service->listarFuentes());
    }

    public function actionListarOrganismos(): array
    {
        return $this->withTryCatch(fn() => $this->service->listarOrganismos(
            $this->postString('idFuente')
        ));
    }

    public function actionGuardar(): array
    {
        [$unidad, $gestion, $estado, $codigoEstado] = $this->obtenerContexto();
        return $this->withTryCatch(fn() => $this->service->guardar(
            $this->cargarFormulario(),
            ($id = $this->postString('idItemCatalogado', false)) !== '' ? $id : null,
            $unidad,
            $gestion,
            $estado,
            $codigoEstado
        ));
    }

    public function actionEliminar(): array
    {
        [, $gestion, $estado] = $this->obtenerContexto();
        return $this->withTryCatch(fn() => $this->service->eliminar(
            $this->postString('idItemCatalogado'),
            $this->postString('idOperacion'),
            $this->postFormulario(),
            $gestion,
            $estado
        ));
    }

    private function cargarFormulario(): ItemCatalogadoForm
    {
        $form = new ItemCatalogadoForm();
        if (!$form->load(Yii::$app->request->post(), '') || !$form->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], $form->errors, 400);
        }
        return $form;
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
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'Debe seleccionar el contexto activo completo.', 400);
        }
        return [$unidad, $gestion, $estado, $this->resolverCodigoEstadoPoa($estado)];
    }

    private function resolverCodigoEstadoPoa(string $idEstadoPoa): int
    {
        $seguridad = EstadoPoaSeguridad::findOne($idEstadoPoa);
        if ($seguridad === null) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'Estado POA inválido.', 400);
        }
        $codigo = trim((string)$seguridad->Codigo);
        $estado = ctype_digit($codigo)
            ? EstadoPoa::findOne(['CodigoEstadoPOA' => (int)$codigo])
            : EstadoPoa::find()
                ->where(['CodigoEstado' => Estado::ESTADO_VIGENTE])
                ->andWhere(['or', ['Descripcion' => $seguridad->Descripcion], ['Abreviacion' => $codigo]])
                ->one();
        if ($estado === null) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'No existe equivalencia del estado POA.', 400);
        }
        return (int)$estado->CodigoEstadoPOA;
    }
}
