<?php

namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\models\AperturaProgramatica;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use Yii;

class AperturasProgramaticasController extends Controller
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
        if ($action->id == "listar-aperturas")
            $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        return $this->render('aperturasprogramaticas');
    }

    public function actionListarAperturas()
    {
        $Data = array();
        if (\Yii::$app->request->isAjax && \Yii::$app->request->isPost) {
            $aperturas = AperturaProgramatica::find()->select(['CodigoAperturaProgramatica','Da','Ue','Prg','Descripcion','FechaInicio','FechaFin','Organizacional','Operacional','CodigoEstado','CodigoUsuario'])->where(['!=','CodigoEstado','E'])->orderBy('Da,Ue,Prg')->asArray()->all();
            foreach($aperturas as  $apertura) {
                array_push($Data, $apertura);
            }
        }
        return json_encode($Data);
    }

    public function actionGuardarApertura()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if ( isset($_POST["da"]) && isset($_POST["ue"]) && isset($_POST["prg"]) &&
                 isset($_POST["descripcion"]) && isset($_POST["fechaInicio"]) &&
                 isset($_POST["fechaFin"]) && isset($_POST["organizacional"]) &&
                 isset($_POST["operacional"]))
            {
                $apertura = new AperturaProgramatica();
                $apertura->Da = $_POST["da"];
                $apertura->Ue = $_POST["ue"];
                $apertura->Prg = $_POST["prg"];
                $apertura->Descripcion = strtoupper(trim($_POST["descripcion"]));
                $apertura->FechaInicio = $_POST["fechaInicio"];
                $apertura->FechaFin = $_POST["fechaFin"];
                $apertura->Organizacional = $_POST["organizacional"];
                $apertura->Operacional = $_POST["operacional"];
                $apertura->CodigoEstado = 'V';
                $apertura->CodigoUsuario = Yii::$app->user->identity->CodigoUsuario;
                if ($apertura->validate()){
                    if (!$apertura->exist()){
                        if ($apertura->save())
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

    public function actionCambiarEstadoApertura()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigo"])) {
                $apertura = new AperturaProgramatica();
                $apertura = AperturaProgramatica::findOne($_POST["codigo"]);
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

    public function actionEliminarApertura()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigo"]) && $_POST["codigo"] != "") {
                $apertura = AperturaProgramatica::findOne($_POST["codigo"]);
                if ($apertura){
                    if (!$apertura->isUsed()) {
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
                    return 'errorExiste';
                }
            } else {
                return "errorEnvio";
            }
        } else {
            return "errorCabecera";
        }
    }

    public function actionBuscarApertura()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigo"]) && $_POST["codigo"] != "") {
                $apertura = AperturaProgramatica::findOne($_POST["codigo"]);
                if ($apertura){
                    return json_encode($apertura->getAttributes(array('CodigoAperturaProgramatica','Da','Ue','Prg','Descripcion','FechaInicio','FechaFin','Organizacional','Operacional')));
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

    public function actionActualizarApertura()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigo"]) && isset($_POST["da"]) && isset($_POST["ue"]) && isset($_POST["prg"]) &&
                isset($_POST["descripcion"]) && isset($_POST["fechaInicio"]) &&
                isset($_POST["fechaFin"]) && isset($_POST["organizacional"]) &&
                isset($_POST["operacional"]))
            {
                $apertura = AperturaProgramatica::findOne($_POST["codigo"]);
                if ($apertura){
                    $apertura->Da = $_POST["da"];
                    $apertura->Ue = $_POST["ue"];
                    $apertura->Prg = $_POST["prg"];
                    $apertura->Descripcion = strtoupper(trim($_POST["descripcion"]));
                    $apertura->FechaInicio = $_POST["fechaInicio"];
                    $apertura->FechaFin = $_POST["fechaFin"];
                    $apertura->Organizacional = $_POST["organizacional"];
                    $apertura->Operacional = $_POST["operacional"];
                    if ($apertura->validate()){
                        if (!$apertura->exist()){
                            if ($apertura->update() !== false) {
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