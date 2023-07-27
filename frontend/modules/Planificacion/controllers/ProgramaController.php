<?php
namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\dao\ProgramaDao;
use app\modules\Planificacion\models\Programa;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use Yii;

class ProgramaController extends Controller
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
        if ($action->id == "listar-programas")
            $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        return $this->render('Programas');
    }

    public function actionListarProgramas()
    {
        $Data = array();
        if (\Yii::$app->request->isAjax && \Yii::$app->request->isPost) {
            $programas = Programa::find()->select(['CodigoPrograma','Codigo','Descripcion','CodigoEstado','CodigoUsuario'])->where(['!=','CodigoEstado','E'])->orderBy('Codigo')->asArray()->all();
            foreach($programas as  $programa) {
                array_push($Data, $programa);
            }
        }
        return json_encode($Data);
    }

    public function actionGuardarPrograma()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if ( isset($_POST["codigo"]) && isset($_POST["descripcion"]))
            {
                $programa = new Programa();
                $programa->CodigoPrograma = ProgramaDao::GenerarCodigoPrograma();
                $programa->Codigo = $_POST["codigo"];
                $programa->Descripcion = mb_strtoupper(trim($_POST["descripcion"]),'utf-8');
                $programa->CodigoEstado = 'V';
                $programa->CodigoUsuario = 'BGC'; //Yii::$app->user->identity->CodigoUsuario;
                if ($programa->validate()){
                    if (!$programa->exist()){
                        if ($programa->save())
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

    public function actionCambiarEstadoPrograma()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigo"])) {
                $programa = Programa::findOne($_POST["codigo"]);
                if ($programa){
                    if ($programa->CodigoEstado == "V") {
                        $programa->CodigoEstado = "C";
                    } else {
                        $programa->CodigoEstado = "V";
                    }
                    if ($programa->update()){
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

    public function actionEliminarPrograma()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigo"]) && $_POST["codigo"] != "") {
                $programa = Programa::findOne($_POST["codigo"]);
                if ($programa){
                    if (!$programa->isUsed()) {
                        $programa->CodigoEstado = 'E';
                        if ($programa->update()) {
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

    public function actionBuscarPrograma()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigo"]) && $_POST["codigo"] != "") {
                $programa = Programa::findOne($_POST["codigo"]);
                if ($programa){
                    return json_encode($programa->getAttributes(array('CodigoPrograma','Codigo','Descripcion')));
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

    public function actionActualizarPrograma()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigoprograma"]) && isset($_POST["codigo"]) && isset($_POST["descripcion"])){
                $programa = Programa::findOne($_POST["codigoprograma"]);
                if ($programa){
                    $programa->Codigo = $_POST["codigo"];
                    $programa->Descripcion = mb_strtoupper(trim($_POST["descripcion"]),'utf-8');
                    if ($programa->validate()){
                        if (!$programa->exist()){
                            if ($programa->update() !== false) {
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