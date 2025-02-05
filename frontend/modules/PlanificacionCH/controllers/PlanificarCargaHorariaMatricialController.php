<?php

namespace app\modules\PlanificacionCH\controllers;

use app\modules\PlanificacionCH\models\Materia;
use common\models\Estado;
use yii\db\Query;
use yii\web\Controller;
use Yii;

class PlanificarCargaHorariaMatricialController extends Controller
{
    public function actionIndex()
    {
        Yii::$app->session->set('gestion', date('Y'));
        Yii::$app->session->set('gestion', 2022);
        return $this->render('planificarcargahorariamatriciales');
    }

    public function actionObtenerEstadoEnvio()
    {
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_CABECERA']]);
        }

        if (!(isset($_POST["gestion"]) && ($_POST["gestion"] != "") &&
            isset($_POST["carrera"]) && isset($_POST["sede"])  &&
            isset($_POST["plan"]))
        ) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS']]);
        }

        $estado = (new Query)
            ->select(['isnull(CodigoEstado,0)'])
            ->from(['ControlEnvioPlanificacionCH C'])
            ->where(['c.GestionAcademica' => $_POST["gestion"] ])
            ->andwhere(['c.CodigoCarrera' => $_POST["carrera"] ])
            ->andwhere(['c.CodigoSede' => $_POST["sede"] ])
            ->andwhere(['c.NumeroPlanEstudios' => $_POST["plan"] ])
            ->all(Yii::$app->dbAcademica);


        if (!$estado) {
            return json_encode(['respuesta' => Yii::$app->params['PROCESO_CORRECTO'], 'estado' =>  '0']);
        }

        return json_encode( ['respuesta' => Yii::$app->params['PROCESO_CORRECTO'], 'estado' =>  $estado]);
    }

    public function actionListarMaterias(){
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_CABECERA']]);
        }

        if (!(isset($_SESSION["gestion"]) && ($_SESSION["gestion"] != "") &&
            isset($_POST["carrera"]) && isset($_POST["flag"])
        )) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS']]);
        }

        $gestion = intval(Yii::$app->session['gestion']) - 1;

        if ($_POST['flag'] == 0)
            return json_encode( ['respuesta' => Yii::$app->params['PROCESO_CORRECTO']]);

        Yii::$app->session->set('curso', $_POST['curso']);
        Yii::$app->session->set('sede', $_POST['sede']);

        $Materias = Materia::find()->alias('M')
            ->select(['Chp.GestionAcademica','M.CodigoCarrera','M.NumeroPlanEstudios','M.Curso','M.SiglaMateria','M.NombreMateria',
                'isnull(M.HorasTeoria,0) as HorasTeoria','isnull(M.HorasPractica,0) as HorasPractica','isnull(M.HorasLaboratorio,0) as HorasLaboratorio',
                'SUM(Chp.Programados) AS [Programados]', 'SUM(Chp.Aprobados) AS [Aprobados]', 'SUM(chp.Reprobados) AS [Reprobados]', 'SUM(Chp.Abandonos) AS [Abandonos]',
                'chp.ProyectadosGeneral as CantidadProyeccion'])
            ->join('INNER JOIN','CargaHorariaPropuesta Chp', 'Chp.CodigoCarrera = M.CodigoCarrera and Chp.NumeroPlanEstudios = M.NumeroPlanEstudios and Chp.SiglaMateria = M.SiglaMateria')
            ->join('INNER JOIN','Carreras c', 'Chp.CodigoCarrera = c.CodigoCarrera')
            ->join('INNER JOIN', 'MateriasMatriciales mm','Chp.CodigoCarrera = mm.CodigoCarreraCH and Chp.NumeroPlanEstudios = mm.NumeroPlanEstudiosCH and Chp.SiglaMateria = mm.SiglaMateriaCH')
            ->where(['in','Chp.GestionAcademica',[(string)($gestion+1),'1/'.$gestion+1]])->andWhere(['in','mm.GestionAcademica',[(string)($gestion+1),'1/'.$gestion+1]])
            ->andWhere("mm.codigomodalidadcurso in ('NS','NA')")
            ->andWhere(['mm.SiglaMateriaCh' => 'FIS100'])
            //->andWhere(['M.CodigoCarrera' => $_POST["carrera"]])->andWhere(['M.Curso' => $_POST["curso"]])
            //->andWhere(['M.NumeroPlanEstudios' => $_POST["plan"]])->andWhere(['Chp.CodigoSede' => $_POST["sede"]])
            ->groupBy('Chp.GestionAcademica,M.CodigoCarrera,c.NombreCortoCarrera, M.NumeroPlanEstudios,M.Curso,M.SiglaMateria,M.NombreMateria,M.HorasTeoria,M.HorasPractica,M.HorasLaboratorio, chp.ProyectadosGeneral')
            ->orderBy('M.SiglaMateria')
            ->asArray()
            ->all();

        return json_encode($Materias);
    }


    public function actionListarGrupos(){
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return 'ERROR_CABECERA';
        }

        $gestion = intval($_SESSION['gestion']);

        $grupos = (new Query)
            ->select(['Md.GestionAcademica as MGA','Chp.gestionAcademica as CGA',
                'M.CodigoCarrera','M.NumeroPlanEstudios','M.Curso','M.SiglaMateria','M.NombreMateria','Md.CodigoModalidadCurso',
                'isnull(M.HorasTeoria,0) as HorasTeoria','isnull(M.HorasPractica,0) as HorasPractica','isnull(M.HorasLaboratorio,0) as HorasLaboratorio',
                'Chp.Grupo','Chp.TipoGrupo','Chp.IdPersona',"isnull(P.Paterno,'') + ' ' + isnull(P.Materno,'') + ' ' + isnull(P.Nombres,'') as Nombre",
                'isnull(Chp.Programados,0) as Programados',
                'isnull(Chp.Aprobados,0) as Aprobados','isnull(Chp.Reprobados,0) as Reprobados','isnull(Chp.Abandonos,0) as Abandonos',
                'isnull(Chp.ProyectadosGeneral,0) as CantidadProyeccion' ,'chp.CodigoEstado','Chp.Observaciones',
                "case [[chp.TipoGrupo]] when 'T' THEN [[HorasTeoria]]
                                            when 'L' THEN [[HorasLaboratorio]]
                                            when 'P' THEN [[HorasPractica]]
                      end as HorasSemana"])
            ->from(['Materias M'])
            ->join('INNER JOIN', 'CargaHorariaPropuesta Chp', 'Chp.CodigoCarrera = M.CodigoCarrera and Chp.NumeroPlanEstudios = M.NumeroPlanEstudios and Chp.SiglaMateria = M.SiglaMateria')
            ->join('left JOIN','MateriasDocentes Md', 'Md.CodigoCarrera = Chp.CodigoCarrera and Md.NumeroPlanEstudios = Chp.NumeroPlanEstudios and Md.SiglaMateria = Chp.SiglaMateria and Md.CodigoTipoGrupoMateria = Chp.TipoGrupo /*and Md.Grupo = Chp.Grupo*/')
            ->join('INNER JOIN', 'MateriasMatriciales mm','Chp.CodigoCarrera = mm.CodigoCarrera and Chp.NumeroPlanEstudios = mm.NumeroPlanEstudios and Chp.SiglaMateria = mm.SiglaMateria and chp.TipoGrupo = mm.CodigoTipoGrupoMateria and Chp.grupo = mm.grupo')
            ->join('INNER JOIN','Personas P', 'P.IdPersona = Chp.IdPersona')
            ->where(['in','Md.GestionAcademica',[(string)$gestion,'1/'.$gestion]])->andWhere(['in','Chp.GestionAcademica',[(string)($gestion+1),'1/'.($gestion+1)]])
            ->andWhere(['M.CodigoCarrera' => $_POST["carrera"]])->andWhere(['M.Curso' => $_POST["curso"]])->andWhere(['M.NumeroPlanEstudios' => $_POST["plan"]])
            ->andWhere(['M.SiglaMateria' => $_POST["sigla"]])
            ->andWhere(['Chp.TipoGrupo' => $_POST['tipoGrupo']])
            ->andWhere("Md.CodigoModalidadCurso in ('NS','NA')")
            ->groupBy('Md.GestionAcademica,chp.GestionAcademica,  
                               M.CodigoCarrera,M.NumeroPlanEstudios,M.Curso,M.SiglaMateria,M.NombreMateria,Md.CodigoModalidadCurso,
                               M.HorasTeoria,M.HorasPractica,M.HorasLaboratorio,
                               Chp.Grupo,Chp.TipoGrupo,Chp.IdPersona,P.Paterno,P.Materno,P.Nombres, 
                               Chp.Programados, Chp.Aprobados, Chp.reprobados, Chp.abandonos, Chp.proyectadosgeneral, chp.CodigoEstado,Chp.Observaciones')
            ->orderBy("M.SiglaMateria,
case when (ISNUMERIC([Chp].[Grupo])) between 1 and 100 then  CONVERT(int,[Chp].[grupo]) end,
case when ([Chp].[Grupo]) between 'A' and 'Z' then  [Chp].[grupo] end,
case when ([Chp].[Grupo]) between 'a' and 'z' then  [Chp].[grupo] end
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

        $gestion = intval($_POST['gestion']);

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
        if (!(isset($_POST["gestion"])
            && isset($_POST["carrera"]) && isset($_POST["plan"])  && isset($_POST["sigla"])
            && isset($_POST["tipoGrupo"]) && isset($_POST["grupo"]) && isset($_POST["sede"]) )
        ) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS']]);
        }

        $gestion = intval($_POST['gestion']);

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

        $gestion = intval($_POST['gestion']);

        $vigente = (new Query)
            ->select(['ltrim(rtrim(Chp.IdPersona)) as IdPersona','M.CodigoCarrera as codigo','c.NombreCortoCarrera as carrera',"sum(case Chp.TipoGrupo
                                                                     when 'T' THEN m.HorasTeoria *4
                                                                     when 'L' THEN m.HorasLaboratorio *4
                                                                     when 'P' THEN m.HorasPractica *4
                                                                 end) as Ch"])
            ->from(['Materias M'])
            ->join('INNER JOIN', 'CargaHorariaPropuesta Chp', 'Chp.CodigoCarrera = M.CodigoCarrera and Chp.NumeroPlanEstudios = M.NumeroPlanEstudios and Chp.SiglaMateria = M.SiglaMateria ')
            ->join('INNER JOIN', 'Carreras c','M.CodigoCarrera = c.CodigoCarrera')
            ->where(['in','Chp.GestionAcademica',[(string)($gestion+1),'1/'.$gestion+1]])
            ->andWhere(['chp.TransferidoCargaHoraria' => 1])->andWhere(['Chp.CodigoEstado' => 'V'])
            ->groupBy('Chp.IdPersona,M.CodigoCarrera,c.NombreCortoCarrera')
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


}