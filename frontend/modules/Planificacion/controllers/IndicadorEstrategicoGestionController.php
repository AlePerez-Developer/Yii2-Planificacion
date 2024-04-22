<?php

namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\models\IndicadorEstrategico;
use app\modules\Planificacion\models\IndicadorEstrategicoGestion;
use Yii;
use yii\db\Query;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

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
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["indicador"]) && $_POST["indicador"] != "") {
                $programacion = IndicadorEstrategicoGestion::find()->select(['CodigoProgramacion','Gestion','IndicadorEstrategico','Meta'])
                    ->where(['IndicadorEstrategico' => $_POST["indicador"]])
                    ->orderBy('Gestion')
                    ->asArray()
                    ->all();
                if ($programacion) {
                    return json_encode($programacion);
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
     * @throws \Throwable
     * @throws StaleObjectException
     */
    public function actionGuardarMetaProgramada()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigo"]) && $_POST["codigo"] != "" &&
                isset($_POST["metaProgramada"]) && $_POST["metaProgramada"] != "") {
                $codigo = $_POST["codigo"];
                $nuevaMeta = $_POST["metaProgramada"];
                $programacion = IndicadorEstrategicoGestion::findOne($codigo);
                if ($programacion) {
                    $indicador = IndicadorEstrategico::findOne($programacion->IndicadorEstrategico);
                    $programado =  IndicadorEstrategicoGestion::find()
                        ->where(['IndicadorEstrategico' => $programacion->IndicadorEstrategico])
                        ->andWhere(['!=','CodigoProgramacion',$codigo])
                        ->sum('meta');
                    if ($indicador->Meta == 0){
                        $programacion->Meta = $nuevaMeta;
                    } else {
                        if ($indicador->Meta >= ($nuevaMeta + $programado)){
                            $programacion->Meta = $nuevaMeta;
                        } else {
                          return 'errorMeta';
                        }
                    }
                    if ($programacion->validate()){
                        if ($programacion->update() !== false) {
                            return "ok";
                        } else {
                            return "errorSql";
                        }
                    } else {
                        return "errorValidacion";
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

}