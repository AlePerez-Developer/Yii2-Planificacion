<?php

namespace app\modules\Planificacion\controllers;

use app\controllers\BaseController;
use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\formModels\IndicadorPoaForm;
use app\modules\Planificacion\models\ObjetivoEspecifico;
use app\modules\Planificacion\services\IndicadorPoaService;
use Yii;
use yii\filters\VerbFilter;

class IndicadorPoaController extends BaseController
{
    public function __construct($id, $module, private IndicadorPoaService $service, $config = [])
    {
        parent::__construct($id, $module, $config);
    }

    public function behaviors(): array
    {
        return [
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
        [$idLlave, $gestion] = $this->obtenerContextoActivo();
        return $this->withTryCatch(fn() => $this->service->listarTodo($idLlave, $gestion));
    }

    public function actionListarObjetivosEspecificosS2(): array
    {
        [$idLlave, $gestion] = $this->obtenerContextoActivo();

        $data = ObjetivoEspecifico::listAll($idLlave, $gestion)
            ->select([
                'id' => 'OE.IdObjEspecifico',
                'text' => 'OE.Objetivo',
                'compuesto' => 'Compuesto',
                'producto' => 'OE.Producto',
            ])
            ->orderBy(['Compuesto' => SORT_ASC])
            ->asArray()
            ->all();

        return ['data' => $data];
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
        $request = Yii::$app->request;

        return $this->service->verificarCodigo(
            (string)$request->post('idIndicadorPoa', '00000000-0000-0000-0000-000000000000'),
            (string)$request->post('idObjEspecifico', ''),
            (int)$request->post('codigo', 0)
        );
    }

    private function cargarFormulario(): IndicadorPoaForm
    {
        $form = new IndicadorPoaForm();

        if (!$form->load(Yii::$app->request->post(), '') || !$form->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], $form->getErrors(), 400);
        }

        return $form;
    }

    private function obtenerId(): string
    {
        $id = (string)Yii::$app->request->post('idIndicadorPoa', '');
        if ($id === '') {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'No se recibió el identificador.', 400);
        }
        return $id;
    }

    private function obtenerContextoActivo(): array
    {
        $contexto = Yii::$app->userContext->contexto();
        $idLlave = (string)($contexto->IdLlavePresupuestaria ?? '');
        $gestion = (int)($contexto->Gestion ?? 0);

        if ($idLlave === '' || $gestion <= 0) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'Debe seleccionar gestión y llave presupuestaria.', 400);
        }

        return [$idLlave, $gestion];
    }
}
