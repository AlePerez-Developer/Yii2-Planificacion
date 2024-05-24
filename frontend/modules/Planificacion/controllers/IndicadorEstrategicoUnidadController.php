<?php

namespace app\modules\Planificacion\controllers;


use app\modules\Planificacion\models\IndicadorEstrategicoUnidad;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

class IndicadorEstrategicoUnidadController extends Controller
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

    public function actionListarIndicadoresEstrategicosUnidades()
    {
        if (\Yii::$app->request->isAjax && \Yii::$app->request->isPost) {
            $programacion = IndicadorEstrategicoUnidad::find()->alias('ieu')
                ->select(['ieu.ProgramacionGestion as Codigo','ieu.ProgramacionGestion', 'concat(u.Da,u.Ue) as Apertura', 'u.Da', 'u.Ue', 'u.Descripcion', 'ieu.Meta'])
                ->InnerJoin('Unidades u','ieu.Unidad = u.CodigoUnidad')
                ->where(['ieu.ProgramacionGestion' => $_POST["codigoGestion"]])
                ->orderBy('u.Da,u.Ue')
                ->asArray()
                ->all();
            return json_encode($programacion);
        } else
            return 'ERROR_CABECERA';
    }


}