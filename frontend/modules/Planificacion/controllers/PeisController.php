<?php

namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\dao\PeiDao;
use app\modules\Planificacion\models\Pei;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use Yii;

class PeisController extends Controller
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
        if ($action->id == "listar-Peis")
            $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        return $this->render('peis');
    }

    public function actionListarPeis()
    {
        $Data = array();
        if (\Yii::$app->request->isAjax && \Yii::$app->request->isPost) {
            $peis = Pei::find()->select(['CodigoPei','DescripcionPei','FechaAprobacion','GestionInicio','GestionFin','CodigoEstado','CodigoUsuario'])->where(['!=','CodigoEstado','E'])->orderBy('CodigoPei')->asArray()->all();
            foreach($peis as  $pei) {
                array_push($Data, $pei);
            }
        }
        return json_encode($Data);
    }

    public function actionGuardarPei()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["descripcionPei"]) && isset($_POST["fechaAprobacion"]) && isset($_POST["gestionInicio"]) && isset($_POST["gestionFin"])) {
                $pei = new Pei();
                $pei->CodigoPei = PeiDao::generarCodigoPei();
                $pei->DescripcionPei = mb_strtoupper(trim($_POST["descripcionPei"]),'utf-8');
                $pei->FechaAprobacion = date("d/m/Y", strtotime($_POST["fechaAprobacion"]));
                $pei->GestionInicio = trim($_POST["gestionInicio"]);
                $pei->GestionFin = trim($_POST["gestionFin"]);
                $pei->CodigoEstado = 'V';
                $pei->CodigoUsuario = 'BGC';//\Yii::$app->user->identity->CodigoUsuario;
                if ($pei->validate()) {
                    if (!$pei->exist()) {
                        if ($pei->save()) {
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

    public function actionCambiarEstadoPei()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigopei"])) {
                $pei = Pei::findOne($_POST["codigopei"]);
                if ($pei) {
                    if ($pei->CodigoEstado == "V") {
                        $pei->CodigoEstado = "C";
                    } else {
                        $pei->CodigoEstado = "V";
                    }
                    if ($pei->update()) {
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

    public function actionEliminarPei()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigopei"]) && $_POST["codigopei"] != "") {
                $pei = Pei::findOne($_POST["codigopei"]);
                if ($pei) {
                    if (!$pei->enUso()) {
                        $pei->CodigoEstado = 'E';
                        if ($pei->update()) {
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

    public function actionBuscarPei()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigopei"]) && $_POST["codigopei"] != "") {
                $pei = Pei::findOne($_POST["codigopei"]);
                if ($pei) {
                    return json_encode($pei->getAttributes(array('CodigoPei', 'DescripcionPei', 'FechaAprobacion', 'GestionInicio', 'GestionFin')));
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

    public function actionActualizarPei()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigoPei"]) && isset($_POST["descripcionPei"]) && isset($_POST["fechaAprobacion"]) && isset($_POST["gestionInicio"]) && isset($_POST["gestionFin"])) {
                $pei = Pei::findOne($_POST["codigoPei"]);
                if ($pei) {
                    $pei->DescripcionPei = mb_strtoupper(trim($_POST["descripcionPei"]),'utf-8');
                    $pei->FechaAprobacion = date("d/m/Y", strtotime($_POST["fechaAprobacion"]));
                    $pei->GestionInicio = trim($_POST["gestionInicio"]);
                    $pei->GestionFin = trim($_POST["gestionFin"]);
                    if ($pei->validate()) {
                        if (!$pei->exist()) {
                            if ($pei->update() !== false) {
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



