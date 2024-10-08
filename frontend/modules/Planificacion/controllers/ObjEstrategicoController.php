<?php

namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\models\ObjetivoEstrategico;
use app\modules\Planificacion\dao\ObjEstrategicoDao;
use app\modules\Planificacion\models\Pei;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use common\models\Estado;
use yii\web\Controller;
use Mpdf\MpdfException;
use Throwable;
use Mpdf\Mpdf;
use Yii;

class ObjEstrategicoController extends Controller
{
    public function behaviors(): array
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
        $peis = Pei::find()->where(['CodigoEstado' => Estado::ESTADO_VIGENTE])->all();
        return $this->render('objEstrategico',['peis'=>$peis]);
    }

    public function actionListarObjs()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            $objs = ObjetivoEstrategico::find()->alias('O')
                ->select(['O.CodigoObjEstrategico','P.DescripcionPEI','P.GestionInicio','P.GestionFin','O.CodigoObjetivo','O.Objetivo','O.CodigoEstado','O.CodigoUsuario','P.FechaAprobacion'])
                ->join('INNER JOIN','PEIs P', 'O.CodigoPei = P.CodigoPei')
                ->where(['!=','O.CodigoEstado', Estado::ESTADO_ELIMINADO])->andWhere(['!=','P.CodigoEstado', Estado::ESTADO_ELIMINADO])
                ->orderBy('O.CodigoObjetivo')
                ->asArray()
                ->all();
            return json_encode($objs);
        } else
            return 'ERROR_CABECERA';
    }

    public function actionGuardarObjs()
    {
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_CABECERA']]);
        }
        if (!(isset($_POST["codigoPei"]) && isset($_POST["codigoObjetivo"]) && isset($_POST["objetivo"]))) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS']]);
        }

        $obj = new ObjetivoEstrategico();
        $obj->CodigoObjEstrategico = ObjEstrategicoDao::GenerarCodigoObjEstrategico();
        $obj->CodigoPei = $_POST["codigoPei"];
        $obj->CodigoObjetivo = trim($_POST["codigoObjetivo"]);
        $obj->Objetivo =  mb_strtoupper(trim($_POST["objetivo"]),'utf-8');
        $obj->CodigoEstado = Estado::ESTADO_VIGENTE;
        $obj->CodigoUsuario = Yii::$app->user->identity->CodigoUsuario;

        if ($obj->exist()) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_REGISTRO_EXISTE']]);
        }
        if (!$obj->validate()) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_VALIDACION_MODELO']]);
        }
        if (!$obj->save()) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_EJECUCION_SQL']]);
        }

        return json_encode(['respuesta' => Yii::$app->params['PROCESO_CORRECTO']]);
    }

    /**
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function actionCambiarEstadoObj()
    {
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_CABECERA']]);
        }
        if (!(isset($_POST["codigoObjEstrategico"]))) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS']]);
        }

        $obj = ObjetivoEstrategico::findOne($_POST["codigoObjEstrategico"]);

        if (!$obj) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO']]);
        }

        ($obj->CodigoEstado == Estado::ESTADO_VIGENTE)?$obj->CodigoEstado = Estado::ESTADO_CADUCO: $obj->CodigoEstado = Estado::ESTADO_VIGENTE;

        if ($obj->update() === false) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_EJECUCION_SQL']]);
        }

        return json_encode(['respuesta' => Yii::$app->params['PROCESO_CORRECTO']]);
    }

    /**
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionEliminarObj()
    {
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_CABECERA']]);
        }
        if (!(isset($_POST["codigoObjEstrategico"]) && $_POST["codigoObjEstrategico"] != "")) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS']]);
        }

        $obj = ObjetivoEstrategico::findOne($_POST["codigoObjEstrategico"]);

        if (!$obj) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO']]);
        }
        if ($obj->enUso()) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_REGISTRO_EN_USO']]);
        }

        $obj->CodigoEstado = Estado::ESTADO_ELIMINADO;

        if ($obj->update() === false) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_EJECUCION_SQL']]);
        }

        return json_encode(['respuesta' => Yii::$app->params['PROCESO_CORRECTO']]);
    }

    public function actionBuscarObj()
    {
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_CABECERA']]);
        }
        if (!(isset($_POST["codigoObjEstrategico"]) && $_POST["codigoObjEstrategico"] != "")) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS']]);
        }

        $obj = ObjetivoEstrategico::findOne($_POST["codigoObjEstrategico"]);

        if (!$obj) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO']]);
        }

        return json_encode(['respuesta' => Yii::$app->params['PROCESO_CORRECTO'], 'obj' => $obj->getAttributes(array('CodigoObjEstrategico','CodigoPei','CodigoObjetivo','Objetivo'))]);
    }

    /**
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionActualizarObj()
    {
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_CABECERA']]);
        }
        if (!(isset($_POST["codigoPei"]) && isset($_POST["codigoObjEstrategico"]) && isset($_POST["codigoObjetivo"]) && isset($_POST["objetivo"]))) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS']]);
        }

        $obj = ObjetivoEstrategico::findOne($_POST["codigoObjEstrategico"]);

        if (!$obj) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO']]);
        }

        $obj->CodigoPei = $_POST["codigoPei"];
        $obj->CodigoObjetivo = trim($_POST["codigoObjetivo"]);
        $obj->Objetivo =  mb_strtoupper(trim($_POST["objetivo"]),'utf-8');

        if ($obj->exist()) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_REGISTRO_EXISTE']]);
        }
        if (!$obj->validate()) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_VALIDACION_MODELO']]);
        }

        if ($obj->update() === false) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_EJECUCION_SQL']]);
        }

        return json_encode(['respuesta' => Yii::$app->params['PROCESO_CORRECTO']]);
    }

    public function actionVerificarCodigo()
    {
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return false;
        }
        if (!isset($_POST["codigo"]) && !isset($_POST["pei"]) && !isset($_POST["objetivoEstrategico"]) ) {
            return false;
        }

        $objetivoEstrategico = ObjetivoEstrategico::find()
            ->where(['CodigoObjetivo' => $_POST["codigo"], 'CodigoEstado' => Estado::ESTADO_VIGENTE])
            ->andWhere(['!=','CodigoObjEstrategico',$_POST["objetivoEstrategico"]])
            ->andWhere(['CodigoPei' => $_POST["pei"]])
            ->one();

        if ($objetivoEstrategico) {
            return false;
        }

        return true;
    }

    /**
     * @throws MpdfException
     */
    public function actionReporte()
    {
        $mpdf = new Mpdf();
        $mpdf->SetMargins(0, 0,32);
        /*$mpdf->SetHTMLHeader('
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


        $mpdf->WriteHTML($a);*/

        $mpdf->Output();
    }
}
