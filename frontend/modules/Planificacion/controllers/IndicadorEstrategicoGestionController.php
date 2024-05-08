<?php

namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\models\IndicadorEstrategico;
use app\modules\Planificacion\models\IndicadorEstrategicoGestion;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use Throwable;
use Yii;

class IndicadorEstrategicoGestionController extends Controller
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

    public function actionListarIndicadoresEstrategicosGestiones()
    {
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return "errorCabecera";
        }
        if (!(isset($_POST["indicador"]) && $_POST["indicador"] != "")) {
            return "errorEnvio";
        }

        $programacion = IndicadorEstrategicoGestion::find()->select(['CodigoProgramacionGestion','Gestion','IndicadorEstrategico','Meta'])
            ->where(['IndicadorEstrategico' => $_POST["indicador"]])
            ->orderBy('Gestion')
            ->asArray()
            ->all();

        if (!$programacion) {
            return 'errorNoEncontrado';
        }

        return json_encode($programacion);
    }

    /**
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionGuardarMetaProgramada()
    {
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            json_encode(["rta" => "errorCabecera"]);
        }
        if (!(isset($_POST["codigo"]) && $_POST["codigo"] != "" &&
            isset($_POST["metaProgramada"]) && $_POST["metaProgramada"] != "")) {
            return json_encode(["rta" => "errorEnvio"]);
        }

        $programacion = IndicadorEstrategicoGestion::findOne($_POST["codigo"]);

        if (!$programacion) {
            return json_encode(["rta" => 'errorNoEncontrado']);
        }

        $indicador = IndicadorEstrategico::findOne($programacion->IndicadorEstrategico);
        $programado =  IndicadorEstrategicoGestion::find()
            ->where(['IndicadorEstrategico' => $programacion->IndicadorEstrategico])
            ->andWhere(['!=','CodigoProgramacionGestion',$_POST["codigo"]])
            ->sum('meta');
        if ($indicador->Meta == 0){
            $programacion->Meta = $_POST["metaProgramada"];
        } else {
            if ($indicador->Meta >= ($_POST["metaProgramada"] + $programado)){
                $programacion->Meta = $_POST["metaProgramada"];
            } else {
                return json_encode(["rta" => 'errorMeta']);
            }
        }

        if (!$programacion->validate()) {
            return json_encode(["rta" => "errorValidacion"]);
        }

        if (!$programacion->update()) {
            return json_encode(["rta" => "errorSql"]);
        }

        return json_encode(["rta" => "ok",'metaProg'=>$_POST["metaProgramada"] + $programado]);
    }
}