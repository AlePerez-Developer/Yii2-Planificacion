<?php

namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\models\CargosDao;
use app\modules\Planificacion\models\UnidadesDao;
use common\models\Cargo;
use common\models\SectorTrabajo;
use common\models\Unidad;
use common\models\UnidadCargo;
use yii\base\BaseObject;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use Yii;

class UnidadesCargosController extends Controller
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
        if (($action->id == "listar-unidades-cargos") || ($action->id == "listar-unidades-padre") || ($action->id == "listar-cargos")  )
            $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        return $this->render('unidadescargos');
    }

    public function actionListarUnidadesCargos()
    {
        $Data = array();
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            $cargos = UnidadCargo::find()->select(['Unidad','Unidades.NombreUnidad','Cargos.NombreCargo','Cargos.CodigoSectorTrabajo','UnidadesCargos.CodigoEstado','UnidadesCargos.CodigoUsuario'])
                ->join('INNER JOIN','Unidades', 'UnidadesCargos.Unidad = Unidades.CodigoUnidad')
                ->join('INNER JOIN','Cargos', 'UnidadesCargos.Cargo = Cargos.CodigoCargo')
                ->where(['!=','UnidadesCargos.CodigoEstado','E'])
                ->orderBy('Unidad')
                ->asArray()->all();

            foreach($cargos as  $cargo) {
                array_push($Data, $cargo);
            }
        } 
        return json_encode($Data);
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

    public function actionListarCargos()
    {
        $Data = array();
        if (\Yii::$app->request->isAjax && \Yii::$app->request->isPost) {
            $cargos = Cargo::find()->select(['CodigoCargo','NombreCargo','CodigoUsuario'])->where(['!=','CodigoEstado','E'])->orderBy('CodigoCargo')->asArray()->all();
            foreach($cargos as  $cargo) {
                array_push($Data, $cargo);
            }
        } 
        return json_encode($Data);
    }

    public function actionGuardarUnidadCargo()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["unidad"]) && isset($_POST["cargo"])){
                $unidadcargo = new UnidadCargo();
                $unidadcargo->Unidad = strtoupper(trim($_POST["unidad"]));
                $unidadcargo->Cargo = strtoupper(trim($_POST["cargo"]));
                $unidadcargo->CodigoEstado = 'V';
                $unidadcargo->CodigoUsuario = Yii::$app->user->identity->CodigoUsuario;
                if ($unidadcargo->validate()){
                    if (!$unidadcargo->exist()){
                        if ($unidadcargo->save())
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


}