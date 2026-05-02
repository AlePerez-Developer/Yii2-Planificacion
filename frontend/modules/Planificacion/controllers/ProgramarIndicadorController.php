<?php

namespace app\modules\Planificacion\controllers;

use app\controllers\BaseController;

use app\modules\Planificacion\models\IndicadorEstrategico;

use app\modules\Planificacion\models\PeiGestion;
use app\modules\Planificacion\models\ProgramacionIndicadorGestion;

use Yii;

use yii\web\Controller;
use yii\web\Response;


/**
 * @noinspection PhpUnused
 */
class ProgramarIndicadorController extends Controller
{
    public function actionIndex()
    {
        return $this->render('Programar');
    }
    public function actionListarIndicadores($IdObj): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return IndicadorEstrategico::find()
            ->where(['IdObjEstrategico'=>$IdObj])
            ->asArray()->all();
    }

    public function actionListarGestiones(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return PeiGestion::find()->asArray()->all();
    }

    public function actionListarProgramacion(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $data = ProgramacionIndicadorGestion::find()
            ->alias('p')
            ->leftJoin('LlavesPresupuestarias l','l.IdLlavePresupuestaria=p.IdLlavePresupuestaria')
            ->where([
                'IdIndicadorEstrategico'=>$_POST['IdIndicadorEstrategico'],
                'IdGestion'=>$_POST['IdGestion']
            ])
            ->select(['p.*','l.Descripcion'])
            ->asArray()->all();

        return ['data'=>$data];
    }

    public function actionActualizarMeta(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = ProgramacionIndicadorGestion::findOne($_POST['id']);

        if(!$model){
            return ['success'=>false,'message'=>'No existe'];
        }

        $valor = $_POST['valor'];

        if(!is_numeric($valor) || $valor < 0){
            return ['success'=>false,'message'=>'Valor inválido'];
        }

        $model->MetaProgramada = (int)$valor;

        if($model->save()){
            return ['success'=>true];
        }

        return [
            'success'=>false,
            'message'=>'Error',
            'errors'=>$model->getErrors()
        ];
    }

}