<?php

namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\models\ObjetivoEstrategico;
use app\modules\Planificacion\dao\ObjEstrategicoDao;
use app\modules\Planificacion\models\Pei;
use common\models\Estado;
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
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            $objs = ObjetivoEstrategico::find()->select(['CodigoObjEstrategico','PEIs.DescripcionPEI','PEIs.GestionInicio','PEIs.GestionFin','CodigoCOGE','Objetivo','ObjetivosEstrategicos.CodigoEstado','ObjetivosEstrategicos.CodigoUsuario','PEIs.FechaAprobacion'])
                ->join('INNER JOIN','PEIs', 'ObjetivosEstrategicos.CodigoPei = PEIs.CodigoPei')
                ->where(['!=','ObjetivosEstrategicos.CodigoEstado','E'])->andWhere(['!=','PEIs.CodigoEstado','E'])
                ->orderBy('CodigoObjEstrategico')
                ->asArray()
                ->all();
        }
        return json_encode($objs);
    }

    public function actionGuardarObjs()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigoPei"]) && isset($_POST["codigoObj"]) && isset($_POST["objetivo"])){
                $obj = new ObjetivoEstrategico();
                $obj->CodigoObjEstrategico = ObjEstrategicoDao::GenerarCodigoObjEstrategico();
                $obj->CodigoPei = $_POST["codigoPei"];
                $obj->CodigoCOGE = trim($_POST["codigoObj"]);
                $obj->Objetivo =  mb_strtoupper(trim($_POST["objetivo"]),'utf-8');
                $obj->CodigoEstado = Estado::ESTADO_VIGENTE;
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
            if (isset($_POST["codigoObjEstrategico"])) {
                $obj = ObjetivoEstrategico::findOne($_POST["codigoObjEstrategico"]);
                if ($obj){
                    if ($obj->CodigoEstado == Estado::ESTADO_VIGENTE) {
                        $obj->CodigoEstado = Estado::ESTADO_CADUCO;
                    } else {
                        $obj->CodigoEstado = Estado::ESTADO_VIGENTE;
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
            if (isset($_POST["codigoObjEstrategico"]) && $_POST["codigoObjEstrategico"] != "") {
                $obj = ObjetivoEstrategico::findOne($_POST["codigoObjEstrategico"]);
                if ($obj){
                    if (!$obj->enUso()) {
                        $obj->CodigoEstado = Estado::ESTADO_ELIMINADO;
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
            if (isset($_POST["codigoObjEstrategico"]) && $_POST["codigoObjEstrategico"] != "") {
                $obj = ObjetivoEstrategico::findOne($_POST["codigoObjEstrategico"]);
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
            if (isset($_POST["codigoPei"]) && isset($_POST["codigoObjEstrategico"]) && isset($_POST["codigoObj"]) && isset($_POST["objetivo"])){
                $obj = ObjetivoEstrategico::findOne($_POST["codigoObjEstrategico"]);
                if ($obj){
                    $obj->CodigoPei = $_POST["codigoPei"];
                    $obj->CodigoCOGE = trim($_POST["codigoObj"]);
                    $obj->Objetivo =  mb_strtoupper(trim($_POST["objetivo"]),'utf-8');
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
