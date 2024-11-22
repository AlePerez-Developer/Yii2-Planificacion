<?php

namespace app\modules\PlanificacionCH\controllers;


use app\modules\PlanificacionCH\dao\CarrerasDao;
use app\modules\PlanificacionCH\dao\FacultadesDao;
use app\modules\PlanificacionCH\dao\PlanesEstudiosDao;
use app\modules\PlanificacionCH\models\Facultad;
use app\modules\PlanificacionCH\models\Materia;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;
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
                $opciones .= "<option value='" . $sede->CodigoSede . "' > Sede academica: " . $sede->NombreSede . "</option>";
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
                $opciones .= "<option value='" . $planEstudio->NumeroPlanEstudios . "'> Numero de plan de estudios: " . $planEstudio->NumeroPlanEstudios . "</option>";
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
                $opciones .= "<option value='" . $curso->Curso . "'> Curso N°: " . $curso->Curso . "</option>";
            }
            return $opciones;
        }
    }


    public function actionListarMaterias(){
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            $Materias = Materia::find()->alias('M')
                ->select(['M.CodigoCarrera','M.NumeroPlanEstudios','M.SiglaMateria','M.NombreMateria','isnull(M.HorasTeoria,0) as HorasTeoria','isnull(M.HorasPractica,0) as HorasPractica','isnull(M.HorasLaboratorio,0) as HorasLaboratorio','M.Curso','Md.CodigoSede',
                    'sum(Md.NumeroEstudiantesProgramados) as Prog','p.CantidadProyeccion as Proy'])
                ->join('INNER JOIN','Proyecciones P', 'P.CodigoCarrera = M.CodigoCarrera and P.SiglaMateria = m.SiglaMateria')
                ->join('INNER JOIN','MateriasDocentes Md', 'Md.CodigoCarrera = M.CodigoCarrera and Md.NumeroPlanEstudios = M.NumeroPlanEstudios and Md.SiglaMateria = M.SiglaMateria')
                ->where(['M.CodigoCarrera' => $_POST["carrera"]])
                ->andWhere(['M.Curso' => $_POST["curso"]])
                ->andWhere(['M.NumeroPlanEstudios' => $_POST["plan"]])
                ->andWhere(['P.GestionAcademica' => $_POST["gestion"]])
                ->andWhere(['Md.GestionAcademica' => $_POST["gestion"]])
                ->andWhere("Md.CodigoTipoGrupoMateria = 'T' and Md.CodigoModalidadCurso in ('NS','NA')")
                ->groupBy(' M.CodigoCarrera,M.NumeroPlanEstudios,M.SiglaMateria,M.NombreMateria,M.HorasTeoria,M.HorasPractica,M.HorasLaboratorio,p.CantidadProyeccion,M.curso,Md.CodigoSede')
                ->orderBy('M.SiglaMateria')
                ->asArray()
                ->all();
            return json_encode($Materias);
        } else
            return 'ERROR_CABECERA';
    }

    public function actionListarEncabezadoModal(){
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return 'ERROR_CABECERA';
        }

        $encabezado = Facultad::find()->alias('F')
            ->select(['F.NombreFacultad', 'c.NombreCarrera', 'S.NombreSede', 'NumeroPlanesEstudios','curso','SiglaMateria','NombreMateria'])
            ->join('INNER JOIN','Carreras C', 'f.CodigoFacultad = C.CodigoFacultad ')
            ->join('INNER JOIN','CarrerasSedes Cs', 'cs.CodigoCarrera = c.CodigoCarrera')
            ->join('INNER JOIN','Sedes S', 's.CodigoSede = Cs.CodigoSede')
            ->join('INNER JOIN','Materias M', 'M.CodigoCarrera = c.CodigoCarrera')
            ->where(['F.CodigoFacultad' => $_POST['facultad']])
            ->andWhere(['C.CodigoCarrera' => $_POST['carrera'] ])
            ->andWhere(['NumeroPlanEstudios' => $_POST['plan'] ])
            ->andWhere(['Curso' => $_POST['curso'] ])
            ->andWhere(['SiglaMateria' => $_POST['sigla'] ])
            ->andWhere(['c.CodigoEstadoCarrera' => 'V' ])
            ->asArray()
            ->one();

        if (!$encabezado) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO']]);
        }

        return json_encode( ['respuesta' => Yii::$app->params['PROCESO_CORRECTO'], 'data' => $encabezado]);
    }

    public function actionListarGruposMaterias(){
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return 'ERROR_CABECERA';
        }

        $lista = Materia::find()->alias('M')
            ->select(['M.CodigoCarrera','M.NumeroPlanEstudios','M.SiglaMateria','M.NombreMateria','Md.CodigoTipoGrupoMateria','Md.Grupo', 'Md.IdPersona'])
            //->from('Materias M')
            ->join('INNER JOIN','MateriasDocentes Md', 'M.CodigoCarrera = Md.CodigoCarrera  and M.NumeroPlanEstudios = Md.NumeroPlanEstudios and M.SiglaMateria = Md.SiglaMateria')
            ->where(['Md.GestionAcademica' => '1/2021'])
            ->andWhere(['M.CodigoCarrera' => $_POST['carrera'] ])
            ->andWhere(['Md.CodigoSede' => $_POST['sede'] ])
            ->andWhere(['M.NumeroPlanEstudios' => $_POST['plan'] ])
            ->andWhere(['M.Curso' => $_POST['curso'] ])
            ->andWhere(['M.SiglaMateria' => $_POST['sigla'] ])
            ->andWhere("CodigoTipoGrupoMateria = 'T' and CodigoModalidadCurso = 'NS'")->asArray()->all();


        return json_encode($lista);
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