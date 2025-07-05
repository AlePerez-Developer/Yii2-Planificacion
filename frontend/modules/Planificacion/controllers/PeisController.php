<?php
namespace app\modules\Planificacion\controllers;

use app\modules\Planificacion\models\IndicadorEstrategicoGestion;
use app\modules\Planificacion\formModels\PeiForm;
use app\modules\Planificacion\models\Pei;
use app\modules\Planificacion\services\PeiService;
use frontend\controllers\BaseController;
use yii\web\BadRequestHttpException;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use Mpdf\MpdfException;
use yii\web\Response;
use yii\db\Exception;
use Mpdf\Mpdf;
use Throwable;
use Yii;

class PeisController extends BaseController
{
    private PeiService $peiService;
    public function __construct($id, $module, PeiService $peiService, $config = [])
    {
        $this->peiService = $peiService;
        parent::__construct($id, $module, $config);
    }
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

    /**
     * @throws BadRequestHttpException
     */
    public function beforeAction($action): bool
    {
        if ($action->id == "listar-peis")
            $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionIndex(): string
    {
        return $this->render('peis');
    }

    public function actionListarPeis(): array
    {
        return $this->withTryCatch(fn() => $this->peiService->listarPeis(),'peis');
    }

    public function actionGuardarPei(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return $this->withTryCatch( function() {
            $request = Yii::$app->request;

            $form = new PeiForm();

            if (!$form->load($request->post(), '') || !$form->validate()) {
                Yii::$app->response->statusCode = 400;
                return [
                    'respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS'] ?? 'Error en el envio de datos',
                    'errors' => $form->getErrors(),
                ];
            }

            $resultado = $this->peiService->guardarPei($form);

            if (!$resultado['success']) {
                Yii::$app->response->statusCode = $resultado['code'];
                return [
                    'respuesta' => $resultado['mensaje'] ?? "ocurrio un error no definido en el procesado",
                    'errors' => $resultado['errors'],
                ];
            }

            return $resultado['success'];
        });
    }

    public function actionCambiarEstadoPei(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request = Yii::$app->request;

        $codigoPei = (int)$request->post('codigoPei');
        if (!$codigoPei) {
            Yii::$app->response->statusCode = 400;
            return [
                'respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS'] ?? 'Código PEI no enviado',
                'errors' => ['Se esperaba el campo codigoPei']
            ];
        }

        return $this->withTryCatch(function() use($codigoPei) {
            $resultado = $this->peiService->cambiarEstado($codigoPei);

            if (!$resultado['success']) {
                Yii::$app->response->statusCode = $resultado['code'];
                return [
                    'respuesta' => $resultado['mensaje'] ?? "ocurrio un error no definido en el procesado",
                    'errors' => $resultado['errors'],
                ];
            }

            return $resultado['estado'];
        }, 'estado');
    }


    public function actionEliminarPei(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request = Yii::$app->request;

        $codigoPei = (int)$request->post('codigoPei');
        if (!$codigoPei) {
            Yii::$app->response->statusCode = 400;
            return [
                'respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS'] ?? 'Código PEI no enviado',
                'errors' => ['Se esperaba el campo codigoPei']
            ];
        }

        return $this->withTryCatch(function() use($codigoPei) {
            $resultado = $this->peiService->eliminarPei($codigoPei);

            if (!$resultado['success']) {
                Yii::$app->response->statusCode = $resultado['code'];
                return [
                    'respuesta' => $resultado['mensaje'] ?? "ocurrio un error no definido en el procesado",
                    'errors' => $resultado['errors'],
                ];
            }

            return $resultado['success'];
        });
    }

    public function actionBuscarPei(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request = Yii::$app->request;

        $codigoPei = (int)$request->post('codigoPei');
        if (!$codigoPei) {
            Yii::$app->response->statusCode = 400;
            return [
                'respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS'] ?? 'Código PEI no enviado',
                'errors' => ['Se esperaba el campo codigoPei']
            ];
        }

        return $this->withTryCatch(function() use($codigoPei){
            $pei = $this->peiService->listarPei($codigoPei);

            if (!$pei) {
                Yii::$app->response->statusCode = 404;
                return [
                    'respuesta' => Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'] ?? 'PEI no encontrado',
                    'errors' => null,
                ];
            }

            return $pei->getAttributes(array('CodigoPei', 'DescripcionPei', 'FechaAprobacion', 'GestionInicio', 'GestionFin'));
        }, 'pei');
    }

    /**
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionActualizarPei()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request = Yii::$app->request;

        if (!($request->isAjax && $request->isPost)) {
            Yii::$app->response->statusCode = 400;
            return ['respuesta' => Yii::$app->params['ERROR_CABECERA'] ?? 'Cabecera inválida'];
        }

        try {
            $codigoPei = $request->post('codigoPei',null);
            if (!$codigoPei || !is_string($codigoPei)) {
                Yii::$app->response->statusCode = 400;
                return [
                    'respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS'] ?? 'Código PEI no enviado'
                ];
            }

            $form = new PeiForm();

            if (!$form->load($request->post(), '') || !$form->validate()) {
                Yii::$app->response->statusCode = 400;
                return [
                    'respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS'] ?? 'Error en el envio de datos',
                    'errors' => $form->getErrors(),
                ];
            }

            $pei = Pei::listOne($codigoPei);

            if (!$pei) {
                Yii::$app->response->statusCode = 404;
                return [
                    'respuesta' => Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'] ?? 'PEI no encontrado'
                ];
            }

            $nuevoInicio = (int) trim($form->gestionInicio);
            $nuevoFin    = (int) trim($form->gestionFin);

            $pei->DescripcionPei  = mb_strtoupper(trim($form->descripcionPei), 'UTF-8');
            $pei->FechaAprobacion = date("d/m/Y", strtotime($form->fechaAprobacion));
            $pei->GestionInicio   = $nuevoInicio;
            $pei->GestionFin      = $nuevoFin;

            // Validaciones personalizadas
            if (!$pei->validarGestionInicio($nuevoInicio)) {
                Yii::$app->response->statusCode = 400;
                return ['respuesta' => 'errorGestionInicio'];
            }
            if (!$pei->validarGestionFin($nuevoFin)) {
                Yii::$app->response->statusCode = 400;
                return ['respuesta' => 'errorGestionFin'];
            }

            if ($pei->exist()) {
                Yii::$app->response->statusCode = 409;
                return ['respuesta' => Yii::$app->params['ERROR_REGISTRO_EXISTE'] ?? 'El registro ya existe'];
            }

            if (!$pei->validate()) {
                Yii::$app->response->statusCode = 422;
                return ['respuesta' => Yii::$app->params['ERROR_VALIDACION_MODELO'], 'errors' => $pei->getErrors()];
            }

            $transaction = Yii::$app->db->beginTransaction();

            if ($pei->update() === false) {
                $transaction->rollBack();
                Yii::$app->response->statusCode = 500;
                return ['respuesta' => Yii::$app->params['ERROR_EJECUCION_SQL']];
            }

            // Eliminar programaciones fuera del rango
            $this->eliminarProgramacionesFueraDeRango($pei->CodigoPei, $nuevoInicio, $nuevoFin, $transaction);

            $transaction->commit();
            Yii::$app->response->statusCode = 200;
            return ['respuesta' => Yii::$app->params['PROCESO_CORRECTO'] ?? 'Actualización exitosa'];




        } catch (Exception $e) {
            Yii::error("Error en la base de datos: " . $e->getMessage(), __METHOD__);
            Yii::$app->response->statusCode = 500;
            return [
                'respuesta' => Yii::$app->params['ERROR_DB'] ?? 'Error en la base de datos'
            ];
        } catch (Throwable $e) {
            Yii::error("Error general: " . $e->getMessage(), __METHOD__);
            Yii::$app->response->statusCode = 500;
            return [
                'respuesta' => Yii::$app->params['ERROR_GENERAL'] ?? 'Error inesperado'
            ];
        }


        if (!(isset($_POST["codigoPei"]) && isset($_POST["descripcionPei"]) && isset($_POST["fechaAprobacion"])
            && isset($_POST["gestionInicio"]) && isset($_POST["gestionFin"]))) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS']]);
        }

        $pei = Pei::findOne($_POST["codigoPei"]);

        if (!$pei) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO']]);
        }

        $nuevoInicio = intval(trim($_POST["gestionInicio"]),10);
        $nuevoFin = intval(trim($_POST["gestionFin"]),10);
        $pei->DescripcionPei = mb_strtoupper(trim($_POST["descripcionPei"]),'utf-8');
        $pei->FechaAprobacion = date("d/m/Y", strtotime($_POST["fechaAprobacion"]));
        if ($pei->GestionInicio< $nuevoInicio ){
            if (!$pei->validarGestionInicio($nuevoInicio)) {
                return json_encode(['respuesta' => 'errorGestionInicio']);
            }
            $pei->GestionInicio = $nuevoInicio;
        } else {
            $pei->GestionInicio = $nuevoInicio;
        }

        if ($pei->GestionFin > $nuevoFin ){
            if (!$pei->validarGestionFin($nuevoFin)) {
                return json_encode(['respuesta' => 'errorGestionFin']);
            }
            $pei->GestionFin = $nuevoFin;
        } else {
            $pei->GestionFin = $nuevoFin;
        }

        if ($pei->exist()) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_REGISTRO_EXISTE']]);
        }
        if (!$pei->validate()) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_VALIDACION_MODELO']]);
        }

        $transaction = Pei::getDb()->beginTransaction();

        if ($pei->update() === false) {
            $transaction->rollBack();
            return json_encode(['respuesta' => Yii::$app->params['ERROR_EJECUCION_SQL']]);
        }

        $programaciones = IndicadorEstrategicoGestion::find()->select('*')->alias('ig')
            ->join('INNER JOIN','IndicadoresEstrategicos i', 'ig.IndicadorEstrategico = i.CodigoIndicador')
            ->join('INNER JOIN','ObjetivosEstrategicos o', 'i.ObjetivoEstrategico = o.CodigoObjEstrategico')
            ->where('(o.CodigoPei = :pei) and (ig.Gestion > :Gestion)',[':pei'=>$pei->CodigoPei,':Gestion'=>$nuevoFin])
            ->all();
        if ($programaciones){
            foreach ($programaciones as $programacion){
                if (!$programacion->delete()){
                    $transaction->rollBack();
                    break;
                }
            }
        }

        $programaciones = IndicadorEstrategicoGestion::find()->select('*')->alias('ig')
            ->join('INNER JOIN','IndicadoresEstrategicos i', 'ig.IndicadorEstrategico = i.CodigoIndicador')
            ->join('INNER JOIN','ObjetivosEstrategicos o', 'i.ObjetivoEstrategico = o.CodigoObjEstrategico')
            ->where('(o.CodigoPei = :pei) and (ig.Gestion < :Gestion)',[':pei'=>$pei->CodigoPei,':Gestion'=>$nuevoInicio])
            ->all();
        if ($programaciones){
            foreach ($programaciones as $programacion){
                if (!$programacion->delete()){
                    $transaction->rollBack();
                    break;
                }
            }
        }

        $transaction->commit();
        return json_encode(['respuesta' => Yii::$app->params['PROCESO_CORRECTO']]);
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


        $mpdf->WriteHTML($a);*/

        $mpdf->Output();
    }
}