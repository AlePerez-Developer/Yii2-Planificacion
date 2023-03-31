<?php

namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\models\ObjEstrategicoDao;
use app\modules\Planificacion\models\ObjetivoEstrategico;
use Throwable;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use app\modules\Planificacion\models\Pei;
use Yii;

class ObjEstrategicoController extends Controller
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
        if ($action->id == "listar-objs")
            $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }


    public function actionIndex()
    {
        $peis = Pei::find()->where(['CodigoEstado'=>'V'])->all();
        return $this->render('ObjEstrategicos',['peis'=>$peis]);
    }

    public function actionListarObjs()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            $objs = ObjetivoEstrategico::find()->all();
            $datosJson = '{"data": [';
            $i=0;
            foreach($objs as $index => $obj) {
                if ($obj->CodigoEstado == 'V') {
                    $colorEstado = "btn-success";
                    $textoEstado = "VIGENTE";
                    $estado = 'V';
                } else {
                    $colorEstado = "btn-danger";
                    $textoEstado = "NO VIGENTE";
                    $estado = "C";
                }

                $acciones = "<button class='btn btn-warning btn-sm  btnEditar' codigo='" . $obj->CodigoObjEstrategico . "'><i class='fa fa-pen'> Editar </i></button> ";
                $acciones .= "<button class='btn btn-danger btn-sm  btnEliminar' codigo='" . $obj->CodigoObjEstrategico . "' ><i class='fa fa-times'> Eliminar </i></button>";

                $estado = "<button class='btn " . $colorEstado . " btn-xs btnEstado' codigoobjestrategico='" . $obj->CodigoObjEstrategico . "' estadoobjestrategico='" . $estado . "' >" . $textoEstado . "</button>";

                $datosJson .= '[
					 	"' . ($i) . '",
					 	"' . $obj->CodigoCOGE . '",
					 	"' . $obj->Objetivo . '",
					 	"' . $obj->Producto . '",
					 	"' . $estado . '",
				      	"' . $acciones . '"
  			    ]';
                if ($index !== array_key_last($objs))
                    $datosJson .= ',';
            }
        } else {
            $datosJson = '{"data": [';
        }
        $datosJson .= ']}';
        return $datosJson;
    }

    public function actionGuardarObjs()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigopei"]) && isset($_POST["codigocoge"]) && isset($_POST["objetivo"]) && isset($_POST["producto"])){
                $obj = new ObjetivoEstrategico();
                $obj->CodigoObjEstrategico = ObjEstrategicoDao::GenerarCodigoObjEstrategico();
                $obj->CodigoPei = $_POST["codigopei"];
                $obj->CodigoCOGE = strtoupper(trim($_POST["codigocoge"]));
                $obj->Objetivo = strtoupper(trim($_POST["objetivo"]));
                $obj->Producto = strtoupper(trim($_POST["producto"]));
                $obj->CodigoEstado = 'V';
                $obj->CodigoUsuario = Yii::$app->user->identity->CodigoUsuario;
                if ($obj->validate()){
                    if (!$obj->exist()){
                        if ($obj->save())
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
                    return "errorval";
                }
            } else {
                return 'errorenvio';
            }
        } else {
            return "errorcabezera";
        }
    }

    /**
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function actionCambiarEstadoObj()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigoobjestrategico"])) {
                $obj = ObjetivoEstrategico::findOne($_POST["codigoobjestrategico"]);
                if ($obj){
                    if ($obj->CodigoEstado == "V") {
                        $obj->CodigoEstado = "C";
                    } else {
                        $obj->CodigoEstado = "V";
                    }
                    if ($obj->update()){
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

    /**
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionEliminarObj()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigoobjestrategico"]) && $_POST["codigoobjestrategico"] != "") {
                $obj = ObjetivoEstrategico::findOne($_POST["codigoobjestrategico"]);
                if ($obj){
                    if (!$obj->enUso()) {
                        if ($obj->delete()) {
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

    public function actionBuscarObj()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigoobjestrategico"]) && $_POST["codigoobjestrategico"] != "") {
                $obj = ObjetivoEstrategico::findOne($_POST["codigoobjestrategico"]);
                if ($obj){
                    return json_encode($obj->getAttributes(array('CodigoObjEstrategico','CodigoPei','CodigoCOGE','Objetivo','Producto')));
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

    public function actionActualizarObj()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigopei"]) && isset($_POST["codigoobjestrategico"]) && isset($_POST["codigocoge"]) && isset($_POST["objetivo"]) && isset($_POST["producto"])){
                $obj = ObjetivoEstrategico::findOne($_POST["codigoobjestrategico"]);
                if ($obj){
                    $obj->CodigoPei = $_POST["codigopei"];
                    $obj->CodigoCOGE = strtoupper(trim($_POST["codigocoge"]));
                    $obj->Objetivo = strtoupper(trim($_POST["objetivo"]));
                    $obj->Producto = strtoupper(trim($_POST["producto"]));
                    if ($obj->validate()){
                        if (!$obj->exist()){
                            if ($obj->update() !== false) {
                                return "ok";
                            } else {
                                return "errorsql";
                            }
                        } else {
                            return "existe";
                        }
                    } else {
                        return "errorval";
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
