<?php


namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\models\AperturaProgramatica;
use app\modules\Planificacion\models\Proyecto;
use yii\base\BaseObject;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use Yii;

class ProyectosController extends Controller
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
        if ($action->id == "listar-proyectos")
            $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        return $this->render('proyectos');
    }

    public function actionListarProyectos()
    {
        $Data = array();
        if (\Yii::$app->request->isAjax && \Yii::$app->request->isPost) {
            $proyectos = Proyecto::find()->select(['CodigoProyecto','Codigo','Descripcion','CodigoEstado','CodigoUsuario'])->where(['!=','CodigoEstado','E'])->orderBy('Codigo')->asArray()->all();
            foreach($proyectos as  $proyecto) {
                array_push($Data, $proyecto);
            }
        }
        return json_encode($Data);
    }

    public function actionGuardarProyecto()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if ( isset($_POST["codigo"]) && isset($_POST["descripcion"]))
            {
                $proyecto = new Proyecto();
                $proyecto->Codigo = $_POST["codigo"];
                $proyecto->Descripcion = strtoupper(trim($_POST["descripcion"]));
                $proyecto->CodigoEstado = 'V';
                $proyecto->CodigoUsuario = Yii::$app->user->identity->CodigoUsuario;
                if ($proyecto->validate()){
                    if (!$proyecto->exist()){
                        if ($proyecto->save())
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

    public function actionCambiarEstadoProyecto()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigo"])) {
                $proyecto = Proyecto::findOne($_POST["codigo"]);
                if ($proyecto){
                    if ($proyecto->CodigoEstado == "V") {
                        $proyecto->CodigoEstado = "C";
                    } else {
                        $proyecto->CodigoEstado = "V";
                    }
                    if ($proyecto->update()){
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

    public function actionEliminarProyecto()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigo"]) && $_POST["codigo"] != "") {
                $proyecto = Proyecto::findOne($_POST["codigo"]);
                if ($proyecto){
                    if (!$proyecto->isUsed()) {
                        $proyecto->CodigoEstado = 'E';
                        if ($proyecto->update()) {
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

    public function actionBuscarProyecto()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigo"]) && $_POST["codigo"] != "") {
                $proyecto = Proyecto::findOne($_POST["codigo"]);
                if ($proyecto){
                    return json_encode($proyecto->getAttributes(array('CodigoProyecto','Codigo','Descripcion')));
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

    public function actionActualizarProyecto()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigoProyecto"]) && isset($_POST["codigo"]) && isset($_POST["descripcion"])){
                $proyecto = Proyecto::findOne($_POST["codigoProyecto"]);
                if ($proyecto){
                    $proyecto->Codigo = $_POST["codigo"];
                    $proyecto->Descripcion = strtoupper(trim($_POST["descripcion"]));
                    if ($proyecto->validate()){
                        if (!$proyecto->exist()){
                            if ($proyecto->update() !== false) {
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