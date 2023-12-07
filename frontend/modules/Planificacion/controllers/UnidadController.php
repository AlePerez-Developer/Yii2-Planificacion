<?php
namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\dao\UnidadDao;
use app\modules\Planificacion\models\Unidad;
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
        return $this->render('Unidades');
    }

    public function actionListarUnidades()
    {
        if (\Yii::$app->request->isAjax && \Yii::$app->request->isPost) {
            $unidades = Unidad::find()
                ->select(['CodigoUnidad','Da','Ue', 'Descripcion','Organizacional','FechaInicio','FechaFin','CodigoEstado','CodigoUsuario'])
                ->where(['!=','CodigoEstado','E'])
                ->orderBy('Da,Ue')
                ->asArray()->all();
        }
        return json_encode($unidades);
    }

    public function actionGuardarUnidad()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if ( isset($_POST["da"]) && isset($_POST["ue"])  &&
                isset($_POST["descripcion"]) && isset($_POST["organizacional"]) && isset($_POST["fechaInicio"]) && isset($_POST["fechaFin"]))
            {
                $unidad = new Unidad();
                $unidad->CodigoUnidad = UnidadDao::GenerarCodigoUnidad();
                $unidad->Da = $_POST["da"];
                $unidad->Ue = $_POST["ue"];
                $unidad->Descripcion = mb_strtoupper(trim($_POST["descripcion"]),'utf-8');
                $unidad->Organizacional = $_POST["organizacional"];
                $unidad->FechaInicio = $_POST["fechaInicio"];
                $unidad->FechaFin = $_POST["fechaFin"];
                $unidad->CodigoEstado = 'V';
                $unidad->CodigoUsuario = Yii::$app->user->identity->CodigoUsuario;
                if ($unidad->validate()){
                    if (!$unidad->exist()){
                        if ($unidad->save())
                        {
                            return "ok";
                        } else
                        {
                            return "errorSql";
                        }
                    } else {
                        return "errorExiste";
                    }
                } else {
                    return  'errorValidacion';
                }
            } else {
                return 'errorEnvio';
            }
        } else {
            return "errorCabezera";
        }
    }

    public function actionCambiarEstadoUnidad()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigoUnidad"])) {
                $unidad = Unidad::findOne($_POST["codigoUnidad"]);
                if ($unidad){
                    if ($unidad->CodigoEstado == "V") {
                        $unidad->CodigoEstado = "C";
                    } else {
                        $unidad->CodigoEstado = "V";
                    }
                    if ($unidad->update()){
                        return "ok";
                    } else {
                        return "errorSql";
                    }
                } else {
                    return 'errorNoEncontrado';
                }
            } else {
                return "errorEnvio";
            }
        } else {
            return "errorCabecera";
        }
    }

    public function actionEliminarUnidad()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigoUnidad"]) && $_POST["codigoUnidad"] != "") {
                $unidad = Unidad::findOne($_POST["codigoUnidad"]);
                if ($unidad){
                    if (!$unidad->isUsed()) {
                        $unidad->CodigoEstado = 'E';
                        if ($unidad->update()) {
                            return "ok";
                        } else {
                            return "errorSql";
                        }
                    } else {
                        return "errorEnUso";
                    }
                } else {
                    return 'errorExiste';
                }
            } else {
                return "errorEnvio";
            }
        } else {
            return "errorCabecera";
        }
    }

    public function actionBuscarUnidad()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigoUnidad"]) && $_POST["codigoUnidad"] != "") {
                $unidad = Unidad::findOne($_POST["codigoUnidad"]);
                if ($unidad){
                    return json_encode($unidad->getAttributes(array('CodigoUnidad','Da','Ue','Descripcion','Organizacional','FechaInicio','FechaFin')));
                } else {
                    return 'errorNoEncontrado';
                }
            } else {
                return "errorEnvio";
            }
        } else {
            return "errorCabecera";
        }
    }

    public function actionActualizarUnidad()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigoUnidad"]) && isset($_POST["da"]) && isset($_POST["ue"]) &&
                isset($_POST["descripcion"]) && isset($_POST["organizacional"]) && isset($_POST["fechaInicio"]) && isset($_POST["fechaFin"]))
            {
                $unidad = Unidad::findOne($_POST["codigoUnidad"]);
                if ($unidad){
                    $unidad->Da = $_POST["da"];
                    $unidad->Ue = $_POST["ue"];
                    $unidad->Descripcion = mb_strtoupper(trim($_POST["descripcion"]),'utf-8');
                    $unidad->Organizacional = $_POST["organizacional"];
                    $unidad->FechaInicio = $_POST["fechaInicio"];
                    $unidad->FechaFin = $_POST["fechaFin"];
                    if ($unidad->validate()){
                        if (!$unidad->exist()){
                            if ($unidad->update() !== false) {
                                return "ok";
                            } else {
                                return "errorSql";
                            }
                        } else {
                            return "errorExiste";
                        }
                    } else {
                        return 'errorValidacion';
                    }
                } else {
                    return "errorNoEncontrado";
                }
            } else {
                return 'errorEnvio';
            }
        } else {
            return "errorCabecera";
        }
    }
}