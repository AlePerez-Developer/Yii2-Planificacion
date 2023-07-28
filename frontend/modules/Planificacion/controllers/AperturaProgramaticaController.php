<?php
namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\dao\AperturaProgramaticaDao;
use app\modules\Planificacion\models\AperturaProgramatica;
use app\modules\Planificacion\models\Actividad;
use app\modules\Planificacion\models\Programa;
use app\modules\Planificacion\models\Proyecto;
use app\modules\Planificacion\models\Unidad;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use Yii;

class AperturaProgramaticaController extends Controller
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
        if ($action->id == "listar-aperturas-programaticas")
            $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        $unidades = Unidad::find()->where(['CodigoEstado'=>'V'])->all();
        $programas = Programa::find()->where(['CodigoEstado'=>'V'])->all();
        $proyectos = Proyecto::find()->where(['CodigoEstado'=>'V'])->all();
        $actividades = Actividad::find()->where(['CodigoEstado'=>'V'])->all();
        return $this->render('AperturasProgramaticas',
            [
                'unidades'=>$unidades,
                'programas'=>$programas,
                'proyectos'=>$proyectos,
                'actividades'=>$actividades
            ]);
    }

    public function actionListarAperturasProgramaticas()
    {
        $Data = array();
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            $aperturas = AperturaProgramatica::find()
                ->select(['AperturasProgramaticas.*',
                    'Unidades.Da','Unidades.Ue','Unidades.Descripcion as DescripcionUnidad',
                    'Programas.Codigo as CodigoPrograma','Programas.Descripcion as DescripcionPrograma',
                    'Proyectos.Codigo as CodigoProyecto','Proyectos.Descripcion as DescripcionProyecto',
                    'Actividades.Codigo as CodigoActividad','Actividades.Descripcion as DescripcionActividad',
                    'concat(Unidades.Da, char(45) , Unidades.Ue, char(45), Programas.Codigo, char(45), Proyectos.Codigo, char(45), Actividades.Codigo) as AperturaProgramatica'
                    ])
                ->join('INNER JOIN','Unidades', 'AperturasProgramaticas.Unidad = Unidades.CodigoUnidad')
                ->join('INNER JOIN','Programas', 'AperturasProgramaticas.Programa = Programas.CodigoPrograma')
                ->join('INNER JOIN','Proyectos', 'AperturasProgramaticas.Proyecto = Proyectos.CodigoProyecto')
                ->join('INNER JOIN','Actividades', 'AperturasProgramaticas.Actividad = Actividades.CodigoActividad')
                ->where(['!=','AperturasProgramaticas.CodigoEstado','E'])
                ->andWhere(['!=','Unidades.CodigoEstado','E'])->andWhere(['!=','Programas.CodigoEstado','E'])->andWhere(['!=','Proyectos.CodigoEstado','E'])->andWhere(['!=','Actividades.CodigoEstado','E'])
                ->orderBy('AperturasProgramaticas.CodigoAperturaProgramatica')->asArray()->all();
            foreach($aperturas as  $apertura) {
                array_push($Data, $apertura);
            }
        }
        return json_encode($Data);
    }

    public function actionGuardarAperturaProgramatica()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["unidad"]) && isset($_POST["programa"]) && isset($_POST["proyecto"])&& isset($_POST["actividad"]) &&
                isset($_POST["organizacional"]) && isset($_POST["descripcion"])){
                $apertura = new AperturaProgramatica();
                $apertura->CodigoAperturaProgramatica = AperturaProgramaticaDao::GenerarCodigoAperturaProgramatica();
                $apertura->Unidad = $_POST["unidad"];
                $apertura->Programa = $_POST["programa"];
                $apertura->Proyecto = $_POST["proyecto"];
                $apertura->Actividad = $_POST["actividad"];
                $apertura->Descripcion =  mb_strtoupper(trim($_POST["descripcion"]),'utf-8');
                $apertura->Organizacional =  $_POST["organizacional"];
                $apertura->CodigoEstado = 'V';
                $apertura->CodigoUsuario = 'BGC';//Yii::$app->user->identity->CodigoUsuario;
                if ($apertura->validate()){
                    if (!$apertura->exist()){
                        if ($apertura->save())
                        {
                            return "ok";
                        } else {
                            return "errorSql";
                        }
                    } else {
                        return "errorExiste";
                    }
                } else {
                    return "errorValidacion";
                }
            } else {
                return 'errorEnvio';
            }
        } else {
            return "errorCabecera";
        }
    }

    public function actionCambiarEstadoAperturaProgramatica()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigoAperturaProgramatica"])) {
                $apertura = AperturaProgramatica::findOne($_POST["codigoAperturaProgramatica"]);
                if ($apertura){
                    if ($apertura->CodigoEstado == "V") {
                        $apertura->CodigoEstado = "C";
                    } else {
                        $apertura->CodigoEstado = "V";
                    }
                    if ($apertura->update()){
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

    public function actionEliminarAperturaProgramatica()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigoAperturaProgramatica"]) && $_POST["codigoAperturaProgramatica"] != "") {
                $apertura = AperturaProgramatica::findOne($_POST["codigoAperturaProgramatica"]);
                if ($apertura){
                    if (!$apertura->enUso()) {
                        $apertura->CodigoEstado = 'E';
                        if ($apertura->update()) {
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

    public function actionBuscarAperturaProgramatica()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigoAperturaProgramatica"]) && $_POST["codigoAperturaProgramatica"] != "") {
                $apertura = AperturaProgramatica::findOne($_POST["codigoAperturaProgramatica"]);
                if ($apertura){
                    return json_encode($apertura->getAttributes(array('CodigoAperturaProgramatica','Unidad','Programa','Proyecto','Actividad','Descripcion','Organizacional')));
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

    public function actionActualizarAperturaProgramatica()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigoAperturaProgramatica"]) && ($_POST["codigoAperturaProgramatica"] != "") &&
                isset($_POST["unidad"]) && isset($_POST["programa"]) && isset($_POST["proyecto"])&& isset($_POST["actividad"]) &&
                isset($_POST["organizacional"]) && isset($_POST["descripcion"])){
                $apertura = AperturaProgramatica::findOne($_POST["codigoAperturaProgramatica"]);
                if ($apertura){
                    $apertura->Unidad = $_POST["unidad"];
                    $apertura->Programa = $_POST["programa"];
                    $apertura->Proyecto = $_POST["proyecto"];
                    $apertura->Actividad = $_POST["actividad"];
                    $apertura->Descripcion =  mb_strtoupper(trim($_POST["descripcion"]),'utf-8');
                    $apertura->Organizacional =  $_POST["organizacional"];
                    if ($apertura->validate()){
                        if (!$apertura->exist()){
                            if ($apertura->update()) {
                                return "ok";
                            } else {
                                return "errorSql";
                            }
                        } else {
                            return "errorExiste";
                        }
                    } else {
                        return "errorValidacion";
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