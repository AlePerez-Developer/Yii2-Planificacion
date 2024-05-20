<?php
namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\dao\ProgramaDao;
use app\modules\Planificacion\models\Programa;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use common\models\Estado;
use yii\web\Controller;
use Yii;

class ProgramaController extends Controller
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
        return $this->render('programa');
    }

    public function actionListarProgramas()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            $programas = Programa::find()->select(['CodigoPrograma','Codigo','Descripcion','CodigoEstado','CodigoUsuario'])
                ->where(['!=','CodigoEstado','E'])
                ->orderBy('Codigo')
                ->asArray()
                ->all();
            return json_encode($programas);
        } else
            return 'ERROR_CABECERA';
    }

    public function actionGuardarPrograma()
    {
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_CABECERA']]);
        }
        if (!(isset($_POST["codigo"]) && isset($_POST["descripcion"]))) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS']]);
        }

        $programa = new Programa();
        $programa->CodigoPrograma = ProgramaDao::GenerarCodigoPrograma();
        $programa->Codigo = trim($_POST["codigo"]);
        $programa->Descripcion = mb_strtoupper(trim($_POST["descripcion"]),'utf-8');
        $programa->CodigoEstado = Estado::ESTADO_VIGENTE;;
        $programa->CodigoUsuario = Yii::$app->user->identity->CodigoUsuario;

        if (!$programa->validate()) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_VALIDACION_MODELO']]);
        }
        if ($programa->exist()) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_REGISTRO_EXISTE']]);
        }
        if (!$programa->save()) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_EJECUCION_SQL']]);
        }

        return json_encode(['respuesta' => Yii::$app->params['PROCESO_CORRECTO']]);
    }

    public function actionCambiarEstadoPrograma()
    {
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_CABECERA']]);
        }
        if (!isset($_POST["codigoPrograma"])) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS']]);
        }

        $programa = Programa::findOne($_POST["codigoPrograma"]);

        if (!$programa) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO']]);
        }

        ($programa->CodigoEstado == Estado::ESTADO_VIGENTE)?$programa->CodigoEstado = Estado::ESTADO_CADUCO:$programa->CodigoEstado = Estado::ESTADO_VIGENTE;

        if ($programa->update() === false) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_EJECUCION_SQL']]);
        }

        return json_encode(['respuesta' => Yii::$app->params['PROCESO_CORRECTO']]);
    }

    public function actionEliminarPrograma()
    {
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_CABECERA']]);
        }
        if (!(isset($_POST["codigoPrograma"]) && $_POST["codigoPrograma"] != "")) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS']]);
        }

        $programa = Programa::findOne($_POST["codigoPrograma"]);

        if (!$programa) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO']]);
        }
        if ($programa->enUso()) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_REGISTRO_EN_USO']]);
        }

        $programa->CodigoEstado = Estado::ESTADO_ELIMINADO;

        if ($programa->update() === false) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_EJECUCION_SQL']]);
        }

        return json_encode(['respuesta' => Yii::$app->params['PROCESO_CORRECTO']]);
    }

    public function actionBuscarPrograma()
    {
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_CABECERA']]);
        }
        if (!(isset($_POST["codigoPrograma"]) && $_POST["codigoPrograma"] != "")) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS']]);
        }

        $programa = Programa::findOne($_POST["codigoPrograma"]);

        if (!$programa) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO']]);
        }

        return json_encode( ['respuesta' => Yii::$app->params['PROCESO_CORRECTO'], 'programa' =>  $programa->getAttributes(array('CodigoPrograma','Codigo','Descripcion'))]);
    }

    public function actionActualizarPrograma()
    {
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_CABECERA']]);
        }
        if (!(isset($_POST["codigoPrograma"]) && isset($_POST["codigo"]) && isset($_POST["descripcion"]))){
            return json_encode(['respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS']]);
        }

        $programa = Programa::findOne($_POST["codigoPrograma"]);

        if (!$programa) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO']]);
        }

        $programa->Codigo = trim($_POST["codigo"]);
        $programa->Descripcion = mb_strtoupper(trim($_POST["descripcion"]),'utf-8');

        if ($programa->exist()) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_REGISTRO_EXISTE']]);
        }
        if (!$programa->validate()) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_VALIDACION_MODELO']]);
        }
        if (!$programa->save()) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_EJECUCION_SQL']]);
        }

        return json_encode(['respuesta' => Yii::$app->params['PROCESO_CORRECTO']]);
    }
}