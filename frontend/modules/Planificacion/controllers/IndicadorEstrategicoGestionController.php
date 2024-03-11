<?php

namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\models\IndicadorEstrategicoGestion;
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

        $programacion = IndicadorEstrategicoGestion::find()->select(['CodigoProgramacion','Gestion','IndicadorEstrategico','Meta'])
            ->where(['IndicadorEstrategico' => 1])
            ->orderBy('Gestion')
            ->asArray()
            ->all();
        return json_encode($programacion);
    }

}