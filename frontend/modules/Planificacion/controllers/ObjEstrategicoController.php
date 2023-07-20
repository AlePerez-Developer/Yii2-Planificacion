<?php

namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\models\ObjetivoEstrategico;
use app\modules\Planificacion\dao\ObjEstrategicoDao;
use app\modules\Planificacion\models\Pei;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use Throwable;
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
        $Data = array();
        if (\Yii::$app->request->isAjax && \Yii::$app->request->isPost) {
            $objs = ObjetivoEstrategico::find()->select(['CodigoObjEstrategico','PEIs.DescripcionPEI','PEIs.GestionInicio','PEIs.GestionFin','CodigoCOGE','Objetivo','ObjetivosEstrategicos.CodigoEstado','ObjetivosEstrategicos.CodigoUsuario','PEIs.FechaAprobacion'])
                ->join('INNER JOIN','PEIs', 'ObjetivosEstrategicos.CodigoPei = PEIs.CodigoPei')
                ->where(['!=','ObjetivosEstrategicos.CodigoEstado','E'])->andWhere(['!=','PEIs.CodigoEstado','E'])
                ->orderBy('CodigoObjEstrategico')->asArray()->all();
            foreach($objs as  $obj) {
                array_push($Data, $obj);
            }
        }
        return json_encode($Data);
    }

    public function actionGuardarObjs()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigopei"]) && isset($_POST["codigocoge"]) && isset($_POST["objetivo"])){
                $obj = new ObjetivoEstrategico();
                $obj->CodigoObjEstrategico = ObjEstrategicoDao::GenerarCodigoObjEstrategico();
                $obj->CodigoPei = $_POST["codigopei"];
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
                    var_dump($obj->errors);
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
            if (isset($_POST["codigoobjestrategico"]) && $_POST["codigoobjestrategico"] != "") {
                $obj = ObjetivoEstrategico::findOne($_POST["codigoobjestrategico"]);
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
            if (isset($_POST["codigoobjestrategico"]) && $_POST["codigoobjestrategico"] != "") {
                $obj = ObjetivoEstrategico::findOne($_POST["codigoobjestrategico"]);
                if ($obj){
                    return json_encode($obj->getAttributes(array('CodigoObjEstrategico','CodigoPei','CodigoCOGE','Objetivo')));
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
            if (isset($_POST["codigopei"]) && isset($_POST["codigoobjestrategico"]) && isset($_POST["codigocoge"]) && isset($_POST["objetivo"])){
                $obj = ObjetivoEstrategico::findOne($_POST["codigoobjestrategico"]);
                if ($obj){
                    $obj->CodigoPei = $_POST["codigopei"];
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
