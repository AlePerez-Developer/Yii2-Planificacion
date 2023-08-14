<?php
namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\dao\IndicadorAperturaDao;
use app\modules\Planificacion\models\IndicadorApertura;
use app\modules\Planificacion\models\Unidad;
use yii\base\BaseObject;
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
                    'isnull(Ia.CodigoIndicadorApertura,0) as check', 'isnull(Ia.MetaObligatoria,0) as MetaObligatoria'
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

    public function actionActualizarIndicadorUnidad()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigoIndicador"]) && isset($_POST["codigoUnidad"]) ) {
                $data = IndicadorApertura::findOne(['Indicador' =>$_POST["codigoIndicador"], 'Apertura' => $_POST["codigoUnidad"] ]);
                if ($data){
                    if ($data->delete())
                    {
                        return "ok";
                    } else {
                        return "errorSql";
                    }
                } else {
                    $data = new IndicadorApertura();
                    $data->CodigoIndicadorApertura = IndicadorAperturaDao::GenerarCodigoIndicadorApertura();
                    $data->Indicador = $_POST["codigoIndicador"];
                    $data->Apertura = $_POST["codigoUnidad"];
                    $data->MetaObligatoria = 0;
                    $data->CodigoEstado = 'V';
                    $data->CodigoUsuario = 'BGC';//Yii::$app->user->identity->CodigoUsuario;
                    if ($data->save())
                    {
                        return "ok";
                    } else {
                        return "errorSql";
                    }
                }
            } else {
                return "errorEnvio";
            }
        } else {
            return "errorCabecera";
        }
    }

    public function actionActualizarMetaUnidad()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigoIndicador"]) && isset($_POST["codigoUnidad"]) && isset($_POST["meta"]) ) {
                $data = IndicadorApertura::findOne(['Indicador' =>$_POST["codigoIndicador"], 'Apertura' => $_POST["codigoUnidad"] ]);
                if ($data){
                    $data->MetaObligatoria = $_POST["meta"];
                    if ($data->update())
                    {
                        return "ok";
                    } else {
                        return "errorSql";
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