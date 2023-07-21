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
            $objs = ObjetivoEspecifico::find()->select([
                'ObjetivosEspecificos.*',
                'ObjetivosInstitucionales.CodigoCOGE as COGEInstitucional','ObjetivosInstitucionales.Objetivo as ObjInstitucional',
                'ObjetivosEstrategicos.CodigoCOGE as COGEEstrategico','ObjetivosEstrategicos.Objetivo as ObjEstrategico',
                'PEIs.DescripcionPEI','PEIs.GestionInicio','PEIs.GestionFin','PEIs.FechaAprobacion','concat(ObjetivosEstrategicos.CodigoCOGE, char(45) , ObjetivosInstitucionales.CodigoCOGE, char(45), ObjetivosEspecificos.CodigoCOGE) as Codigo  '
            ])
                ->join('INNER JOIN','ObjetivosInstitucionales','ObjetivosEspecificos.CodigoObjInstitucional = ObjetivosInstitucionales.CodigoObjInstitucional')
                ->join('INNER JOIN','ObjetivosEstrategicos', 'ObjetivosInstitucionales.CodigoObjEstrategico = ObjetivosEstrategicos.CodigoObjEstrategico')
                ->join('INNER JOIN','PEIs', 'ObjetivosEstrategicos.CodigoPei = PEIs.CodigoPei')
                ->where(['!=','ObjetivosEspecificos.CodigoEstado','E'])->andwhere(['!=','ObjetivosInstitucionales.CodigoEstado','E'])->andwhere(['!=','ObjetivosEstrategicos.CodigoEstado','E'])->andWhere(['!=','PEIs.CodigoEstado','E'])
                ->orderBy('ObjetivosEspecificos.CodigoObjEspecifico')->asArray()->all();
            foreach($objs as  $obj) {
                array_push($Data, $obj);
            }
        }
        return json_encode($Data);
    }

    public function actionListarObjsinstitucionales()
    {
        $Data = array();
        if (\Yii::$app->request->isAjax && \Yii::$app->request->isPost && isset($_POST["codigo"])) {
            $objs = ObjetivoInstitucional::find()->select(['CodigoObjInstitucional','Objetivo'])
                ->where(['CodigoObjEstrategico'=>$_POST["codigo"]])
                ->andWhere(['!=','CodigoEstado','E'])->orderBy('CodigoObjInstitucional')->asArray()->all();
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
        if (\Yii::$app->request->isAjax && \Yii::$app->request->isPost) {
            if (isset($_POST["codigoobjespecifico"]) && $_POST["codigoobjespecifico"] != "") {
                $obj = ObjetivoEspecifico::find()->select([
                    'ObjetivosEspecificos.*',
                    'ObjetivosInstitucionales.CodigoObjInstitucional as CodigoInstitucional',
                    'ObjetivosEstrategicos.CodigoObjEstrategico as CodigoEstrategico'
                ])
                    ->join('INNER JOIN','ObjetivosInstitucionales','ObjetivosEspecificos.CodigoObjInstitucional = ObjetivosInstitucionales.CodigoObjInstitucional')
                    ->join('INNER JOIN','ObjetivosEstrategicos', 'ObjetivosInstitucionales.CodigoObjEstrategico = ObjetivosEstrategicos.CodigoObjEstrategico')
                    ->where(['CodigoObjEspecifico' => $_POST["codigoobjespecifico"] ])
                    ->orderBy('ObjetivosEspecificos.CodigoObjEspecifico')->asArray()->one();

                if ($obj){
                    return json_encode($obj);
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
                    $obj->CodigoObjInstitucional = $_POST["codigoobjinstitucional"];
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