<?php

namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\models\IndicadorEstrategicoGestion;
use Yii;
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

}