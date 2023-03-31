<?php

namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\models\PeiDao;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use app\modules\Planificacion\models\Pei;
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
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            $peis = Pei::find()->all();
            $datosJson = '{"data": [';
            $i = 0;
            foreach ($peis as $index => $pei) {
                if ($pei->CodigoEstado == 'V') {
                    $colorEstado = "btn-success";
                    $textoEstado = "VIGENTE";
                    $estado = 'V';
                } else {
                    $colorEstado = "btn-danger";
                    $textoEstado = "NO VIGENTE";
                    $estado = "C";
                }
                $acciones = "<button class='btn btn-warning btn-sm  btnEditar' codigo-pei='" . $pei->CodigoPei . "'><i class='fa fa-pen'> Editar </i></button> ";
                $acciones .= "<button class='btn btn-danger btn-sm  btnEliminar' codigo-pei='" . $pei->CodigoPei . "' ><i class='fa fa-times'> Eliminar </i></button>";
                $estado = "<button class='btn " . $colorEstado . " btn-xs btnEstado' codigo='" . $pei->CodigoPei . "' estado='" . $estado . "' >" . $textoEstado . "</button>";
                $datosJson .= '[
					 	"' . ($i) . '",
					 	"' . $pei->DescripcionPei . '",
					 	"' . $pei->FechaAprobacion . '",
					 	"' . $pei->GestionInicio . '",
					 	"' . $pei->GestionFin . '",
					 	"' . $estado . '",
				      	"' . $acciones . '"
  			    ]';
                if ($index !== array_key_last($peis))
                    $datosJson .= ',';
            }
        } else {
            $datosJson = '{"data": [';
        }
        $datosJson .= ']}';
        return $datosJson;
    }

    public function actionGuardarPei()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["descripcionPei"]) && isset($_POST["fechaAprobacion"]) && isset($_POST["gestionInicio"]) && isset($_POST["gestionFin"])) {
                $pei = new Pei();
                $pei->CodigoPei = PeiDao::generarCodigoPei();
                $pei->DescripcionPei = strtoupper(trim($_POST["descripcionPei"]));
                $pei->FechaAprobacion = date("d/m/Y", strtotime($_POST["fechaAprobacion"]));
                $pei->GestionInicio = $_POST["gestionInicio"];
                $pei->GestionFin = $_POST["gestionFin"];
                $pei->CodigoEstado = 'V';
                $pei->CodigoUsuario = Yii::$app->user->identity->CodigoUsuario;
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
                        if ($pei->delete()) {
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
                    $pei->DescripcionPei = strtoupper(trim($_POST["descripcionPei"]));
                    $pei->FechaAprobacion = date("d/m/Y", strtotime($_POST["fechaAprobacion"]));
                    $pei->GestionInicio = $_POST["gestionInicio"];
                    $pei->GestionFin = $_POST["gestionFin"];
                    if ($pei->validate()) {
                        if (!$pei->exist()) {
                            if ($pei->update() !== false) {
                                return "ok";
                            } else {
                                return "errorsql";
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



