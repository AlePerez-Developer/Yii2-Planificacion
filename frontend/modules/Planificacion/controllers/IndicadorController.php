<?php
namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\models\CategoriaIndicador;
use app\modules\Planificacion\models\TipoArticulacion;
use app\modules\Planificacion\models\IndicadorUnidad;
use app\modules\Planificacion\models\TipoResultado;
use app\modules\Planificacion\models\TipoIndicador;
use app\modules\Planificacion\dao\IndicadorDao;
use app\modules\Planificacion\models\Indicador;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use Yii;

class IndicadorController extends Controller
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

    public function beforeAction($action)
    {
        if ($action->id == "listar-indicadores")
            $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        $tiposArticulaciones = TipoArticulacion::find()->where(['CodigoEstado'=>'V'])->all();
        $tiposResultados = TipoResultado::find()->where(['CodigoEstado'=>'V'])->all();
        $tiposIndicadores = TipoIndicador::find()->where(['CodigoEstado'=>'V'])->all();
        $categoriasIndicadores = CategoriaIndicador::find()->where(['CodigoEstado'=>'V'])->all();
        $indicadoresUnidades = IndicadorUnidad::find()->where(['CodigoEstado'=>'V'])->all();
        return $this->render('Indicadores',[
            'Articulaciones'=>$tiposArticulaciones,
            'Resultados'=>$tiposResultados,
            'Tipos'=>$tiposIndicadores,
            'Categorias'=>$categoriasIndicadores,
            'Unidades'=>$indicadoresUnidades
        ]);
    }

    public function actionListarIndicadores()
    {
        $Data = array();
        if (\Yii::$app->request->isAjax && \Yii::$app->request->isPost) {
            $indicadores = Indicador::find()->select([
                'Indicadores.*',
                'TiposArticulaciones.Descripcion as ArticulacionDescripcion',
                'TiposResultados.Descripcion as ResultadoDescripcion',
                'TiposIndicadores.Descripcion as TipoDescripcion',
                'CategoriasIndicadores.Descripcion as CategoriaDescripcion',
                'IndicadoresUnidades.Descripcion as UnidadDescripcion'
            ])
                ->join('INNER JOIN','TiposArticulaciones', 'Indicadores.Articulacion = TiposArticulaciones.CodigoTipo')
                ->join('INNER JOIN','TiposResultados', 'Indicadores.Resultado = TiposResultados.CodigoTipo')
                ->join('INNER JOIN','TiposIndicadores', 'Indicadores.TipoIndicador = TiposIndicadores.CodigoTipo')
                ->join('INNER JOIN','CategoriasIndicadores', 'Indicadores.Categoria = CategoriasIndicadores.CodigoCategoria')
                ->join('INNER JOIN','IndicadoresUnidades', 'Indicadores.Unidad = IndicadoresUnidades.CodigoTipo')
                ->where(['!=','Indicadores.CodigoEstado','E'])
                ->andWhere(['!=','TiposArticulaciones.CodigoEstado','E'])->andWhere(['!=','TiposResultados.CodigoEstado','E'])->andWhere(['!=','TiposIndicadores.CodigoEstado','E'])->andWhere(['!=','CategoriasIndicadores.CodigoEstado','E'])->andWhere(['!=','IndicadoresUnidades.CodigoEstado','E'])
                ->orderBy('Indicadores.Articulacion','Indicadores.Codigo')->asArray()->all();
            foreach($indicadores as  $indicador) {
                array_push($Data, $indicador);
            }
        }
        return json_encode($Data);
    }

    public function actionGuardarIndicador()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigo"]) && isset($_POST["descripcion"])
                && isset($_POST["articulacion"]) && isset($_POST["resultado"])
                && isset($_POST["tipoindicador"]) && isset($_POST["categoria"]) && isset($_POST["unidad"])
            ){
                $indicador = new Indicador();
                $indicador->CodigoIndicador = IndicadorDao::GenerarCodigoIndicador();
                $indicador->Codigo = trim($_POST["codigo"]);
                $indicador->Descripcion = mb_strtoupper(trim($_POST["descripcion"]),'utf-8');
                $indicador->Articulacion = trim($_POST["articulacion"]);
                $indicador->Resultado = trim($_POST["resultado"]);
                $indicador->TipoIndicador = trim($_POST["tipoindicador"]);
                $indicador->Categoria = trim($_POST["categoria"]);
                $indicador->Unidad = trim($_POST["unidad"]);
                $indicador->CodigoEstado = 'V';
                $indicador->CodigoUsuario = 'BGC';//Yii::$app->user->identity->CodigoUsuario;
                if ($indicador->validate()){
                    if (!$indicador->exist()){
                        if ($indicador->save())
                        {
                            return "ok";
                        } else {
                            return "errorSql";
                        }
                    } else {
                        return "errorExiste";
                    }
                } else {
                    return "errorValidacion";
                }
            } else {
                return 'errorEnvio';
            }
        } else {
            return "errorCabecera";
        }
    }

    /**
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function actionCambiarEstadoIndicador()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigoindicador"])) {
                $indicador = Indicador::findOne($_POST["codigoindicador"]);
                if ($indicador){
                    if ($indicador->CodigoEstado == "V") {
                        $indicador->CodigoEstado = "C";
                    } else {
                        $indicador->CodigoEstado = "V";
                    }
                    if ($indicador->update()){
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

    /**
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionEliminarIndicador()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigoindicador"]) && $_POST["codigoindicador"] != "") {
                $indicador = Indicador::findOne($_POST["codigoindicador"]);
                if ($indicador){
                    if (!$indicador->enUso()) {
                        $indicador->CodigoEstado = 'E';
                        if ($indicador->update()) {
                            return "ok";
                        } else {
                            return "errorSql";
                        }
                    } else {
                        return "errorEnUso";
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

    public function actionBuscarIndicador()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigoindicador"]) && $_POST["codigoindicador"] != "") {
                $indicador = Indicador::findOne($_POST["codigoindicador"]);
                if ($indicador){
                    return json_encode($indicador->getAttributes(array('CodigoIndicador','Codigo','Descripcion','Articulacion','Resultado','TipoIndicador','Categoria','Unidad')));
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

    public function actionActualizarIndicador()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigoindicador"]) && isset($_POST["codigo"]) && isset($_POST["descripcion"])
                && isset($_POST["articulacion"]) && isset($_POST["resultado"])
                && isset($_POST["tipoindicador"]) && isset($_POST["categoria"]) && isset($_POST["unidad"])
            ){
                $indicador = Indicador::findOne($_POST["codigoindicador"]);
                if ($indicador){
                    $indicador->Codigo = trim($_POST["codigo"]);
                    $indicador->Descripcion = mb_strtoupper(trim($_POST["descripcion"]),'utf-8');
                    $indicador->Articulacion = trim($_POST["articulacion"]);
                    $indicador->Resultado = trim($_POST["resultado"]);
                    $indicador->TipoIndicador = trim($_POST["tipoindicador"]);
                    $indicador->Categoria = trim($_POST["categoria"]);
                    $indicador->Unidad = trim($_POST["unidad"]);
                    if ($indicador->validate()){
                        if ($indicador->update() !== false) {
                            return "ok";
                        } else {
                            return "errorSql";
                        }
                    } else {
                        return "errorValidacion";
                    }
                } else {
                    return "errorNoEncontrado";
                }
            } else {
                return 'errorEnvio';
            }
        } else {
            return "errorCabecera";
        }
    }
}
