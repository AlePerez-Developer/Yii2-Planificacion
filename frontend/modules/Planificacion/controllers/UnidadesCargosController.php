<?php

namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\models\CargosDao;
use common\models\Cargo;
use common\models\SectorTrabajo;
use common\models\Unidad;
use common\models\UnidadCargo;
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
        if (($action->id == "listar-unidadescargos") || ($action->id == "listar-unidades-padre") || ($action->id == "listar-cargos")  )
            $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        return $this->render('unidadescargos');
    }

    public function actionListarUnidadescargos()
    {
        $Data = array();
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            $cargos = Cargo::find()->select(['CodigoCargo','NombreCargo','DescripcionCargo','ArchivoManualFunciones','CodigoSectorTrabajo','CodigoEstado','CodigoUsuario'])->where(['!=','CodigoEstado','E'])->orderBy('CodigoCargo')->asArray()->all();
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


}