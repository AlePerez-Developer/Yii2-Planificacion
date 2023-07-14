<?php
namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\models\Actividad;
use yii\base\BaseObject;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use Yii;

class ActividadesController extends Controller
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
        if ($action->id == "listar-actividades")
            $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        return $this->render('actividades');
    }

    public function actionListarActividades()
    {
        $Data = array();
        if (\Yii::$app->request->isAjax && \Yii::$app->request->isPost) {
            $actividades = Actividad::find()->select(['CodigoActividad','Codigo','Descripcion','CodigoEstado','CodigoUsuario'])->where(['!=','CodigoEstado','E'])->orderBy('Codigo')->asArray()->all();
            foreach($actividades as  $actividad) {
                array_push($Data, $actividad);
            }
        }
        return json_encode($Data);
    }

    public function actionGuardarActividad()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if ( isset($_POST["codigo"]) && isset($_POST["descripcion"]))
            {
                $actividad = new Actividad();
                $actividad->Codigo = $_POST["codigo"];
                $actividad->Descripcion = strtoupper(trim($_POST["descripcion"]));
                $actividad->CodigoEstado = 'V';
                $actividad->CodigoUsuario = Yii::$app->user->identity->CodigoUsuario;
                if ($actividad->validate()){
                    if (!$actividad->exist()){
                        if ($actividad->save())
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

    public function actionCambiarEstadoActividad()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigo"])) {
                $actividad = Actividad::findOne($_POST["codigo"]);
                if ($actividad){
                    if ($actividad->CodigoEstado == "V") {
                        $actividad->CodigoEstado = "C";
                    } else {
                        $actividad->CodigoEstado = "V";
                    }
                    if ($actividad->update()){
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

    public function actionEliminarActividad()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigo"]) && $_POST["codigo"] != "") {
                $actividad = Actividad::findOne($_POST["codigo"]);
                if ($actividad){
                    if (!$actividad->isUsed()) {
                        $actividad->CodigoEstado = 'E';
                        if ($actividad->update()) {
                            return "ok";
                        } else {
                            return "errorSql";
                        }
                    } else {
                        return "errorEnUso";
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

    public function actionBuscarActividad()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigo"]) && $_POST["codigo"] != "") {
                $actividad = actividad::findOne($_POST["codigo"]);
                if ($actividad){
                    return json_encode($actividad->getAttributes(array('CodigoActividad','Codigo','Descripcion')));
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

    public function actionActualizarActividad()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigoActividad"]) && isset($_POST["codigo"]) && isset($_POST["descripcion"])){
                $actividad = Actividad::findOne($_POST["codigoActividad"]);
                if ($actividad){
                    $actividad->Codigo = $_POST["codigo"];
                    $actividad->Descripcion = strtoupper(trim($_POST["descripcion"]));
                    if ($actividad->validate()){
                        if (!$actividad->exist()){
                            if ($actividad->update() !== false) {
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