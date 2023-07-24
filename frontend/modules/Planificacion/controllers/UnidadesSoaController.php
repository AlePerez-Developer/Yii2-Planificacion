<?php
namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\models\UnidadesSoaDao;
use app\modules\Planificacion\models\UnidadSoa;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use Yii;

class UnidadesSoaController extends Controller
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


    public function actionIndex()
    {
        return $this->render('UnidadesSoa');
    }

    public function actionListarUnidades()
    {
        $Data = array();
        if (\Yii::$app->request->isAjax && \Yii::$app->request->isPost) {
            $unidades = UnidadSoa::find()->select(['CodigoUnidad','NombreUnidad','NombreCorto','CodigoUnidadPadre','CodigoEstado','CodigoUsuario'])->where(['!=','CodigoEstado','E'])->orderBy('CodigoUnidad')->asArray()->all();
            foreach($unidades as  $unidad) {
                array_push($Data, $unidad);
            }
        }
        return json_encode($Data);
    }

    public function actionListarUnidadesPadre()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            $unidades = UnidadSoa::find()->where(['CodigoUnidadPadre' => null])->all();
            $datosJson = '[';
            foreach ($unidades as $index => $unidad) {
                $datosJson .= '{"name": "'.$unidad->NombreUnidad.'", "id": "'.$unidad->CodigoUnidad.'"';
                $datosJson .= $this->getData($unidad->CodigoUnidad);
                $datosJson .= '}';
                if ($index !== array_key_last($unidades))
                    $datosJson .= ',';
            }
        } else {
            $datosJson = "[{}";
        }
        $datosJson .= ']';
        return $datosJson;
    }

    public function getData($padre){
        $data = '';
        $unidades = UnidadSoa::find()->where(['CodigoUnidadPadre' => $padre])->all();
        if ($unidades){
            $data .= ',"children":[';
            foreach ($unidades as $index => $unidad){
                $data .= '{"name": "'.$unidad->NombreUnidad.'", "id": "'.$unidad->CodigoUnidad.'"';
                $data .= $this->getData($unidad->CodigoUnidad);
                $data .= "}";
                if ($index !== array_key_last($unidades))
                    $data .= ',';
            }
            $data .= "]";
        }
        return $data;
    }

    public function actionGuardarUnidad()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["nombreunidad"]) && isset($_POST["nombrecorto"]) && isset($_POST["unidadpadre"])){
                $unidad = new UnidadSoa();
                $unidad->CodigoUnidad =  UnidadesSoaDao::GenerarCodigoUnidad();
                $unidad->NombreUnidad = strtoupper(trim($_POST["nombreunidad"]));
                $unidad->NombreCorto = strtoupper(trim($_POST["nombrecorto"]));
                $unidad->CodigoUnidadPadre = strtoupper(trim($_POST["unidadpadre"]));
                $unidad->CodigoEstado = 'V';
                $unidad->CodigoUsuario = Yii::$app->user->identity->CodigoUsuario;
                if ($unidad->validate()){
                    if (!$unidad->exist()){
                        if ($unidad->save())
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

    public function actionCambiarEstadoUnidad()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigounidad"])) {
                $unidad = UnidadSoa::findOne($_POST["codigounidad"]);
                if ($unidad){
                    if ($unidad->CodigoEstado == "V") {
                        $unidad->CodigoEstado = "C";
                    } else {
                        $unidad->CodigoEstado = "V";
                    }
                    if ($unidad->update()){
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

    public function actionEliminarUnidad()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigounidad"]) && $_POST["codigounidad"] != "") {
                $unidad = UnidadSoa::findOne($_POST["codigounidad"]);
                if ($unidad){
                    if (!$unidad->enUso()) {
                        $unidad->CodigoEstado = 'E';
                        if ($unidad->update()) {
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

    public function actionBuscarUnidad()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigounidad"]) && $_POST["codigounidad"] != "") {
                $unidad = UnidadSoa::findOne($_POST["codigounidad"]);
                if ($unidad){
                    return json_encode($unidad->getAttributes(array('CodigoUnidad','NombreUnidad','NombreCorto','CodigoUnidadPadre')));
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

    public function actionActualizarUnidad()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigounidad"]) && isset($_POST["unidadpadre"]) && isset($_POST["nombreunidad"]) && isset($_POST["nombrecorto"])){
                $unidad = UnidadSoa::findOne($_POST["codigounidad"]);
                if ($unidad){
                    $unidad->NombreUnidad = strtoupper(trim($_POST["nombreunidad"]));
                    $unidad->NombreCorto = strtoupper(trim($_POST["nombrecorto"]));
                    $unidad->CodigoUnidadPadre = strtoupper(trim($_POST["unidadpadre"]));
                    if ($unidad->validate()){
                        if (!$unidad->exist()){
                            if ($unidad->update() !== false) {
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