<?php
namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\models\Unidad;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use Yii;

class IndicadorAperturaController extends Controller
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

    public function actionListarUnidades()
    {
        $Data = array();
        if (\Yii::$app->request->isAjax && \Yii::$app->request->isPost && isset($_POST['indicador'])) {
            if ($_POST['indicador'] !== '0'){
                $unidades = Unidad::find()->alias('U')->select([
                    'U.*',
                    'isnull(Ia.CodigoIndicadorApertura,0) as check', 'Ia.MetaObligatoria'
                ])
                    ->join('LEFT JOIN','IndicadoresAperturas Ia', 'Ia.Apertura = U.CodigoUnidad and Ia.Indicador = ' . $_POST['indicador'] )
                    ->where(['U.CodigoEstado' => 'V'])->andWhere(['U.Ue' => '000'])
                    ->orderBy('U.Da')->asArray()->all();
                foreach($unidades as  $unidad) {
                    array_push($Data, $unidad);
                }
            }
        }
        return json_encode($Data);

    }

}