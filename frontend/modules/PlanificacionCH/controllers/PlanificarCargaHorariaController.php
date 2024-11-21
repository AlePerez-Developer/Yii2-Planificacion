<?php

namespace app\modules\PlanificacionCH\controllers;


use app\modules\PlanificacionCH\dao\CarrerasDao;
use app\modules\PlanificacionCH\dao\FacultadesDao;
use app\modules\PlanificacionCH\dao\PlanesEstudiosDao;
use app\modules\PlanificacionCH\models\Facultad;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;

class PlanificarCargaHorariaController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $r = Facultad::find()->select(['CodigoFacultad','NombreFacultad'])->orderBy('CodigoFacultad')
            ->asArray()
            ->all();
        $listaFacultades = ArrayHelper::map(FacultadesDao::listaFacultades(), 'CodigoFacultad', 'NombreFacultad');
        return $this->render('planificarcargahoraria', ['facultades' => $listaFacultades,'a' => $r]);
    }

    public function actionListarCarreras(){
        if (\Yii::$app->request->isAjax && \Yii::$app->request->isPost) {
            $opciones = "<option value=''>Selecionar Carrera</option>";
            $codigoFacultad = $_POST["facultad"];
            $carreras = CarrerasDao::listaCarrerasFacultad($codigoFacultad);
            foreach ($carreras as $carrera) {
                $opciones .= "<option value='" . $carrera->CodigoCarrera . "'>" . $carrera->NombreCarrera . "</option>";
            }
            return $opciones;
        }
    }

    public function actionListarSedes()
    {
        if (\Yii::$app->request->isAjax && \Yii::$app->request->isPost) {
            $opciones = "<option value=''>Selecionar Sede</option>";
            $codigoCarrera = $_POST["carrera"];
            $sedes = CarrerasDao::listaSedesCarrera($codigoCarrera);
            foreach ($sedes as $sede) {
                $opciones .= "<option value='" . $sede->CodigoSede . "' >" . $sede->NombreSede . "</option>";
            }
            return $opciones;
        }
    }

    public function actionListarPlanesEstudios()
    {
        if (\Yii::$app->request->isAjax && \Yii::$app->request->isPost) {
            $opciones = "<option value=''>Selecionar Plan</option>";
            $sede = $_POST["carrera"];
            $planesEstudios = PlanesEstudiosDao::listaPlanesEstudioCarrera($sede);
            foreach ($planesEstudios as $planEstudio) {
                $opciones .= "<option value='" . $planEstudio->NumeroPlanEstudios . "'>" . $planEstudio->NumeroPlanEstudios . "</option>";
            }
            return $opciones;
        }
    }

    public function actionListarCursos()
    {
        if (\Yii::$app->request->isAjax && \Yii::$app->request->isPost) {
            $opciones = "<option value=''>Selecionar Curso</option>";
            $codigoCarrera = $_POST["codigocarrera"];
            $numeroPlanEstudios = $_POST["numeroplanestudios"];
            $cursos = PlanesEstudiosDao::listaCursos($codigoCarrera, $numeroPlanEstudios);
            foreach ($cursos as $curso) {
                $opciones .= "<option value='" . $curso->Curso . "'>" . $curso->Curso . "</option>";
            }
            return $opciones;
        }
    }

    public function actionBuscarConfiguracionVigenteAjax()
    {
        if (\Yii::$app->request->isAjax && \Yii::$app->request->isPost) {
            if (isset($_POST["carrera"]) && isset($_POST["sede"])) {
                $configuracion = CargaHorariaConfiguracionesDao::buscaCargaHorariaConfiguracion("obj", $_POST["carrera"], $_POST["sede"]);
                $rta = [
                    "GestionAcademicaAnterior" => $configuracion->GestionAcademicaAnterior,
                    "GestionAcademicaPlanificacion" => $configuracion->GestionAcademicaPlanificacion,
                    "GestionAnterior" => $configuracion->GestionAnterior,
                    "MesAnterior" => $configuracion->MesAnterior,
                ];
                return json_encode($rta);
            } else {
                return "error";
            }
        } else {
            return "error";
        }
    }

    public function actionActualizarCargaHorariaConfiguracionAjax()
    {
        if (\Yii::$app->request->isAjax && \Yii::$app->request->isPost) {
            if (isset($_POST["codigocarrera"]) && isset($_POST["codigosede"]) && isset($_POST["gestionacademica"]) && isset($_POST["gestionanterior"]) && isset($_POST["mesanterior"]) && isset($_POST["gestionacademicaanterior"])) {
                $cargaHorariaConfiguracion = CargaHorariaConfiguracion::find()
                    ->where(['CodigoCarrera' => $_POST["codigocarrera"]])
                    ->andWhere(['CodigoSede' => $_POST["codigosede"]])
                    ->andWhere(['GestionAcademica' => $_POST["gestionacademica"]])->one();
                if ($cargaHorariaConfiguracion) {
                    $cargaHorariaConfiguracion->GestionAnterior = $_POST["gestionanterior"];
                    $cargaHorariaConfiguracion->MesAnterior = $_POST["mesanterior"];
                    $cargaHorariaConfiguracion->GestionAcademicaAnterior = $_POST["gestionacademicaanterior"];
                    $cargaHorariaConfiguracion->save();
                } else {
                    return 'vacio';
                }
                return "ok";
            } else {
                return "error";
            }
        } else {
            return "error";
        }
    }



    public function actionListarConfiguraciones()
    {
        if (!(\Yii::$app->request->isAjax && \Yii::$app->request->isPost)){
            return 'Error en la cabezera';
        }
        if (!( isset($_POST['carrera']) && isset($_POST['sede']) )) {
            return 'error en el envio de datos' ;
        }

        $configuracion = CargaHorariaConfiguracion::find()->alias('Cf')
            ->select(['Cf.GestionAcademica','C.NombreCarrera','l.NombreLugar','GestionAcademicaAnterior','GestionAcademicaPlanificacion','CodigoModalidadCurso','CodigoEstado','CodigoUsuario'])
            ->join('INNER JOIN','Carreras c','c.CodigoCarrera = Cf.CodigoCarrera')
            ->join('INNER JOIN','Sedes s','s.CodigoSede = Cf.CodigoSede')
            ->join('INNER JOIN','Lugares l','l.CodigoLugar = s.CodigoLugar')
            ->where(["Cf.CodigoCarrera" => $_POST['carrera'], "Cf.CodigoSede" => $_POST['sede']])
            ->orderBy(['FechaHoraRegistro' => SORT_DESC])
            ->asArray()->all();


        if (!$configuracion){
            return 'No se encontro la informacion de la carrera';
        }

        return json_encode(['rta' => 'ok', 'data' => $configuracion]);
    }


}