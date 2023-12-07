<?php
namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\models\ObjetivoInstitucional;
use app\modules\Planificacion\models\CategoriaIndicador;
use app\modules\Planificacion\models\ObjetivoEspecifico;
use app\modules\Planificacion\models\TipoArticulacion;
use app\modules\Planificacion\models\IndicadorUnidad;
use app\modules\Planificacion\models\TipoResultado;
use app\modules\Planificacion\models\TipoIndicador;
use app\modules\Planificacion\dao\IndicadorDao;
use app\modules\Planificacion\models\Indicador;
use app\modules\Planificacion\models\Actividad;
use app\modules\Planificacion\models\Programa;
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

    public function actionIndex()
    {
        $objInsitucionales = ObjetivoInstitucional::find()->alias('oi')
            ->select(['oi.CodigoObjInstitucional','concat(oe.CodigoObjetivo, char(45) , oi.CodigoCOGE) as Codigo, oi.Objetivo'])
            ->join('INNER JOIN','ObjetivosEstrategicos oe','oi.CodigoObjEstrategico = oe.CodigoObjEstrategico')
            ->where(['oi.CodigoEstado' => 'V'])->andWhere(['oe.CodigoEstado' => 'V'])
            ->asArray()->all();
        $programas = Programa::find()->where(['CodigoEstado'=>'V'])->all();
        $tiposArticulaciones = TipoArticulacion::find()->where(['CodigoEstado'=>'V'])->all();
        $tiposResultados = TipoResultado::find()->where(['CodigoEstado'=>'V'])->all();
        $tiposIndicadores = TipoIndicador::find()->where(['CodigoEstado'=>'V'])->all();
        $categoriasIndicadores = CategoriaIndicador::find()->where(['CodigoEstado'=>'V'])->all();
        $indicadoresUnidades = IndicadorUnidad::find()->where(['CodigoEstado'=>'V'])->all();
        return $this->render('Indicadores',[
            'objInsitucionales' => $objInsitucionales,
            'programas' => $programas,
            'Articulaciones'=> $tiposArticulaciones,
            'Resultados'=> $tiposResultados,
            'Tipos'=> $tiposIndicadores,
            'Categorias'=> $categoriasIndicadores,
            'Unidades'=> $indicadoresUnidades
        ]);
    }

    public function actionListarIndicadores()
    {
        $Data = array();
        if (\Yii::$app->request->isAjax && \Yii::$app->request->isPost) {
            $indicadores = Indicador::find()->alias('I')->select([
                'I.*',
                'Oi.CodigoCOGE as CodigoInstitucional', 'Oi.Objetivo as ObjetivoInstitucional',
                'Oe.CodigoCOGE as CodigoEspecifico', 'Oe.Objetivo as ObjetivoEspecifico',
                'P.Codigo as CodigoPrograma', 'P.Descripcion as DescripcionPrograma',
                'A.Codigo as CodigoActividad', 'A.Descripcion as DescripcionActividad',
                'Ta.Descripcion as ArticulacionDescripcion',
                'Tr.Descripcion as ResultadoDescripcion',
                'Ti.Descripcion as TipoDescripcion',
                'Ci.Descripcion as CategoriaDescripcion',
                'U.Descripcion as UnidadDescripcion'
            ])
                ->join('INNER JOIN','ObjetivosEspecificos Oe', 'I.ObjetivoEspecifico = Oe.CodigoObjEspecifico')
                ->join('INNER JOIN','ObjetivosInstitucionales Oi', 'Oi.CodigoObjInstitucional = Oe.CodigoObjInstitucional')
                ->join('INNER JOIN','Actividades A', 'I.Actividad = A.CodigoActividad')
                ->join('INNER JOIN','Programas P', 'P.CodigoPrograma = A.Programa')
                ->join('INNER JOIN','TiposArticulaciones Ta', 'I.Articulacion = Ta.CodigoTipo')
                ->join('INNER JOIN','TiposResultados Tr', 'I.Resultado = Tr.CodigoTipo')
                ->join('INNER JOIN','TiposIndicadores Ti', 'I.TipoIndicador = Ti.CodigoTipo')
                ->join('INNER JOIN','CategoriasIndicadores Ci', 'I.Categoria = Ci.CodigoCategoria')
                ->join('INNER JOIN','IndicadoresUnidades U', 'I.Unidad = U.CodigoTipo')
                ->where(['!=','I.CodigoEstado','E'])->andWhere(['Gestion' => 2024])
                ->andWhere(['!=','Oe.CodigoEstado','E'])->andWhere(['!=','Oi.CodigoEstado','E'])
                ->andWhere(['!=','A.CodigoEstado','E'])->andWhere(['!=','P.CodigoEstado','E'])
                ->andWhere(['!=','Ta.CodigoEstado','E'])->andWhere(['!=','Tr.CodigoEstado','E'])->andWhere(['!=','Ti.CodigoEstado','E'])->andWhere(['!=','Ci.CodigoEstado','E'])->andWhere(['!=','U.CodigoEstado','E'])
                ->orderBy('I.Codigo,i.Articulacion')->asArray()->all();
            foreach($indicadores as  $indicador) {
                array_push($Data, $indicador);
            }
        }
        return json_encode($Data);
    }

    public function actionListarObjsespecificos()
    {
        $Data = array();
        if (\Yii::$app->request->isAjax && \Yii::$app->request->isPost && isset($_POST["codigo"])) {
            $objs = ObjetivoEspecifico::find()->select(['CodigoObjEspecifico','CodigoCOGE','Objetivo'])
                ->where(['CodigoObjInstitucional'=>$_POST["codigo"]])
                ->andWhere(['!=','CodigoEstado','E'])->orderBy('CodigoObjEspecifico')->asArray()->all();
            foreach($objs as  $obj) {
                array_push($Data, $obj);
            }
        }
        return json_encode($Data);
    }

    public function actionListarActividades()
    {
        $Data = array();
        if (\Yii::$app->request->isAjax && \Yii::$app->request->isPost && isset($_POST["codigo"])) {
            $actividades = Actividad::find()->select(['CodigoActividad','Codigo','Descripcion'])
                ->where(['Programa'=>$_POST["codigo"]])
                ->andWhere(['!=','CodigoEstado','E'])->orderBy('Codigo')->asArray()->all();
            foreach($actividades as  $actividad) {
                array_push($Data, $actividad);
            }
        }
        return json_encode($Data);
    }

    public function actionGuardarIndicador()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["objEspecifico"]) && isset($_POST["actividad"])
                && isset($_POST["codigo"])  && isset($_POST["descripcion"])
                && isset($_POST["articulacion"]) && isset($_POST["resultado"])
                && isset($_POST["tipoindicador"]) && isset($_POST["categoria"]) && isset($_POST["unidad"])
            ){
                $indicador = new Indicador();
                $indicador->CodigoIndicador = IndicadorDao::GenerarCodigoIndicador();
                $indicador->ObjetivoEspecifico = $_POST["objEspecifico"];
                $indicador->Actividad = $_POST["actividad"];
                $indicador->Codigo = trim($_POST["codigo"]);
                $indicador->Descripcion = mb_strtoupper(trim($_POST["descripcion"]),'utf-8');
                $indicador->Gestion = 2024; //Yii::$app->user->identity->Gestion
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
                $indicador = Indicador::find()->alias('I')->select([
                    'I.CodigoIndicador','I.Codigo','I.Descripcion','I.Articulacion','I.Resultado','I.TipoIndicador','I.Categoria','I.Unidad',
                    'I.ObjetivoEspecifico', 'I.Actividad',
                    'Oi.CodigoObjInstitucional', 'P.CodigoPrograma'
                ])
                    ->join('INNER JOIN','ObjetivosEspecificos Oe', 'I.ObjetivoEspecifico = Oe.CodigoObjEspecifico')
                    ->join('INNER JOIN','ObjetivosInstitucionales Oi', 'Oi.CodigoObjInstitucional = Oe.CodigoObjInstitucional')
                    ->join('INNER JOIN','Actividades A', 'I.Actividad = A.CodigoActividad')
                    ->join('INNER JOIN','Programas P', 'P.CodigoPrograma = A.Programa')
                    ->where(['I.CodigoIndicador' => $_POST["codigoindicador"] ])
                    ->asArray()->one();
                if ($indicador){
                    return json_encode($indicador);
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
            if (isset($_POST["codigoindicador"]) && $_POST["codigoindicador"] != ""
                && isset($_POST["objEspecifico"]) && isset($_POST["actividad"])
                && isset($_POST["codigo"]) && isset($_POST["descripcion"])
                && isset($_POST["articulacion"]) && isset($_POST["resultado"])
                && isset($_POST["tipoindicador"]) && isset($_POST["categoria"]) && isset($_POST["unidad"])
            ){
                $indicador = Indicador::findOne($_POST["codigoindicador"]);
                if ($indicador){
                    $indicador->ObjetivoEspecifico = $_POST["objEspecifico"];
                    $indicador->Actividad = $_POST["actividad"];
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
