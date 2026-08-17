<?php

namespace app\modules\Planificacion\controllers;

use app\controllers\BaseController;
use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\formModels\OperacionDticForm;
use app\modules\Planificacion\models\IndicadorEstrategico;
use app\modules\Planificacion\models\IndicadorPoa;
use app\modules\Planificacion\models\LlavePresupuestaria;
use app\modules\Planificacion\models\ObjetivoEspecifico;
use app\modules\Planificacion\services\OperacionDticService;
use common\models\Estado;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class OperacionDticController extends BaseController
{
    public function __construct($id, $module, private OperacionDticService $service, $config = [])
    {
        parent::__construct($id, $module, $config);
    }

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [[
                    'allow' => true,
                    'roles' => ['@'],
                ]],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'listar-todo' => ['POST'],
                    'listar-objetivos-s2' => ['POST'],
                    'listar-indicadores-estrategicos-s2' => ['POST'],
                    'listar-indicadores-poa-s2' => ['POST'],
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
        [$idLlave, $idGestion, $idEstadoPoa] = $this->obtenerContextoActivo();
        return $this->withTryCatch(
            fn() => $this->service->listarTodo($idLlave, $idGestion, $idEstadoPoa)
        );
    }

    public function actionListarObjetivosS2(): array
    {
        [$idLlave, $idGestion] = $this->obtenerContextoActivo();

        $data = ObjetivoEspecifico::find()->alias('Oe')
            ->select([
                'id' => 'Oe.IdObjEspecifico',
                'text' => 'Oe.Objetivo',
                'producto' => 'Oe.Producto',
                'compuesto' => "CONCAT(a.Codigo, p.Codigo, Oes.Codigo, '-', Oi.Codigo, '-', Oe.Codigo)",
            ])
            ->joinWith('objetivosInstitucionales Oi', true, 'INNER JOIN')
            ->joinWith('objetivosInstitucionales.objetivosEstrategicos Oes', true, 'INNER JOIN')
            ->joinWith('objetivosInstitucionales.objetivosEstrategicos.areaEstrategica a', true, 'INNER JOIN')
            ->joinWith('objetivosInstitucionales.objetivosEstrategicos.politicaEstrategica p', true, 'INNER JOIN')
            ->where([
                'Oe.IdLlavePresupuestaria' => $idLlave,
                'Oe.IdGestion' => $idGestion,
            ])
            ->andWhere(['<>', 'Oe.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->orderBy(['Oe.Codigo' => SORT_ASC])
            ->asArray()
            ->all();

        return ResponseHelper::success($data);
    }

    public function actionListarIndicadoresEstrategicosS2(): array
    {
        $this->obtenerContextoActivo();

        $data = IndicadorEstrategico::find()->alias('I')
            ->select([
                'id' => 'I.IdIndicadorEstrategico',
                'text' => 'I.Descripcion',
                'codigo' => 'I.Codigo',
            ])
            ->where(['<>', 'I.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->orderBy(['I.Codigo' => SORT_ASC])
            ->asArray()
            ->all();

        return ResponseHelper::success($data);
    }

    public function actionListarIndicadoresPoaS2(): array
    {
        [, $idGestion] = $this->obtenerContextoActivo();

        $data = IndicadorPoa::find()->alias('P')
            ->select([
                'id' => 'P.IdIndicador',
                'text' => 'I.Descripcion',
                'codigo' => 'P.Codigo',
            ])
            ->innerJoin('Indicadores I', 'I.IdIndicador = P.IdIndicador')
            ->where(['P.IdGestion' => $idGestion])
            ->andWhere(['<>', 'P.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['<>', 'I.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->orderBy(['P.Codigo' => SORT_ASC])
            ->asArray()
            ->all();

        return ResponseHelper::success($data);
    }

    public function actionGuardar(): array
    {
        return $this->withTryCatch(function () {
            [$idLlave, $idGestion, $idEstadoPoa] = $this->obtenerContextoActivo();
            return $this->service->guardar(
                $this->cargarFormulario(),
                $idLlave,
                $idGestion,
                $idEstadoPoa
            );
        });
    }

    public function actionActualizar(): array
    {
        return $this->withTryCatch(function () {
            [$idLlave, $idGestion, $idEstadoPoa] = $this->obtenerContextoActivo();
            return $this->service->actualizar(
                $this->obtenerId(),
                $this->cargarFormulario(),
                $idLlave,
                $idGestion,
                $idEstadoPoa
            );
        });
    }

    public function actionBuscar(): array
    {
        [$idLlave, $idGestion, $idEstadoPoa] = $this->obtenerContextoActivo();
        return $this->withTryCatch(
            fn() => $this->service->obtenerModelo(
                $this->obtenerId(),
                $idLlave,
                $idGestion,
                $idEstadoPoa
            )
        );
    }

    public function actionCambiarEstado(): array
    {
        [$idLlave, $idGestion, $idEstadoPoa] = $this->obtenerContextoActivo();
        return $this->withTryCatch(
            fn() => $this->service->cambiarEstado(
                $this->obtenerId(),
                $idLlave,
                $idGestion,
                $idEstadoPoa
            )
        );
    }

    public function actionEliminar(): array
    {
        [$idLlave, $idGestion, $idEstadoPoa] = $this->obtenerContextoActivo();
        return $this->withTryCatch(
            fn() => $this->service->eliminar(
                $this->obtenerId(),
                $idLlave,
                $idGestion,
                $idEstadoPoa
            )
        );
    }

    public function actionVerificarCodigo(): bool
    {
        [$idLlave, $idGestion, $idEstadoPoa] = $this->obtenerContextoActivo();
        return $this->service->verificarCodigo(
            (string)Yii::$app->request->post(
                'idOperacion',
                '00000000-0000-0000-0000-000000000000'
            ),
            $idLlave,
            $idGestion,
            $idEstadoPoa,
            (string)Yii::$app->request->post('codigo', '')
        );
    }

    private function cargarFormulario(): OperacionDticForm
    {
        $form = new OperacionDticForm();

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
        $id = (string)Yii::$app->request->post('idOperacion', '');
        if ($id === '') {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'No se recibió el identificador.',
                400
            );
        }

        return $id;
    }

    private function obtenerContextoActivo(): array
    {
        $contexto = Yii::$app->userContext->contexto();
        $idGestion = (string)($contexto?->IdGestion ?? '');
        $idUnidad = (string)($contexto?->IdUnidadEjecutora ?? '');
        $idEstadoPoa = (string)($contexto?->IdEstadoPoa ?? '');

        $llave = LlavePresupuestaria::find()
            ->where(['IdUnidadEjecutora' => $idUnidad, 'esOrganizacional' => 1])
            ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->one();

        if ($idGestion === '' || $idUnidad === '' || $idEstadoPoa === '' || $llave === null) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'Debe seleccionar gestión, estado POA y llave presupuestaria en el contexto activo.',
                400
            );
        }

        return [$llave->IdLlavePresupuestaria, $idGestion, $idEstadoPoa];
    }
}
