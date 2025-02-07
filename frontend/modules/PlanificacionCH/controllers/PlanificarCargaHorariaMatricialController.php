<?php

namespace app\modules\PlanificacionCH\controllers;

use app\modules\PlanificacionCH\models\MateriaMatricial;
use app\modules\PlanificacionCH\models\CargaHorariaPropuesta;
use app\modules\PlanificacionCH\models\Carrera;
use app\modules\PlanificacionCH\models\Materia;
use app\modules\PlanificacionCH\models\MateriaDocente;
use common\models\Estado;
use yii\db\Query;
use yii\web\Controller;
use Yii;

class PlanificarCargaHorariaMatricialController extends Controller
{
    public function actionIndex()
    {
        Yii::$app->session->set('gestion', date('Y')-1);

        //Yii::$app->session->set('gestion',2021);
        return $this->render('planificarcargahorariamatriciales');
    }

    public function actionListarMateriasSelect(){
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_CABECERA']]);
        }

        $gestion = intval($_SESSION['gestion']);

        $search = '%' . str_replace(" ","%", $_POST['q'] ?? '') . '%';

        $materias = Carrera::find()->select(['Mm.SiglaMateriaCH AS id','[M].[NombreMateria] COLLATE SQL_Latin1_General_Cp1251_CS_AS AS text','M.Curso'])->alias('C')
            ->distinct(true)
            ->join('INNER JOIN','MateriasMatriciales Mm','Mm.CodigoCarreraCH = C.CodigoCarrera')
            ->join('INNER JOIN', 'PlanesEstudios P', 'P.CodigoCarrera = Mm.CodigoCarreraCH ')
            ->join('INNER JOIN', 'Materias M', 'M.CodigoCarrera = Mm.CodigoCarreraCH and M.NumeroPlanEstudios = Mm.NumeroPlanEstudiosCH and M.SiglaMateria = Mm.SiglaMateriaCH')
            ->where(['in','Mm.GestionAcademica',[(string)($gestion),'1/'.$gestion]])
            ->andWhere(['C.CodigoEstadoCarrera' => Estado::ESTADO_VIGENTE])->andWhere(['P.CodigoEstadoPlanEstudios' => Estado::ESTADO_VIGENTE])
            ->andWhere(['Mm.CodigoTipoGrupoMateriaCH' => 'T'])->andWhere(['C.CodigoFacultad' => $_POST['facultad']])
            ->andWhere(['like', 'M.NombreMateria', $search, false])
            ->orderBy('M.Curso,Mm.SiglaMateriaCH')
            ->asArray()->all();

        if (!$materias) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO']]);
        }

        return json_encode( ['respuesta' => Yii::$app->params['PROCESO_CORRECTO'], 'materias' =>  $materias]);
    }

    public function actionListarGrupos(){
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return 'ERROR_CABECERA';
        }

        if ($_POST['flag'] == 0)
            return json_encode( ['respuesta' => Yii::$app->params['PROCESO_CORRECTO'],'grupos'=>'']);

        $gestion = intval($_SESSION['gestion']);

        $grupos = (new Query)
            ->select(['v.gestionAcademica as CGA','v.CodigoCarreraCH as CodigoCarrera','v.NumeroPlanEstudiosCH as NumeroPlanEstudios','M.Curso',
                      'v.SiglaMateriaCH as SiglaMateria','v.CodigoModalidadCursoCH as CodigoModalidadCurso',
                      'isnull(M.HorasTeoria,0) as HorasTeoria', 'isnull(M.HorasPractica,0) as HorasPractica', 'isnull(M.HorasLaboratorio,0) as HorasLaboratorio',
                      'v.GrupoCH as Grupo','v.CodigoTipoGrupoMateriaCH as TipoGrupo','chpP.IdPersona',"isnull(P.Paterno,'') + ' ' + isnull(P.Materno,'') + ' ' + isnull(P.Nombres,'') as Nombre",
                      'sum(isnull(Chp.Programados,0)) as Programados','sum(isnull(Chp.Aprobados,0)) as Aprobados','sum(isnull(Chp.Reprobados,0)) as Reprobados',
                      'sum(isnull(Chp.Abandonos,0)) as Abandonos','sum(isnull(Chp.ProyectadosGeneral,0)) as CantidadProyeccion',
                      'chpP.CodigoEstado','chpP.Observaciones',
                      "case [[chpP.TipoGrupo]] when 'T' THEN [[HorasTeoria]]
                                            when 'L' THEN [[HorasLaboratorio]]
                                            when 'P' THEN [[HorasPractica]]
                      end as HorasSemana",'C.NombreCortoCarrera'])
            ->from(['vmateriasmatriciales v'])
            ->join('INNER JOIN', 'CargaHorariaPropuesta chpP', "ChpP.GestionAcademica in ('2025','1/2025')  and 
                                                                              chpP.CodigoCarrera = V.CodigoCarreraCH and 
																			  chpP.NumeroPlanEstudios = V.NumeroPlanEstudiosCH and
																			  chpP.SiglaMateria = V.SiglaMateriaCH and
																			  chpP.TipoGrupo = v.CodigoTipoGrupoMateriaCH and 
																			  chpP.Grupo = v.GrupoCH")
            ->join('INNER JOIN','Carreras C','C.CodigoCarrera = v.CodigoCarreraCH')
            ->join('INNER JOIN','Personas P','P.IdPersona = chpP.IdPersona')
            ->join('INNER JOIN','Materias M', 'M.CodigoCarrera = v.CodigoCarreraCH and M.NumeroPlanEstudios = v.NumeroPlanEstudiosCH and 
                                                             M.SiglaMateria = v.SiglaMateriaCH')
            ->join('INNER JOIN', 'CargaHorariaPropuesta chp',"Chp.GestionAcademica in ('2025','1/2025') and 
                                                                              chp.CodigoCarrera = V.CodigoCarrera and 
																			  chp.NumeroPlanEstudios = V.NumeroPlanEstudios and
																			  chp.SiglaMateria = V.SiglaMateria and
																			  chp.TipoGrupo = v.CodigoTipoGrupoMateria and 
																			  chp.Grupo = v.Grupo")
            ->join('INNER JOIN','Carreras Ch', 'Ch.CodigoCarrera = v.CodigoCarrera')
            ->where(['v.GestionAcademicaCH' => '2/2024'])
            ->andWhere("v.CodigoModalidadCursoCH in ('NS','NA') AND C.CODIGOFACULTAD='TE'")
            ->andWhere(['v.SiglaMateriaCH' => $_POST["sigla"]])
            ->andWhere(['v.CodigoTipoGrupoMateriaCH' => $_POST['tipoGrupo']])
            ->groupBy(' v.GestionAcademica,v.CodigoCarreraCH,v.NumeroPlanEstudiosCH,M.Curso,v.SiglaMateriaCH,v.CodigoModalidadCursoCH,
                                M.HorasTeoria,M.HorasPractica,M.HorasLaboratorio,
                                v.GrupoCH,v.CodigoTipoGrupoMateriaCH, chpP.IdPersona,P.Paterno,P.Materno,P.Nombres,
                                chpP.CodigoEstado,chpP.Observaciones,chpP.TipoGrupo,C.NombreCortoCarrera,chp.grupo')
            ->orderBy("v.SiglaMateriaCH,
                               case when (ISNUMERIC([Chp].[Grupo])) between 1 and 100 then  CONVERT(int,[Chp].[grupo]) end,
                               case when ([Chp].[Grupo]) between 'A' and 'Z' then  [Chp].[grupo] end,
                               case when ([Chp].[Grupo]) between 'a' and 'z' then  [Chp].[grupo] end,C.NombreCortoCarrera
            ")->all(Yii::$app->dbAcademica);

        return json_encode( ['respuesta' => Yii::$app->params['PROCESO_CORRECTO'], 'grupos' => $grupos]);
    }

    public function actionListarDocentes() {
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_CABECERA']]);
        }

        $search = '%' . str_replace(" ","%", $_POST['q'] ?? '') . '%';

        $docentes = (new Query)
            ->select(['convert(varchar(max),P.IdPersona) as id',"ltrim(rtrim(isnull(p.Paterno,''))) + ' ' + ltrim(rtrim(isnull(p.materno,''))) + ' ' + ltrim(rtrim(isnull(p.nombres,''))) as text",
                'cl.DescripcionCondicionLaboral as condicion'])
            ->from(['DetalleItemFuncionario Dif'])
            ->join('INNER JOIN', 'Items i', 'Dif.NroItem = I.NroItem')
            ->join('INNER JOIN','Cargos C', 'c.IdCargo = i.IdCargo and C.CodigoSectorTrabajo = i.CodigoSectortrabajo')
            ->join('INNER JOIN','Organigrama U', 'U.IdUnidad = i.IdUnidad ')
            ->join('INNER JOIN','Funcionarios F', 'F.IdFuncionario = Dif.IdFuncionario and f.CodigoSectorTrabajo = i.CodigoSectortrabajo')
            ->join('INNER JOIN','Personas P', 'P.IdPersona = F.IdPersona')
            ->join('INNER JOIN','CondicionesLaborales Cl', 'Cl.CodigoCondicionLaboral = Dif.CodigoCondicionLaboral')
            ->where(['Dif.CodigoEstadoCargo' => 'V'])->andWhere(['i.CodigoSectortrabajo' => 'DOC'])->andWhere(['i.EstadoCargoUnidad' => 'V'])
            ->andWhere(['c.CodigoEstadoCargo' => 'V'])
            ->andWhere(['u.CodigoEstadoUnidad' => 'V'])
            ->andWhere(['F.CodigoEstadoFuncionario' => 'V'])
            ->andWhere(['like',"ltrim(rtrim(isnull(p.Paterno,''))) + ' ' + ltrim(rtrim(isnull(p.materno,''))) + ' ' + ltrim(rtrim(isnull(p.nombres,'')))",$search,false])
            ->groupBy('convert(varchar(max),[[P.IdPersona]]),P.Paterno,P.Materno,P.Nombres,cl.DescripcionCondicionLaboral')
            ->orderBy('p.Paterno,P.Materno,P.Nombres')
            ->all(Yii::$app->dbrrhh);

        if (!$docentes) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO']]);
        }

        return json_encode( ['respuesta' => Yii::$app->params['PROCESO_CORRECTO'], 'docentes' =>  $docentes]);
    }


    public function actionCambiarEstadoGrupo(){
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_CABECERA']]);
        }

        if (!(isset($_SESSION["gestion"]) && ($_SESSION["gestion"] != "") &&
            isset($_POST["carrera"]) && isset($_POST["sede"]) && isset($_POST["plan"]) &&
            isset($_POST["grupo"]) && isset($_POST["tipoGrupo"])
        )) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS']]);
        }

        $gestion = intval($_SESSION['gestion']);

        $row = CargaHorariaPropuesta::find()
            ->where(['in','GestionAcademica',[(string)($gestion+1),'1/'.($gestion+1)]])
            ->andWhere(['CodigoCarrera' => $_POST["carrera"]])
            ->andWhere(['CodigoSede' => $_POST['sede']])
            ->andWhere(['NumeroPlanEstudios' => $_POST["plan"]])
            ->andWhere(['SiglaMateria' => $_POST["sigla"]])
            ->andWhere(['Grupo' => $_POST["grupo"]])
            ->andWhere(['TipoGrupo' => $_POST["tipoGrupo"]])
            ->andWhere(['CodigoEstado' => $_POST["estado"]])
            ->one();

        if (!$row) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO']]);
        }

        switch ($row->CodigoEstado) {
            case 'V':
                $row->CodigoEstado = Estado::ESTADO_ELIMINADO;
                if ($row->update() === false) {
                    return json_encode(['respuesta' => Yii::$app->params['ERROR_EJECUCION_SQL']]);
                }
                break;
            case 'E':
                if ($row->exist()){
                    return json_encode(['respuesta' => Yii::$app->params['ERROR_REGISTRO_EXISTE']]);
                }

                $row->CodigoEstado = Estado::ESTADO_VIGENTE;

                if ($row->update() === false) {
                    return json_encode(['respuesta' => Yii::$app->params['ERROR_EJECUCION_SQL']]);
                }
                break;
            case 'C':
                $dataAntigua = explode(",",$row->Observaciones);
                $row->CodigoEstado = Estado::ESTADO_VIGENTE;
                $row->IdPersona = $dataAntigua[1];
                $row->Grupo = $dataAntigua[0];
                $row->Observaciones = '';
                if ($row->update() === false) {
                    return json_encode(['respuesta' => Yii::$app->params['ERROR_EJECUCION_SQL']]);
                }
                break;
            case 'A':
                if ($row->delete() === false) {
                    return json_encode(['respuesta' => Yii::$app->params['ERROR_EJECUCION_SQL']]);
                }
                break;
        }

        return json_encode(['respuesta' => Yii::$app->params['PROCESO_CORRECTO']]);
    }

    public function actionVerificarGrupo()
    {
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return false;
        }
        if (!isset($_POST["grupo"]) ) {
            return false;
        }

        $gestion = intval($_SESSION['gestion']);

        $grupo = CargaHorariaPropuesta::find()
            ->where(['in','GestionAcademica',[(string)($gestion+1),'1/'.($gestion+1)]])
            ->andWhere(['CodigoCarrera' => $_POST["carrera"]])
            ->andWhere(['CodigoSede' => $_POST["sede"]])
            ->andWhere(['NumeroPlanEstudios' => $_POST["plan"]])
            ->andWhere(['SiglaMateria' => $_POST["sigla"]])
            ->andWhere(['TipoGrupo' => $_POST["tipoGrupo"]])
            ->andWhere(['grupo' => $_POST["grupo"]])
            ->andWhere(['!=','IdPersona',$_POST['docente']])
            ->andWhere(['!=','CodigoEstado','E'])
            ->one();

        if ($grupo) {
            return false;
        }

        return true;
    }

    public function actionGuardarGrupo()
    {
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_CABECERA']]);
        }
        if (!(isset($_SESSION["gestion"])
            && isset($_POST["carrera"]) && isset($_POST["plan"])  && isset($_POST["sigla"])
            && isset($_POST["tipoGrupo"]) && isset($_POST["grupo"]) && isset($_POST["sede"]) )
        ) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS']]);
        }

        $gestion = intval($_SESSION['gestion']);

        $materiaDocente = MateriaDocente::find()
            ->where(['in','GestionAcademica',[(string)($gestion+1),'1/'.($gestion+1)]])
            ->andWhere(['CodigoCarrera' => $_POST["carrera"]])
            ->andWhere(['CodigoSede' => $_POST["sede"]])
            ->andWhere(['NumeroPlanEstudios' => $_POST["plan"]])
            ->andWhere(['SiglaMateria' => $_POST["sigla"]])
            ->andWhere(['CodigoTipoGrupoMateria' => $_POST["tipoGrupo"]])
            ->andWhere(['in','CodigoModalidadCurso', ['NA','NS'] ])
            ->one();

        $materia = Materia::findOne([
            'CodigoCarrera' => $_POST["carrera"],
            'NumeroPlanEstudios' => $_POST["plan"],
            'SiglaMateria' => $_POST["sigla"]]);

        switch ($_POST['tipoGrupo']){
            case 'T': $horas = $materia->HorasTeoria;
                break;
            case 'L': $horas = $materia->HorasLaboratorio;
                break;
            case 'P': $horas = $materia->HorasPractica;
                break;
        }

        $matriz = new MateriaMatricial();
        ($materiaDocente->CodigoModalidadCurso == 'NS')?$matriz->GestionAcademica = '1/'.($gestion):$matriz->GestionAcademica=(string)($gestion);
        ($materiaDocente->CodigoModalidadCurso == 'NS')?$matriz->GestionAcademicaCH = '1/'.($gestion):$matriz->GestionAcademicaCH=(string)($gestion);


    $matriz->CodigoModalidadCurso = $materiaDocente->CodigoModalidadCurso;
    $matriz->CodigoCarrera = $_POST["carrera"];
    $matriz->SiglaMateria = $_POST["sigla"];
    $matriz->NumeroPlanEstudios = $_POST["plan"];
    $matriz->Grupo = $_POST["grupo"];
    $matriz->CodigoTipoGrupoMateria = $_POST["tipoGrupo"];
    $matriz->CodigoModalidadCursoCH = $materiaDocente->CodigoModalidadCurso;
    $matriz->CodigoCarreraCH = $_POST["carrera"];
    $matriz->SiglaMateriaCH = $_POST["sigla"];
    $matriz->NumeroPlanEstudiosCH = $_POST["plan"];
    $matriz->GrupoCH = $_POST["grupo"];
    $matriz->CodigoTipoGrupoMateriaCH = $_POST["tipoGrupo"];
    $matriz->ProgramacionAgrupada = 0;
    $matriz->FechaHoraRegistro = '06/02/2025';
    $matriz->Observaciones = 'Insertado por planificacion cargahoraria';

    //$matriz->save();
        if (!$matriz->save()) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_EJECUCION_SQL'], 'eerores'=>$matriz->errors]);
        }




        $grupo = new CargaHorariaPropuesta();
        ($materiaDocente->CodigoModalidadCurso == 'NS')?$grupo->GestionAcademica = '1/'.($gestion+1):$grupo->GestionAcademica=(string)($gestion+1);
        $grupo->CodigoCarrera = $_POST["carrera"];
        $grupo->NumeroPlanEstudios = $_POST["plan"];
        $grupo->SiglaMateria = $_POST["sigla"];
        $grupo->Grupo = $_POST["grupo"];
        $grupo->TipoGrupo = $_POST["tipoGrupo"];
        $grupo->CodigoSede = $_POST["sede"];
        $grupo->IdPersona = $_POST['docente'];
        $grupo->HorasSemana = $horas;
        $grupo->Observaciones = '';
        $grupo->CodigoEstado = 'A';
        $grupo->CodigoUsuario = Yii::$app->user->identity->CodigoUsuario;



        if ($grupo->exist()) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_REGISTRO_EXISTE']]);
        }
        if (!$grupo->validate()) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_VALIDACION_MODELO']]);
        }
        if (!$grupo->save()) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_EJECUCION_SQL']]);
        }

        return json_encode(['respuesta' => Yii::$app->params['PROCESO_CORRECTO']]);
    }

    public function actionBuscarGrupo(){
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_CABECERA']]);
        }

        if (!(isset($_POST["gestion"]) && ($_POST["gestion"] != "") &&
            isset($_POST["carrera"]) && isset($_POST["sede"]) && isset($_POST["plan"]) &&
            isset($_POST["grupo"]) && isset($_POST["tipoGrupo"])
        )) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS']]);
        }

        $gestion = intval($_POST['gestion']);

        $row = CargaHorariaPropuesta::find()->alias('chp')
            ->select('chp.*,P.*')
            ->join('INNER JOIN','Personas P', 'P.IdPersona = chp.IdPersona')
            ->where(['in','GestionAcademica',[(string)($gestion+1),'1/'.($gestion+1)]])
            ->andWhere(['CodigoCarrera' => $_POST["carrera"]])
            ->andWhere(['CodigoSede' => $_POST['sede']])
            ->andWhere(['NumeroPlanEstudios' => $_POST["plan"]])
            ->andWhere(['SiglaMateria' => $_POST["sigla"]])
            ->andWhere(['Grupo' => $_POST["grupo"]])
            ->andWhere(['TipoGrupo' => $_POST["tipoGrupo"]])
            ->asArray()
            ->one();

        if (!$row) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO']]);
        }

        return json_encode( ['respuesta' => Yii::$app->params['PROCESO_CORRECTO'], 'grupo' => $row]);
    }

    public function actionActualizarGrupo(){
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_CABECERA']]);
        }

        if (!(isset($_POST["gestion"]) && ($_POST["gestion"] != "") &&
            isset($_POST["carrera"]) && isset($_POST["sede"]) && isset($_POST["plan"]) &&
            isset($_POST["grupo"]) && isset($_POST["tipoGrupo"])
        )) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS']]);
        }

        $gestion = intval($_POST['gestion']);

        $row = CargaHorariaPropuesta::find()
            ->where(['in','GestionAcademica',[(string)($gestion+1),'1/'.($gestion+1)]])
            ->andWhere(['CodigoCarrera' => $_POST["carrera"]])
            ->andWhere(['CodigoSede' => $_POST['sede']])
            ->andWhere(['NumeroPlanEstudios' => $_POST["plan"]])
            ->andWhere(['SiglaMateria' => $_POST["sigla"]])
            ->andWhere(['Grupo' => $_POST["grupo"]])
            ->andWhere(['TipoGrupo' => $_POST["tipoGrupo"]])
            ->one();

        if (!$row) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO']]);
        }

        $row->Observaciones = $row->Grupo . ',' . $row->IdPersona;
        $row->IdPersona = trim($_POST['idPersonaN']);
        $row->Grupo = trim($_POST['grupoN']);
        $row->CodigoEstado = "C";

        if ($row->update() === false) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_EJECUCION_SQL']]);
        }


        return json_encode(['respuesta' => Yii::$app->params['PROCESO_CORRECTO']]);
    }

    public function actionEnviarCargahoraria(){
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_CABECERA']]);
        }

        if (!(isset($_POST["gestion"]) && ($_POST["gestion"] != "") &&
            isset($_POST["carrera"]) && isset($_POST["sede"]) && isset($_POST["plan"]))
        ) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS']]);
        }

        $envio = new ControlEnvioPlanificacionCH();
        $envio->GestionAcademica = $_POST["gestion"];
        $envio->CodigoCarrera = $_POST["carrera"];
        $envio->CodigoSede = $_POST["sede"];
        $envio->NumeroPlanEstudios = $_POST["plan"];
        $envio->CodigoEstado = '1';

        $envio->save();

        return json_encode( ['respuesta' => Yii::$app->params['PROCESO_CORRECTO']]);
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

    public function actionMostrar(){

        $gestion = intval($_SESSION['gestion']);

        $vigente = (new Query)
            ->select(['ltrim(rtrim(Chp.IdPersona)) as IdPersona','M.CodigoCarrera as codigo','c.NombreCortoCarrera as carrera','Chp.siglaMateria as materia','M.nombremateria',"sum(case Chp.TipoGrupo
                                                                     when 'T' THEN m.HorasTeoria *4
                                                                     when 'L' THEN m.HorasLaboratorio *4
                                                                     when 'P' THEN m.HorasPractica *4
                                                                 end) as Ch"])
            ->from(['Materias M'])
            ->join('INNER JOIN', 'CargaHorariaPropuesta Chp', 'Chp.CodigoCarrera = M.CodigoCarrera and Chp.NumeroPlanEstudios = M.NumeroPlanEstudios and Chp.SiglaMateria = M.SiglaMateria ')
            ->join('INNER JOIN', 'Carreras c','M.CodigoCarrera = c.CodigoCarrera')
            ->where(['in','Chp.GestionAcademica',[(string)($gestion+1),'1/'.$gestion+1]])
            ->andWhere(['chp.TransferidoCargaHoraria' => 1])->andWhere(['Chp.CodigoEstado' => 'V'])
            ->groupBy('Chp.IdPersona,M.CodigoCarrera,c.NombreCortoCarrera,Chp.SiglaMateria, M.nombremateria')
            ->all(Yii::$app->dbAcademica);

        $eliminada = (new Query)
            ->select(['ltrim(rtrim(Chp.IdPersona)) as IdPersona',"sum(case Chp.TipoGrupo
                                                                     when 'T' THEN m.HorasTeoria *4
                                                                     when 'L' THEN m.HorasLaboratorio *4
                                                                     when 'P' THEN m.HorasPractica *4
                                                                 end) as Ch"])
            ->from(['Materias M'])
            ->join('INNER JOIN', 'CargaHorariaPropuesta Chp', 'Chp.CodigoCarrera = M.CodigoCarrera and Chp.NumeroPlanEstudios = M.NumeroPlanEstudios and Chp.SiglaMateria = M.SiglaMateria ')
            ->where(['in','Chp.GestionAcademica',[(string)($gestion+1),'1/'.$gestion+1]])
            ->andWhere(['Chp.CodigoEstado' => 'E'])
            ->groupBy('Chp.IdPersona')
            ->all(Yii::$app->dbAcademica);


        $agregada = (new Query)
            ->select(['ltrim(rtrim(Chp.IdPersona)) as IdPersona',"sum(case Chp.TipoGrupo
                                                                     when 'T' THEN m.HorasTeoria *4
                                                                     when 'L' THEN m.HorasLaboratorio *4
                                                                     when 'P' THEN m.HorasPractica *4
                                                                 end) as Ch"])
            ->from(['Materias M'])
            ->join('INNER JOIN', 'CargaHorariaPropuesta Chp', 'Chp.CodigoCarrera = M.CodigoCarrera and Chp.NumeroPlanEstudios = M.NumeroPlanEstudios and Chp.SiglaMateria = M.SiglaMateria ')
            ->where(['in','Chp.GestionAcademica',[(string)($gestion+1),'1/'.$gestion+1]])
            ->andWhere(['Chp.CodigoEstado' => 'A'])
            ->groupBy('Chp.IdPersona')
            ->all(Yii::$app->dbAcademica);

        return json_encode( ['respuesta' => Yii::$app->params['PROCESO_CORRECTO'], 'vigente' =>  $vigente, 'eliminada' =>  $eliminada, 'agregada' =>  $agregada] );

    }


    public function actionTotales(){

        $gestion = intval($_SESSION['gestion']);
        $sigla = $_POST['sigla'];
        $tipogrupo = $_POST['tipogrupo'];

        $totales = (new Query)
            ->select(['SUM(c.Programados) as programado',
                    'SUM(c.Aprobados) as aprobado',
                    'SUM(c.Reprobados) as reprobado',
                    'SUM(c.Abandonos) as abandono',
                    'SUM(c.ProyectadosGeneral) as proyectado'])
            ->from(['MateriasMatriciales v'])
            ->join('INNER JOIN', 'CargaHorariaPropuesta c', "c.GestionAcademica = '1/2025' and
																		  v.NumeroPlanEstudiosCH = c.NumeroPlanEstudios and
                                                                          v.CodigoCarreraCH = c.CodigoCarrera and 
																		  v.SiglaMateriaCH = c.SiglaMateria and 
																		  v.CodigoTipoGrupoMateriaCH = c.TipoGrupo and 
																		  v.GrupoCH = c.Grupo")
            ->where(['v.GestionAcademicaCH'=>'2/2024'])
            ->andWhere(['v.CodigoModalidadCursoCH' => 'NS'])->andWhere(['v.SiglaMateriaCH' => $sigla])
            ->andWhere(['v.CodigoTipoGrupoMateriaCH' => $tipogrupo])
            ->one(Yii::$app->dbAcademica);


        return json_encode( ['respuesta' => Yii::$app->params['PROCESO_CORRECTO'], 'totales' =>  $totales] );

    }


}