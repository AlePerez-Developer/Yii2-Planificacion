<?php
namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\dao\UnidadDao;
use app\modules\Planificacion\models\Unidad;
use common\models\Estado;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use Yii;

class UnidadController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
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
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function beforeAction($action)
    {
        if ($action->id == "listar-unidades")
            $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        return $this->render('unidad');
    }

    public function actionListarUnidades()
    {
        if (\Yii::$app->request->isAjax && \Yii::$app->request->isPost) {
            $unidades = Unidad::find()
                ->select(['CodigoUnidad','Da','Ue', 'Descripcion','Organizacional','FechaInicio','FechaFin','CodigoEstado','CodigoUsuario'])
                ->where(['!=','CodigoEstado','E'])
                ->orderBy('Da,Ue')
                ->asArray()->all();
            return json_encode($unidades);
        } else
            return 'ERROR_CABECERA';
    }

    public function actionGuardarUnidad()
    {
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_CABECERA']]);
        }
        if (!(isset($_POST["da"]) && isset($_POST["ue"])  &&
            isset($_POST["descripcion"]) && isset($_POST["organizacional"]) &&
            isset($_POST["fechaInicio"]) && isset($_POST["fechaFin"]))){
            return json_encode(['respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS']]);
        }

        $unidad = new Unidad();
        $unidad->CodigoUnidad = UnidadDao::GenerarCodigoUnidad();
        $unidad->Da = $_POST["da"];
        $unidad->Ue = $_POST["ue"];
        $unidad->Descripcion = mb_strtoupper(trim($_POST["descripcion"]),'utf-8');
        $unidad->Organizacional = intval($_POST["organizacional"]);
        $unidad->FechaInicio = date("d/m/Y", strtotime($_POST["fechaInicio"]));
        $unidad->FechaFin = date("d/m/Y", strtotime($_POST["fechaFin"]));
        $unidad->CodigoEstado = Estado::ESTADO_VIGENTE;
        $unidad->CodigoUsuario = Yii::$app->user->identity->CodigoUsuario;

        if ($unidad->exist()) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_REGISTRO_EXISTE']]);
        }
        if (!$unidad->validate()) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_VALIDACION_MODELO']]);
        }
        if (!$unidad->save()) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_EJECUCION_SQL']]);
        }

        return json_encode(['respuesta' => Yii::$app->params['PROCESO_CORRECTO']]);
    }

    public function actionCambiarEstadoUnidad()
    {
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_CABECERA']]);
        }
        if (!isset($_POST["codigoUnidad"])) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS']]);
        }

        $unidad = Unidad::findOne($_POST["codigoUnidad"]);

        if (!$unidad) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO']]);
        }

        ($unidad->CodigoEstado == Estado::ESTADO_VIGENTE)?$unidad->CodigoEstado = Estado::ESTADO_CADUCO:$unidad->CodigoEstado = Estado::ESTADO_VIGENTE;

        if ($unidad->update() === false) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_EJECUCION_SQL']]);
        }

        return json_encode(['respuesta' => Yii::$app->params['PROCESO_CORRECTO']]);
    }

    public function actionEliminarUnidad()
    {
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_CABECERA']]);
        }
        if (!(isset($_POST["codigoUnidad"]) && $_POST["codigoUnidad"] != "")) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS']]);
        }

        $unidad = Unidad::findOne($_POST["codigoUnidad"]);

        if (!$unidad) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO']]);
        }
        if ($unidad->enUso()) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_REGISTRO_EN_USO']]);
        }

        $unidad->CodigoEstado = Estado::ESTADO_ELIMINADO;

        if ($unidad->update() === false) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_EJECUCION_SQL']]);
        }

        return json_encode(['respuesta' => Yii::$app->params['PROCESO_CORRECTO']]);
    }

    public function actionBuscarUnidad()
    {
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_CABECERA']]);
        }
        if (!(isset($_POST["codigoUnidad"]) && $_POST["codigoUnidad"] != "")) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS']]);
        }

        $unidad = Unidad::findOne($_POST["codigoUnidad"]);

        if (!$unidad) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO']]);
        }

        return json_encode(['respuesta' => Yii::$app->params['PROCESO_CORRECTO'], 'unidad' => $unidad->getAttributes(array('CodigoUnidad','Da','Ue','Descripcion','Organizacional','FechaInicio','FechaFin'))]);
    }

    public function actionActualizarUnidad()
    {
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_CABECERA']]);
        }
        if (!(isset($_POST["da"]) && isset($_POST["ue"])  &&
            isset($_POST["descripcion"]) && isset($_POST["organizacional"]) &&
            isset($_POST["fechaInicio"]) && isset($_POST["fechaFin"]))){
            return json_encode(['respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS']]);
        }

        $unidad = Unidad::findOne($_POST["codigoUnidad"]);

        if (!$unidad) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO']]);
        }

        $unidad->Da = $_POST["da"];
        $unidad->Ue = $_POST["ue"];
        $unidad->Descripcion = mb_strtoupper(trim($_POST["descripcion"]),'utf-8');
        $unidad->Organizacional = intval($_POST["organizacional"]);
        $unidad->FechaInicio = date("d/m/Y", strtotime($_POST["fechaInicio"]));
        $unidad->FechaFin = date("d/m/Y", strtotime($_POST["fechaFin"]));

        if ($unidad->exist()) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_REGISTRO_EXISTE']]);
        }
        if (!$unidad->validate()) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_VALIDACION_MODELO']]);
        }
        if (!$unidad->save()) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_EJECUCION_SQL']]);
        }

        return json_encode(['respuesta' => Yii::$app->params['PROCESO_CORRECTO']]);
    }
}