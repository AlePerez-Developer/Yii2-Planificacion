<?php
namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\dao\ActividadDao;
use app\modules\Planificacion\models\Actividad;
use app\modules\Planificacion\models\Programa;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use Yii;

class ActividadController extends Controller
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

    public function actionIndex()
    {
        $programas = Programa::find()->where(['CodigoEstado'=>'V'])->all();
        return $this->render('Actividades',[
            'programas' => $programas
        ]);
    }

    public function actionListarActividades()
    {
        $Data = array();
        if (\Yii::$app->request->isAjax && \Yii::$app->request->isPost) {
            $actividades = Actividad::find()->alias('a')
                ->select(['a.CodigoActividad','p.Codigo as Programa','a.Codigo','a.Descripcion','a.CodigoEstado','a.CodigoUsuario'])
                ->join('Inner Join','Programas p','a.Programa = p.CodigoPrograma')
                ->where(['!=','a.CodigoEstado','E'])->andWhere(['!=','p.CodigoEstado','E'])
                ->orderBy('a.Codigo')->asArray()->all();
            foreach($actividades as  $actividad) {
                array_push($Data, $actividad);
            }
        }
        return json_encode($Data);
    }

    public function actionGuardarActividad()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if ( isset($_POST["programa"]) && isset($_POST["codigo"]) && isset($_POST["descripcion"]))
            {
                $actividad = new Actividad();
                $actividad->CodigoActividad = ActividadDao::GenerarCodigoActividad();
                $actividad->Programa = $_POST["programa"];
                $actividad->Codigo = $_POST["codigo"];
                $actividad->Descripcion = mb_strtoupper(trim($_POST["descripcion"]),'utf-8');
                $actividad->CodigoEstado = 'V';
                $actividad->CodigoUsuario = 'BGC'; //Yii::$app->user->identity->CodigoUsuario;
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
                    return json_encode($actividad->getAttributes(array('CodigoActividad','Programa','Codigo','Descripcion')));
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
            if (isset($_POST["codigoactividad"]) && isset($_POST["programa"]) && isset($_POST["codigo"]) && isset($_POST["descripcion"])){
                $actividad = Actividad::findOne($_POST["codigoactividad"]);
                if ($actividad){
                    $actividad->Programa = $_POST["programa"];
                    $actividad->Codigo = $_POST["codigo"];
                    $actividad->Descripcion = mb_strtoupper(trim($_POST["descripcion"]),'utf-8');
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
                        var_dump($ac);
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