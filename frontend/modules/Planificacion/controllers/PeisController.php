<?php

namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\dao\PeiDao;
use app\modules\Planificacion\models\Pei;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use common\models\Estado;
use yii\web\Controller;
use Mpdf\Mpdf;
use Yii;


class PeisController extends Controller
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
        if ($action->id == "listar-Peis")
            $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        return $this->render('peis');
    }

    public function actionListarPeis()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            $peis = Pei::find()->select(['CodigoPei','DescripcionPei','FechaAprobacion','GestionInicio','GestionFin','CodigoEstado','CodigoUsuario'])
                ->where(['!=','CodigoEstado',Estado::ESTADO_ELIMINADO])
                ->orderBy('CodigoPei')
                ->asArray()
                ->all();
        }
        return json_encode($peis);
    }

    public function actionGuardarPei()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["descripcionPei"]) && isset($_POST["fechaAprobacion"]) && isset($_POST["gestionInicio"]) && isset($_POST["gestionFin"])) {
                $pei = new Pei();
                $pei->CodigoPei = PeiDao::generarCodigoPei();
                $pei->DescripcionPei = mb_strtoupper(trim($_POST["descripcionPei"]),'utf-8');
                $pei->FechaAprobacion = date("d/m/Y", strtotime($_POST["fechaAprobacion"]));
                $pei->GestionInicio = trim($_POST["gestionInicio"]);
                $pei->GestionFin = trim($_POST["gestionFin"]);
                $pei->CodigoEstado = Estado::ESTADO_VIGENTE;
                $pei->CodigoUsuario = Yii::$app->user->identity->CodigoUsuario;
                if ($pei->validate()) {
                    if (!$pei->exist()) {
                        if ($pei->save()) {
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

    public function actionCambiarEstadoPei()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigoPei"])) {
                $pei = Pei::findOne($_POST["codigoPei"]);
                if ($pei) {
                    if ($pei->CodigoEstado == Estado::ESTADO_VIGENTE) {
                        $pei->CodigoEstado = Estado::ESTADO_CADUCO;
                    } else {
                        $pei->CodigoEstado = Estado::ESTADO_VIGENTE;
                    }
                    if ($pei->update()) {
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

    public function actionEliminarPei()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigoPei"]) && $_POST["codigoPei"] != "") {
                $pei = Pei::findOne($_POST["codigoPei"]);
                if ($pei) {
                    if (!$pei->enUso()) {
                        $pei->CodigoEstado = Estado::ESTADO_ELIMINADO;
                        if ($pei->update()) {
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

    public function actionBuscarPei()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigoPei"]) && $_POST["codigoPei"] != "") {
                $pei = Pei::findOne($_POST["codigoPei"]);
                if ($pei) {
                    return json_encode($pei->getAttributes(array('CodigoPei', 'DescripcionPei', 'FechaAprobacion', 'GestionInicio', 'GestionFin')));
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

    public function actionActualizarPei()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            if (isset($_POST["codigoPei"]) && isset($_POST["descripcionPei"]) && isset($_POST["fechaAprobacion"]) && isset($_POST["gestionInicio"]) && isset($_POST["gestionFin"])) {
                $nuevoInicio = intval(trim($_POST["gestionInicio"]),10);
                $nuevoFin = intval(trim($_POST["gestionFin"]),10);

                $pei = Pei::findOne($_POST["codigoPei"]);
                if ($pei) {
                    $pei->DescripcionPei = mb_strtoupper(trim($_POST["descripcionPei"]),'utf-8');
                    $pei->FechaAprobacion = date("d/m/Y", strtotime($_POST["fechaAprobacion"]));

                    if ($pei->GestionInicio< $nuevoInicio ){
                        if ($pei->validarGestionInicio($nuevoInicio)) {
                            $pei->GestionInicio = $nuevoInicio;
                        } else {
                            return 'errorGestionInicio';
                        }
                    } else {
                        $pei->GestionInicio = $nuevoInicio;
                    }

                    if ($pei->GestionFin > $nuevoFin ){
                        if ($pei->validarGestionFin($nuevoFin)) {
                            $pei->GestionFin = $nuevoFin;
                        } else {
                            return 'errorGestionFin';
                        }
                    } else {
                        $pei->GestionFin = $nuevoFin;
                    }

                    if ($pei->validate()) {
                        if (!$pei->exist()) {
                            if ($pei->update() !== false) {
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
                    <td width="53%" style="text-align: center; vertical-align: bottom; border-style: hidden" >Este Titulo completo del reporte me soprende lo bien que se ve aunque depebdera de muchas cosas</td>
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
        $a .=       '<th width="10%" style="border-bottom: 1px solid black">Numero</th>';
        $a .=       '<th width="50%" style="border-bottom: 1px solid black"> Titulo 1</th>';
        $a .=       '<th width="40%" style="border-bottom: 1px solid black">Titulo 2 o subtitlo</th>';
        $a .=   '</tr>';
        $a .= ' </thead>';
        $a .= ' <tbody>';

        for ($i = 1; $i <= 200; $i++) {
            $a .= '<tr>';
            $a .= '<td style="text-align: center; border-bottom: 1px solid darkgray">'.$i.'</td>';
            $a .= '<td style="border-bottom: 1px solid darkgray"> es te es el campo uno de la tabla</td>';
            $a .= '<td style="border-bottom: 1px solid darkgray">campo 2 de la tavbla</td>';
            $a .= '</tr>';
        }

        $a .= ' </tbody>';
        $a .= '</table>';


        $mpdf->WriteHTML($a);

        $mpdf->Output();
    }
}