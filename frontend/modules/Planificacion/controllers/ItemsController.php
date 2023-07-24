<?php


namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\models\ItemsDao;
use common\models\Item;
use common\models\Cargo;
use common\models\UnidadSoa;
use common\models\SectorTrabajo;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use Yii;

class ItemsController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function beforeAction($action)
    {
        if ($action->id == "listar-items")
            $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        $sectoresTrabajo = SectorTrabajo::find()->all();
        return $this->render('items', [
            'sectoresTrabajo' => $sectoresTrabajo
        ]);
    }

    public function actionListarItems()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            $items = Item::find()->all();
            $datosJson = '{"data": [';
            $i=0;
            foreach($items as $index => $item) {
                if ($item->CodigoEstado == 'V') {
                    $colorEstado = "btn-success";
                    $textoEstado = "VIGENTE";
                    $estado = 'V';
                } else {
                    $colorEstado = "btn-danger";
                    $textoEstado = "NO VIGENTE";
                    $estado = "C";
                }

                $acciones = "<button class='btn btn-warning btn-sm  btnEditar' codigo='" . $item->NroItem . "'><i class='fa fa-pen'> Editar </i></button> ";
                $acciones .= "<button class='btn btn-danger btn-sm  btnEliminar' codigo='" . $item->NroItem . "' ><i class='fa fa-times'> Eliminar </i></button>";

                $estado = "<button class='btn " . $colorEstado . " btn-xs btnEstado' codigo='" . $item->NroItem . "' estado='" . $estado . "' >" . $textoEstado . "</button>";

                $datosJson .= '[
					 	"' . ($i) . '",
					 	"' . $item->NroItem . '",
					 	"' . $item->NroItemRrhh . '",
					 	"' . $item->NroItemPlanillas . '",
					 	"' . $item->cargo->sectorTrabajo->NombreSectorTrabajo . '",		
					 	"' . $item->unidad->NombreUnidad . '",					 				 	
					 	"' . $item->cargo->NombreCargo . '",
					 	"' . $item->cargoDependencia->NombreCargo . '",

					 	"' . $estado . '",
				      	"' . $acciones . '"
  			    ]';
                if ($index !== array_key_last($items))
                    $datosJson .= ',';
            }
        } else {
            $datosJson = '{"data": [';
        }
        $datosJson .= ']}';
        return $datosJson;
    }

    public function actionListarUnidades()
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

    public function actionListarCargos()
    {
        if (\Yii::$app->request->isAjax && \Yii::$app->request->isPost) {
            $opciones = "<option value=''>Seleccionar Cargo</option>";
            $cargos = Cargo::find()->where(['CodigoSectorTrabajo' => $_POST["sectortrabajo"]])->all();
            foreach ($cargos as $cargo) {
                $opciones .= "<option value='" . $cargo->CodigoCargo . "'>" . $cargo->NombreCargo . "</option>";
            }
            return $opciones;
        }
    }

    public function actionGuardarItem()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["itemplanillas"]) && isset($_POST["itemrrhh"]) && isset($_POST["codigocargo"]) && isset($_POST["codigocargodependencia"]) && isset($_POST["codigounidad"])){
                $item = new Item();
                $item->NroItem = ItemsDao::GenerarCodigoItem();
                $item->NroItemPlanillas = $_POST["itemplanillas"];
                $item->NroItemRrhh = $_POST["itemrrhh"];
                $item->CodigoCargo = $_POST["codigocargo"];
                $item->CodigoCargoDependencia = $_POST["codigocargodependencia"];
                $item->CodigoUnidad = $_POST["codigounidad"];
                $item->CodigoEstado = 'V';
                $item->CodigoUsuario = Yii::$app->user->identity->CodigoUsuario;
                if ($item->validate()){
                    if (!$item->exist()){
                        if ($item->save())
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

    public function actionCambiarEstadoItem()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["nroitem"])) {
                $item = Item::findOne($_POST["nroitem"]);
                if ($item){
                    if ($item->CodigoEstado == "V") {
                        $item->CodigoEstado = "C";
                    } else {
                        $item->CodigoEstado = "V";
                    }
                    if ($item->update()){
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

    public function actionEliminarItem()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["nroitem"]) && $_POST["nroitem"] != "") {
                $item = Item::findOne($_POST["nroitem"]);
                if ($item){
                    if (!$item->enUso()) {
                        if ($item->delete()) {
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

    public function actionBuscarItem()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["nroitem"]) && $_POST["nroitem"] != "") {
                $item = Item::findOne($_POST["nroitem"]);
                if ($item){
                    return json_encode($item->getAttributes(array('nroItem','NroItemPlanillas','NroItemRrhh','CodigoCargo','CodigoCargoDependencia','CodigoUnidad')));
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

    public function actionActualizarItem()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["nroitem"]) && isset($_POST["itemplanillas"]) && isset($_POST["itemrrhh"]) && isset($_POST["codigocargo"]) && isset($_POST["codigocargodependencia"]) && isset($_POST["codigounidad"])){
                $item = Item::findOne($_POST["nroitem"]);
                if ($item){
                    $item->NroItemPlanillas = $_POST["itemplanillas"];
                    $item->NroItemRrhh = $_POST["itemrrhh"];
                    $item->CodigoCargo = $_POST["codigocargo"];
                    $item->CodigoCargoDependencia = $_POST["codigocargodependencia"];
                    $item->CodigoUnidad = $_POST["codigounidad"];
                    if ($item->validate()){
                        if (!$item->exist()){
                            if ($item->update() !== false) {
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