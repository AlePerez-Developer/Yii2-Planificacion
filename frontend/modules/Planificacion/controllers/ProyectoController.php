<?php


namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\dao\ProyectoDao;
use app\modules\Planificacion\models\AperturaProgramatica;
use app\modules\Planificacion\models\Programa;
use app\modules\Planificacion\models\Proyecto;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\base\BaseObject;
use yii\web\Controller;
use Yii;

class ProyectoController extends Controller
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
        $programas = Programa::find()->where(['CodigoEstado'=>'V'])->all();
        return $this->render('Proyectos',[
            'programas' => $programas
            ]);
    }

    public function actionListarProyectos()
    {
        $Data = array();
        if (\Yii::$app->request->isAjax && \Yii::$app->request->isPost) {
            $proyectos = Proyecto::find()->alias('proy')
                ->select(['proy.CodigoProyecto','p.Codigo as Programa', 'proy.Codigo','proy.Descripcion','proy.CodigoEstado','proy.CodigoUsuario'])
                ->join('Inner Join','Programas p','proy.Programa = p.CodigoPrograma')
                ->where(['!=','proy.CodigoEstado','E'])->andWhere(['!=','p.CodigoEstado','E'])
                ->orderBy('proy.Codigo')->asArray()->all();
            foreach($proyectos as  $proyecto) {
                array_push($Data, $proyecto);
            }
        }
        return json_encode($Data);
    }

    public function actionGuardarProyecto()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if ( isset($_POST["programa"]) && isset($_POST["codigo"]) && isset($_POST["descripcion"]))
            {
                $proyecto = new Proyecto();
                $proyecto->CodigoProyecto = ProyectoDao::GenerarCodigoProyecto();
                $proyecto->Programa = $_POST["programa"];
                $proyecto->Codigo = $_POST["codigo"];
                $proyecto->Descripcion =mb_strtoupper(trim($_POST["descripcion"]),'utf-8');
                $proyecto->CodigoEstado = 'V';
                $proyecto->CodigoUsuario = 'BGC'; //Yii::$app->user->identity->CodigoUsuario;
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
                    return json_encode($proyecto->getAttributes(array('CodigoProyecto','Programa', 'Codigo','Descripcion')));
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
            if (isset($_POST["codigoproyecto"]) && isset($_POST["programa"]) && isset($_POST["codigo"]) && isset($_POST["descripcion"])){
                $proyecto = Proyecto::findOne($_POST["codigoproyecto"]);
                if ($proyecto){
                    $proyecto->Programa = $_POST["programa"];
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