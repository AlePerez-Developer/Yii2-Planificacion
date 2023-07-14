<?php


namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\dao\ObjEspecificoDao;
use app\modules\Planificacion\models\ObjetivoEspecifico;
use app\modules\Planificacion\models\ObjetivoEstrategico;
use app\modules\Planificacion\models\ObjetivoInstitucional;

use yii\base\BaseObject;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use Yii;

class ObjEspecificoController extends Controller
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
        $objsEstrategicos = ObjetivoEstrategico::find()->where(['CodigoEstado'=>'V'])->all();
        return $this->render('ObjEspecificos',['objsEstrategicos'=>$objsEstrategicos]);
    }

    public function actionListarObjs()
    {
        $Data = array();
        if (\Yii::$app->request->isAjax && \Yii::$app->request->isPost) {
            $objs = ObjetivoEspecifico::find()->select(['CodigoObjEspecifico','CodigoCOGE','Objetivo','CodigoEstado','CodigoUsuario'])->where(['!=','CodigoEstado','E'])->orderBy('CodigoObjEspecifico')->asArray()->all();
            foreach($objs as  $obj) {
                array_push($Data, $obj);
            }
        }
        return json_encode($Data);
    }

    public function actionGuardarObjs()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigoobjinstitucional"]) && isset($_POST["codigocoge"]) && isset($_POST["objetivo"])){
                $obj = new ObjetivoEspecifico();
                $obj->CodigoObjEspecifico = ObjEspecificoDao::GenerarCodigoObjEspecifico();
                $obj->CodigoObjInstitucional = $_POST["codigoobjinstitucional"];
                $obj->CodigoCOGE = strtoupper(trim($_POST["codigocoge"]));
                $obj->Objetivo = strtoupper(trim($_POST["objetivo"]));
                $obj->CodigoEstado = 'V';
                $obj->CodigoUsuario = Yii::$app->user->identity->CodigoUsuario;
                if ($obj->validate()){
                    if (!$obj->exist()){
                        if ($obj->save())
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

    /**
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function actionCambiarEstadoObj()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigoobjespecifico"])) {
                $obj = ObjetivoEspecifico::findOne($_POST["codigoobjespecifico"]);
                if ($obj){
                    if ($obj->CodigoEstado == "V") {
                        $obj->CodigoEstado = "C";
                    } else {
                        $obj->CodigoEstado = "V";
                    }
                    if ($obj->update()){
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

    /**
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionEliminarObj()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigoobjespecifico"]) && $_POST["codigoobjespecifico"] != "") {
                $obj = ObjetivoEspecifico::findOne($_POST["codigoobjespecifico"]);
                if ($obj){
                    if (!$obj->enUso()) {
                        $obj->CodigoEstado = 'E';
                        if ($obj->update()) {
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

    public function actionBuscarObj()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigoobjespecifico"]) && $_POST["codigoobjespecifico"] != "") {
                $obj = ObjetivoEspecifico::findOne($_POST["codigoobjespecifico"]);
                if ($obj){
                    return json_encode($obj->getAttributes(array('CodigoObjEspecifico','CodigoObjInstitucional','CodigoCOGE','Objetivo')));
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

    public function actionActualizarObj()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigoobjinstitucional"]) && isset($_POST["codigoobjespecifico"]) && isset($_POST["codigocoge"]) && isset($_POST["objetivo"])){
                $obj = ObjetivoEspecifico::findOne($_POST["codigoobjespecifico"]);
                if ($obj){
                    $obj->CodigoObjInstitucional = $_POST["codigoobjespecifico"];
                    $obj->CodigoCOGE = strtoupper(trim($_POST["codigocoge"]));
                    $obj->Objetivo = strtoupper(trim($_POST["objetivo"]));
                    if ($obj->validate()){
                        if (!$obj->exist()){
                            if ($obj->update() !== false) {
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