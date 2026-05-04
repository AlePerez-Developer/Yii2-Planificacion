<?php

namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\models\LlavePresupuestaria;
use app\modules\Planificacion\models\PeiGestion;
use app\modules\Planificacion\models\ProgramacionIndicadorGestion;
use app\modules\Planificacion\services\IndicadorEstrategicoService;
use app\controllers\BaseController;
use common\models\Estado;
use yii\db\Exception;
use yii\db\Expression;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use Yii;
use yii\web\Response;

/**
 * @noinspection PhpUnused
 */

class IndicadorEstrategicoProgramacionController extends BaseController
{
    private IndicadorEstrategicoService $serviceIndicadorEstrategico;

    public function __construct($id, $module,
                                IndicadorEstrategicoService $serviceIndicadorEstrategico,
        $config = [])
    {
        $this->serviceIndicadorEstrategico = $serviceIndicadorEstrategico;
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
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function beforeAction($action): bool
    {
        if ($action->id == "listar-todo")
            $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    /**
     * accion index.
     *
     * @param string $id
     * @return string
     */
    public function actionIndex(string $id): string
    {
        return $this->render('index', ['idObjEstrategico' => $id]);
    }

    /**
     * accion para listar todos los registros del modelo.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     * @noinspection PhpUnused
     */
    public function actionListarIndicadores(): array
    {
        return $this->withTryCatch(function () {
            $id = $this->obtenerId();
            return $this->serviceIndicadorEstrategico->listarTodobyObj($id);
        });
    }

    /**
     * accion para listar todos los registros del modelo.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     * @noinspection PhpUnused
     */
    public function actionListarGestiones(): array
    {
        return $this->withTryCatch(function () {
            $data = PeiGestion::find()->asArray()->all();
            return ResponseHelper::success($data, 'Listado de Indicadores Estrategicos obtenido.');
        });
    }


    /**
     * accion para listar todos los registros del modelo.
     *
     * @return array ['success' => bool, 'mensaje' => string, 'data' => string, 'errors' => array|null]
     * @noinspection PhpUnused
     */
    public function actionListarProgramacion(): array
    {
        return $this->withTryCatch(function () {
            $idGestion = Yii::$app->request->post('idGestion');
            $idIndicadorEstrategico = Yii::$app->request->post('idIndicadorEstrategico');

            $data = ProgramacionIndicadorGestion::listAllbyGestion($idIndicadorEstrategico, $idGestion)->asArray()->all();
            return ResponseHelper::success($data, 'Listado de Indicadores Estrategicos obtenido.');
        });
    }

    /**
     * obtiene y valida si se recibio el codigo por el request
     *
     * return string
     * @throws ValidationException
     */
    private function obtenerId(): string
    {
        $id = Yii::$app->request->post('idObjEstrategico');
        if (!$id) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'Codigo de objetivo no enviado.', 404);
        }
        return $id;
    }

    public function actionBuscarLlaves()
    {
        $term = Yii::$app->request->post('term');

        $data = LlavePresupuestaria::find()->alias('l')
            ->select([
                'l.IdLlavePresupuestaria',
                'CONCAT(Lu.Da,\'-\',Lu.Ue,\'-\',Lpr.Codigo,\'-\',Lpy.Codigo,\'-\',La.Codigo) AS Llave',
                'l.Descripcion',
                'Meta' => new Expression('0'),
            ])
            ->joinWith('unidad Lu', true, 'INNER JOIN')
            ->joinWith('programa Lpr', true, 'INNER JOIN')
            ->joinWith('proyecto Lpy', true, 'INNER JOIN')
            ->joinWith('actividad La', true, 'INNER JOIN')
            ->where(['!=', 'l.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andFilterWhere([
                'or',
                ['like', new Expression("CONCAT(Lu.Da,'-',Lu.Ue,'-',Lpr.Codigo,'-',Lpy.Codigo,'-',La.Codigo)"), $term],
                ['like', 'l.Descripcion', $term], // ajusta el alias si corresponde
            ])->limit(20)->asArray()->all();

        $res=[];
        foreach($data as $d){
            $res[]=[
                'IdLlavePresupuestaria'=>$d['IdLlavePresupuestaria'],
                'Llave'=>$d['Llave'],
                'Descripcion'=>$d['Descripcion'],
                'Meta'=>$d['Meta'],
            ];
        }

        return $this->asJson($res);
    }

    /**
     * @throws ValidationException
     * @throws Exception
     */
    public function actionGuardarProgramacion()
    {
        $idGestion = Yii::$app->request->post('IdGestion');
        $idIndicadorEstrategico = Yii::$app->request->post('IdIndicadorEstrategico');
        $idLlavePresupuestaria = Yii::$app->request->post('IdLlavePresupuestaria');
        $meta = Yii::$app->request->post('Meta');

        $modelo = new ProgramacionIndicadorGestion([
            'IdIndicadorEstrategico'  => $idIndicadorEstrategico,
            'IdLlavePresupuestaria' => $idLlavePresupuestaria,
            'IdGestion'   => $idGestion,
            'MetaProgramada'      => $meta,
            'CodigoUsuario'   => Yii::$app->user->identity->CodigoUsuario ?? null,
        ]);

        if (!$modelo->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'],$modelo->getErrors(),500);
        }

        if (!$modelo->save(false)) {
            Yii::error("Error al guardar los datos del objetivo $modelo->Descripcion", __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'],$modelo->getErrors(),500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => '',
        ];


    }

}