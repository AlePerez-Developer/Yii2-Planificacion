<?php

namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\models\CargosDao;
use common\models\Cargo;
use common\models\SectorTrabajo;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use Yii;

class CargosController extends Controller
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
        if ($action->id == "listar-cargos")
            $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        $sectoresTrabajo = SectorTrabajo::find()->all();
        return $this->render('cargos', [
            'sectoresTrabajo' => $sectoresTrabajo,
        ]);
    }

    public function actionListarCargos()
    {
        if (\Yii::$app->request->isAjax && \Yii::$app->request->isPost) {

            $r = array();
            $cargos = Cargo::find()->select(['CodigoCargo','NombreCargo','DescripcionCargo','ArchivoManualFunciones','CodigoSectorTrabajo','CodigoEstado','CodigoUsuario'])->where(['!=','CodigoEstado','E'])->orderBy('CodigoCargo')->asArray()->all();


            $result = array();

            foreach($cargos as  $cargo) {
                array_push($result, $cargo);
            }

            return json_encode($result);

            //return json_encode($cargos, JSON_FORCE_OBJECT);

            //return json_encode($cargos);

            /*
            $datosJson = '{"data": [';
            $i=0;
            foreach($cargos as $index => $cargo) {
                if ($cargo->CodigoEstado == 'V') {
                    $colorEstado = "btn-success";
                    $textoEstado = "VIGENTE";
                    $estado = 'V';
                } else {
                    $colorEstado = "btn-danger";
                    $textoEstado = "NO VIGENTE";
                    $estado = "C";
                }

                $acciones = "<button class='btn btn-warning btn-sm  btnEditar' codigo='" . $cargo->CodigoCargo . "'><i class='fa fa-pen'> Editar </i></button> ";
                $acciones .= "<button class='btn btn-danger btn-sm  btnEliminar' codigo='" . $cargo->CodigoCargo . "' ><i class='fa fa-times'> Eliminar </i></button>";

                $estado = "<button class='btn " . $colorEstado . " btn-xs btnEstado' codigo='" . $cargo->CodigoCargo . "' estado='" . $estado . "' >" . $textoEstado . "</button>";

                $datosJson .= '[
					 	"' . ($i) . '",				 	
					 	"' . $cargo->CodigoCargo . '",
					 	"' . $cargo->NombreCargo . '",
					 	"' . $cargo->DescripcionCargo . '",
					 	"' . $cargo->sectorTrabajo->NombreSectorTrabajo . '",
					 	"' . $estado . '",
				      	"' . $acciones . '"
  			    ]';
                if ($index !== array_key_last($cargos))
                    $datosJson .= ',';
            }
            $datosJson .= ']}';
            return $datosJson;*/


            return $cargos;
            //return json_encode($result);
        } else {
            $datosJson = '{"data": [';
        }
        $datosJson .= ']}';
        //return json_encode($cargos);
    }

    public function actionGuardarCargo()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["nombrecargo"]) && isset($_POST["sectortrabajo"])){
                $cargo = new Cargo();
                $cargo->CodigoCargo =  CargosDao::GenerarCodigoCargo($_POST["sectortrabajo"]);
                $cargo->NombreCargo = $_POST["nombrecargo"];
                $cargo->DescripcionCargo = strtoupper(trim($_POST["descripcioncargo"]));
                $cargo->RequisitosPrincipales = strtoupper(trim($_POST["requisitosprincipales"]));
                $cargo->RequisitosOpcionales = strtoupper(trim($_POST["requisitosopcionales"]));
                $cargo->ArchivoManualFunciones = $cargo->CodigoCargo . '.pdf';
                $cargo->CodigoSectorTrabajo = $_POST["sectortrabajo"];
                $cargo->CodigoEstado = 'V';
                $cargo->CodigoUsuario = Yii::$app->user->identity->CodigoUsuario;
                if ($cargo->validate()){
                    if (!$cargo->exist()){
                        if ($cargo->save())
                        {
                            return "ok";
                        } else
                        {
                            return "errorsql";
                        }
                    } else {
                        return "existe";
                    }
                } else {
                    return  'errorval';
                }
            } else {
                return 'errorenvio';
            }
        } else {
            return "errorcabezera";
        }
    }

    public function actionCambiarEstadoCargo()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigocargo"])) {
                $cargo = new Cargo();
                $cargo = Cargo::findOne($_POST["codigocargo"]);
                if ($cargo){
                    if ($cargo->CodigoEstado == "V") {
                        $cargo->CodigoEstado = "C";
                    } else {
                        $cargo->CodigoEstado = "V";
                    }
                    if ($cargo->update()){
                        return "ok";
                    } else {
                        return "errorsql";
                    }
                } else {
                    return 'errorval';
                }
            } else {
                return "errorenvio";
            }
        } else {
            return "errorcabezera";
        }
    }

    public function actionEliminarCargo()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigocargo"]) && $_POST["codigocargo"] != "") {
                $cargo = Cargo::findOne($_POST["codigocargo"]);
                if ($cargo){
                    if (!$cargo->isUsed()) {
                        $cargo->CodigoEstado = 'E';
                        if ($cargo->update()) {
                            return "ok";
                        } else {
                            return "errorsql";
                        }
                    } else {
                        return "enUso";
                    }
                } else {
                    return 'errorval';
                }
            } else {
                return "errorenvio";
            }
        } else {
            return "errorcabezera";
        }
    }

    public function actionBuscarCargo()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigocargo"]) && $_POST["codigocargo"] != "") {
                $cargo = Cargo::findOne($_POST["codigocargo"]);
                if ($cargo){
                    return json_encode($cargo->getAttributes(array('CodigoCargo','NombreCargo','DescripcionCargo','RequisitosPrincipales','RequisitosOpcionales','ArchivoManualFunciones','CodigoSectorTrabajo')));
                } else {
                    return 'errorval';
                }
            } else {
                return "errorenvio";
            }
        } else {
            return "errorcabezera";
        }
    }

    public function actionActualizarCargo()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigocargo"]) && isset($_POST["nombrecargo"]) && isset($_POST["sectortrabajo"])){
                $cargo = Cargo::findOne($_POST["codigocargo"]);
                if ($cargo){
                    $cargo->NombreCargo = $_POST["nombrecargo"];
                    $cargo->DescripcionCargo = strtoupper(trim($_POST["descripcioncargo"]));
                    $cargo->RequisitosPrincipales = strtoupper(trim($_POST["requisitosprincipales"]));
                    $cargo->RequisitosOpcionales = strtoupper(trim($_POST["requisitosopcionales"]));
                    $cargo->CodigoSectorTrabajo = strtoupper(trim($_POST["sectortrabajo"]));
                    if ($cargo->validate()){
                        if (!$cargo->exist()){
                            if ($cargo->update() !== false) {
                                return "ok";
                            } else {
                                return "errorsql";
                            }
                        } else {
                            return "existe";
                        }
                    } else {
                        return 'errorval';
                    }
                } else {
                    return "errorval";
                }
            } else {
                return 'errorenvio';
            }
        } else {
            return "errorcabezera";
        }
    }
}