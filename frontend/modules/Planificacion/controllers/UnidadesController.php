<?php
namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\models\PeiDao ;
use app\modules\Planificacion\models\UnidadesDao;
use common\models\TipoUnidad;
use common\models\Unidad;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use Throwable;
use Yii;

class UnidadesController extends Controller
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
        if ( ($action->id == "listar-unidades") || ($action->id == "listar-unidades-padre") )
            $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        return $this->render('Unidades');
    }

    public function actionListarUnidades()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            $unidades = Unidad::find()->where(['!=','CodigoEstado','E'])->all();
            $datosJson = '{"data": [';
            $i=0;
            foreach($unidades as $index => $unidad) {
                if ($unidad->CodigoEstado == 'V') {
                    $colorEstado = "btn-success";
                    $textoEstado = "VIGENTE";
                    $estado = 'V';
                } else {
                    $colorEstado = "btn-danger";
                    $textoEstado = "NO VIGENTE";
                    $estado = "C";
                }
                
                $acciones = "<div class='btn-group' role='group'>";
                $acciones .= "<button class='btn btn-warning btn-xs  btnEditar' codigo='" . $unidad->CodigoUnidad . "'><i class='fa fa-pen'></i> Editar </button> ";
                $acciones .= "<button class='btn btn-danger btn-xs  btnEliminar' codigo='" . $unidad->CodigoUnidad . "' ><i class='fa fa-times'></i> Eliminar </button>";
                $acciones .= '</div>';

                $estado = "<button class='btn " . $colorEstado . " btn-xs btnEstado' codigo='" . $unidad->CodigoUnidad . "' estado='" . $estado . "' >" . $textoEstado . "</button>";

                $padre = ($unidad->CodigoUnidadPadre)?$unidad->unidadPadre->NombreUnidad:'';

                $datosJson .= '[
					 	"' . ($i) . '",
					 	"' . $unidad->CodigoUnidad . '",
					 	"' . $unidad->NombreUnidad . '",
					 	"' . $unidad->NombreCorto . '",
					 	"' . $padre . '",
					 	"' . $estado . '",
				      	"' . $acciones . '"
  			    ]';
                if ($index !== array_key_last($unidades))
                    $datosJson .= ',';
            }
        } else {
            $datosJson = '{"data": [';
        }
        $datosJson .= ']}';
        return $datosJson;
    }

    public function actionListarUnidadesPadre()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            $unidades = Unidad::find()->where(['CodigoUnidadPadre' => null])->all();
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
        $unidades = Unidad::find()->where(['CodigoUnidadPadre' => $padre])->all();
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
                $unidad = new Unidad();
                $unidad->CodigoUnidad =  UnidadesDao::GenerarCodigoUnidad();
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
                $unidad = Unidad::findOne($_POST["codigounidad"]);
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
                $unidad = Unidad::findOne($_POST["codigounidad"]);
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
                $unidad = Unidad::findOne($_POST["codigounidad"]);
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
                $unidad = Unidad::findOne($_POST["codigounidad"]);
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