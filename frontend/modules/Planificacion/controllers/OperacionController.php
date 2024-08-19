<?php
namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\models\CategoriaIndicador;
use app\modules\Planificacion\models\IndicadorUnidad;
use app\modules\Planificacion\models\ObjetivoEspecifico;
use app\modules\Planificacion\models\ObjetivoEstrategico;
use app\modules\Planificacion\models\Programa;
use app\modules\Planificacion\models\TipoIndicador;
use app\modules\Planificacion\models\TipoResultado;
use common\models\Estado;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

class OperacionController extends Controller
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

    public function actionIndex()
    {
        $objsEstrategicos = ObjetivoEstrategico::find()->where(['CodigoEstado'=>'V', 'CodigoPei'=>1])->all();
        $programas = Programa::find()->where(['CodigoEstado'=>'V'])->all();
        return $this->render('Operacion',['objsEstrategicos'=>$objsEstrategicos, 'programas' => $programas]);
    }

}