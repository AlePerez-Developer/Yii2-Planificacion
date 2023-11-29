<?php

namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\models\ObjetivoEstrategico;
use app\modules\Planificacion\dao\ObjEstrategicoDao;
use app\modules\Planificacion\models\Pei;
use common\models\Estado;
use Mpdf\Mpdf;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use Throwable;
use Yii;

class ObjEstrategicoController extends Controller
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
        if ($action->id == "listar-objs")
            $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }


    public function actionIndex()
    {
        $peis = Pei::find()->where(['CodigoEstado'=>'V'])->all();
        return $this->render('ObjEstrategicos',['peis'=>$peis]);
    }

    public function actionListarObjs()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            $objs = ObjetivoEstrategico::find()->select(['CodigoObjEstrategico','PEIs.DescripcionPEI','PEIs.GestionInicio','PEIs.GestionFin','CodigoObjetivo','Objetivo','ObjetivosEstrategicos.CodigoEstado','ObjetivosEstrategicos.CodigoUsuario','PEIs.FechaAprobacion'])
                ->join('INNER JOIN','PEIs', 'ObjetivosEstrategicos.CodigoPei = PEIs.CodigoPei')
                ->where(['!=','ObjetivosEstrategicos.CodigoEstado','E'])->andWhere(['!=','PEIs.CodigoEstado','E'])
                ->orderBy('CodigoObjEstrategico')
                ->asArray()
                ->all();
        }
        return json_encode($objs);
    }

    public function actionGuardarObjs()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigoPei"]) && isset($_POST["codigoObjetivo"]) && isset($_POST["objetivo"])){
                $obj = new ObjetivoEstrategico();
                $obj->CodigoObjEstrategico = ObjEstrategicoDao::GenerarCodigoObjEstrategico();
                $obj->CodigoPei = $_POST["codigoPei"];
                $obj->CodigoObjetivo = trim($_POST["codigoObjetivo"]);
                $obj->Objetivo =  mb_strtoupper(trim($_POST["objetivo"]),'utf-8');
                $obj->CodigoEstado = Estado::ESTADO_VIGENTE;
                $obj->CodigoUsuario = Yii::$app->user->identity->CodigoUsuario;
                if ($obj->validate()){
                    if (!$obj->exist()){
                        if ($obj->save())
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
    public function actionCambiarEstadoObj()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigoObjEstrategico"])) {
                $obj = ObjetivoEstrategico::findOne($_POST["codigoObjEstrategico"]);
                if ($obj){
                    if ($obj->CodigoEstado == Estado::ESTADO_VIGENTE) {
                        $obj->CodigoEstado = Estado::ESTADO_CADUCO;
                    } else {
                        $obj->CodigoEstado = Estado::ESTADO_VIGENTE;
                    }
                    if ($obj->update()){
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
    public function actionEliminarObj()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigoObjEstrategico"]) && $_POST["codigoObjEstrategico"] != "") {
                $obj = ObjetivoEstrategico::findOne($_POST["codigoObjEstrategico"]);
                if ($obj){
                    if (!$obj->enUso()) {
                        $obj->CodigoEstado = Estado::ESTADO_ELIMINADO;
                        if ($obj->update()) {
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

    public function actionBuscarObj()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigoObjEstrategico"]) && $_POST["codigoObjEstrategico"] != "") {
                $obj = ObjetivoEstrategico::findOne($_POST["codigoObjEstrategico"]);
                if ($obj){
                    return json_encode($obj->getAttributes(array('CodigoObjEstrategico','CodigoPei','CodigoObjetivo','Objetivo')));
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

    public function actionActualizarObj()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigoPei"]) && isset($_POST["codigoObjEstrategico"]) && isset($_POST["codigoObjetivo"]) && isset($_POST["objetivo"])){
                $obj = ObjetivoEstrategico::findOne($_POST["codigoObjEstrategico"]);
                if ($obj){
                    $obj->CodigoPei = $_POST["codigoPei"];
                    $obj->CodigoObjetivo = trim($_POST["codigoObjetivo"]);
                    $obj->Objetivo =  mb_strtoupper(trim($_POST["objetivo"]),'utf-8');
                    if ($obj->validate()){
                        if (!$obj->exist()){
                            if ($obj->update() !== false) {
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
                    return "errorNoEncontrado";
                }
            } else {
                return 'errorEnvio';
            }
        } else {
            return "errorCabecera";
        }
    }

    public function actionReporte()
    {
        $mpdf = new Mpdf();
        $mpdf->SetMargins(0, 0,32);
        $mpdf->SetHTMLHeader('
            <table style="width: 100%" >
                <tr>
                    <td width="7%" style="border-right: 1px solid black" >
                        <img src="img/EscudoPNG.png" width="7%">
                    </td>
                    <td width="25%" style="font-size: 9px">Universidad Mayor Real y Pontificia de San Francisco Xavier de Chuquisaca</td>
                    <td width="53%" style="text-align: center; vertical-align: bottom; border-style: hidden" >Objetivos Estrategicos</td>
                    <td width="15%" style="text-align: center" >
                        <img src="img/logo400.png" width="15%">
                    </td>
                </tr>
            </table>
            <hr>
        ');
        $mpdf->SetHTMLFooter('
            <hr>
            <table width="100%">
                <tr>
                    <td width="33%"  style="font-size: 9px">'. Yii::$app->user->identity->Login .'('.Yii::$app->user->identity->CodigoUsuario.')'  .'</td>
                    <td width="33%"  style="font-size: 9px" align="center">{PAGENO}/{nbpg}</td>
                    <td width="33%" style="text-align: right; font-size: 9px">{DATE j-m-Y h:i:s}</td>
                </tr>
            </table>'
        );

        $a = '<table  width="100%" style="border: none; border-collapse: collapse "> <tr>' ;
        $a .= '<thead >';
        $a .=   '<tr>';
        $a .=       '<th width="10%" style="border-bottom: 1px solid black">cabecera</th>';
        $a .=       '<th width="50%" style="border-bottom: 1px solid black"> cabecera</th>';
        $a .=       '<th width="40%" style="border-bottom: 1px solid black">cabecera </th>';
        $a .=   '</tr>';
        $a .= ' </thead>';
        $a .= ' <tbody>';



        $a .= ' </tbody>';
        $a .= '</table>';


        $mpdf->WriteHTML($a);

        $mpdf->Output();
    }
}
