<?php


namespace app\modules\Planificacion\controllers;
use app\modules\Planificacion\models\ObjetivoInstitucional;
use app\modules\Planificacion\models\ObjetivoEstrategico;
use app\modules\Planificacion\dao\ObjInstitucionalDao;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\base\BaseObject;
use yii\web\Controller;
use Yii;

class ObjInstitucionalController extends Controller
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
        return $this->render('ObjInstitucionales',['objsEstrategicos'=>$objsEstrategicos]);
    }

    public function actionListarObjs()
    {
        $Data = array();
        if (\Yii::$app->request->isAjax && \Yii::$app->request->isPost) {
            $objs = ObjetivoInstitucional::find()->select([
                'ObjetivosInstitucionales.CodigoObjInstitucional','ObjetivosInstitucionales.CodigoCOGE','ObjetivosInstitucionales.Objetivo','ObjetivosInstitucionales.CodigoEstado','ObjetivosInstitucionales.CodigoUsuario',
                'ObjetivosEstrategicos.CodigoCOGE as COGEEstrategico','ObjetivosEstrategicos.Objetivo as ObjEstrategico','ObjetivosEstrategicos.Producto as ProdEstrategico',
                'PEIs.DescripcionPEI','PEIs.GestionInicio','PEIs.GestionFin','PEIs.FechaAprobacion'
                ])
                ->join('INNER JOIN','ObjetivosEstrategicos', 'ObjetivosInstitucionales.CodigoObjEstrategico = ObjetivosEstrategicos.CodigoObjEstrategico')
                ->join('INNER JOIN','PEIs', 'ObjetivosEstrategicos.CodigoPei = PEIs.CodigoPei')
                ->where(['!=','ObjetivosInstitucionales.CodigoEstado','E'])->andwhere(['!=','ObjetivosEstrategicos.CodigoEstado','E'])->andWhere(['!=','PEIs.CodigoEstado','E'])
                ->orderBy('ObjetivosInstitucionales.CodigoObjInstitucional')->asArray()->all();
            foreach($objs as  $obj) {
                array_push($Data, $obj);
            }
        }
        return json_encode($Data);
    }

    public function actionGuardarObjs()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigoobjestrategico"]) && isset($_POST["codigocoge"]) && isset($_POST["objetivo"])){
                $obj = new ObjetivoInstitucional();
                $obj->CodigoObjInstitucional = ObjInstitucionalDao::GenerarCodigoObjInstitucional();
                $obj->CodigoObjEstrategico = $_POST["codigoobjestrategico"];
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
            if (isset($_POST["codigoobjinstitucional"])) {
                $obj = ObjetivoInstitucional::findOne($_POST["codigoobjinstitucional"]);
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
            if (isset($_POST["codigoobjinstitucional"]) && $_POST["codigoobjinstitucional"] != "") {
                $obj = ObjetivoInstitucional::findOne($_POST["codigoobjinstitucional"]);
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
            if (isset($_POST["codigoobjinstitucional"]) && $_POST["codigoobjinstitucional"] != "") {
                $obj = ObjetivoInstitucional::findOne($_POST["codigoobjinstitucional"]);
                if ($obj){
                    return json_encode($obj->getAttributes(array('CodigoObjInstitucional','CodigoObjEstrategico','CodigoCOGE','Objetivo')));
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
            if (isset($_POST["codigoobjestrategico"]) && isset($_POST["codigoobjinstitucional"]) && isset($_POST["codigocoge"]) && isset($_POST["objetivo"]) ){
                $obj = ObjetivoInstitucional::findOne($_POST["codigoobjinstitucional"]);
                if ($obj){
                    $obj->CodigoObjEstrategico = $_POST["codigoobjestrategico"];
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