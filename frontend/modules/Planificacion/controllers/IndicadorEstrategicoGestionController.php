<?php

namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\models\IndicadorEstrategicoGestion;
use app\modules\Planificacion\models\IndicadorEstrategico;
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
        if (\Yii::$app->request->isAjax && \Yii::$app->request->isPost) {
            $programacion = IndicadorEstrategicoGestion::find()->select(['CodigoProgramacionGestion','Gestion','IndicadorEstrategico','Meta'])
                ->where(['IndicadorEstrategico' => $_POST["indicador"]])
                ->orderBy('Gestion')
                ->asArray()
                ->all();
            return json_encode($programacion);
        } else
            return 'ERROR_CABECERA';
    }

    /**
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionGuardarMetaProgramada()
    {
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_CABECERA']]);
        }
        if (!(isset($_POST["codigo"]) && $_POST["codigo"] != "" &&
            isset($_POST["metaProgramada"]) && $_POST["metaProgramada"] != "")) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS']]);
        }

        $programacion = IndicadorEstrategicoGestion::findOne($_POST["codigo"]);

        if (!$programacion) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO']]);
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
                return json_encode(["respuesta" => 'errorMeta']);
            }
        }

        if (!$programacion->validate()) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_VALIDACION_MODELO']]);
        }

        if (!$programacion->update()) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_EJECUCION_SQL']]);
        }

        return json_encode(["respuesta" => "ok",'metaProg'=>$_POST["metaProgramada"] + $programado]);
    }
}