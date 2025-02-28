<?php
namespace app\modules\PlanificacionCH\controllers;

use app\modules\PlanificacionCH\models\ControlEnvioPlanificacionCH;
use app\modules\PlanificacionCH\models\CargaHorariaPropuesta;
use app\modules\PlanificacionCH\models\MateriaDocente;
use app\modules\PlanificacionCH\models\vCargaHoraria;
use app\modules\PlanificacionCH\models\CarreraSede;
use app\modules\PlanificacionCH\models\PlanEstudio;
use app\modules\PlanificacionCH\models\Facultad;
use app\modules\PlanificacionCH\models\Carrera;
use app\modules\PlanificacionCH\models\Materia;
use yii\base\InvalidConfigException;
use yii\db\StaleObjectException;
use yii\filters\VerbFilter;
use common\models\Estado;
use yii\web\Controller;
use yii\db\Exception;
use yii\db\Query;
use Throwable;
use Yii;

class PlanificarCargaHorariaController extends Controller
{
    public function behaviors(): array
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

    public function actionIndex(): string
    {
        Yii::$app->session->set('gestion', date('Y')-1);
        Yii::$app->session->set('gestion',2021);
        return $this->render('planificarcargahoraria');
    }

    public function actionListarFacultades() {
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_CABECERA'], 'facultades' =>  '']);
        }

        $search = '%' . str_replace(" ","%", $_POST['q'] ?? '') . '%';

        $facultades = Facultad::find()->select(['F.CodigoFacultad as id','F.NombreFacultad as text'])->alias('F')
            ->distinct()
            ->join('INNER JOIN', 'Carreras C', 'F.CodigoFacultad = C.CodigoFacultad')
            ->join('INNER JOIN', 'ConfiguracionesUsuariosCarreras cfg', 'C.CodigoCarrera = cfg.CodigoCarrera')
            ->where(['like', 'NombreFacultad', $search,false])
            ->andWhere(['cfg.CodigoUsuario' => Yii::$app->user->identity->CodigoUsuario])
            ->orderBy('NombreFacultad')
            ->asArray()->all();

        if (!$facultades) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'], 'facultades' => '']);
        }

        return json_encode( ['respuesta' => Yii::$app->params['PROCESO_CORRECTO'], 'facultades' =>  $facultades]);
    }

    public function actionListarCarreras() {
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_CABECERA'], 'carreras' =>  '']);
        }

        if (!(isset($_POST["facultad"]) && $_POST["facultad"] != "")) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS'], 'carreras' =>  '']);
        }

        $search = '%' . str_replace(" ","%", $_POST['q'] ?? '') . '%';

        $carreras = Carrera::find()->select(['C.CodigoCarrera as id','C.NombreCarrera as text'])->alias('C')
            ->distinct()
            ->join('INNER JOIN', 'ConfiguracionesUsuariosCarreras cfg', 'C.CodigoCarrera = cfg.CodigoCarrera')
            ->where(['CodigoFacultad' => $_POST['facultad']])
            ->andWhere(['like', 'NombreCarrera', $search, false])
            ->andWhere(['CodigoEstadoCarrera' => Estado::ESTADO_VIGENTE])
            ->andWhere(['cfg.CodigoUsuario' => Yii::$app->user->identity->CodigoUsuario])
            ->orderBy('NombreCarrera')
            ->asArray()->all();

        if (!$carreras) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'], 'carreras' => '']);
        }

        return json_encode( ['respuesta' => Yii::$app->params['PROCESO_CORRECTO'], 'carreras' =>  $carreras]);
    }

    public function actionListarSedes()
    {
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_CABECERA'], 'sedes' =>  '']);
        }

        if (!(isset($_POST["carrera"]) && $_POST["carrera"] != "")) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS'], 'sedes' =>  '']);
        }

        $search = str_replace(" ","%", $_POST['q'] ?? '');

        $sedes = CarreraSede::find()->alias('Cs')
            ->select(['Cs.CodigoSede as id','S.NombreSede as text'])
            ->join('inner join','Sedes S', 'Cs.CodigoSede = S.CodigoSede')
            ->where(['Cs.CodigoCarrera' => $_POST['carrera']])
            ->andWhere(['like', 'S.NombreSede', $search])
            ->orderBy('S.NombreSede')
            ->asArray()->all();

        if (!$sedes) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'], 'sedes' => '']);
        }

        return json_encode( ['respuesta' => Yii::$app->params['PROCESO_CORRECTO'], 'sedes' =>  $sedes]);
    }

    public function actionListarPlanesEstudios()
    {
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_CABECERA'], 'planes' =>  '']);
        }

        if (!(isset($_POST["carrera"]) && $_POST["carrera"] != "")) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS'], 'planes' =>  '']);
        }

        $search = str_replace(" ","%", $_POST['q'] ?? '');

        $planes = PlanEstudio::find()
            ->select(['NumeroPlanEstudios as id','NumeroPlanEstudios as text'])
            ->where(['CodigoCarrera' => $_POST['carrera']])
            ->andWhere(['CodigoEstadoPlanEstudios' => 'V'])
            ->andWhere(['like', 'NumeroPlanEstudios', $search])
            ->orderBy('NumeroPlanEstudios')
            ->asArray()->all();

        if (!$planes) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'], 'planes' => '']);
        }

        return json_encode( ['respuesta' => Yii::$app->params['PROCESO_CORRECTO'], 'planes' =>  $planes]);
    }

    public function actionListarCursos()
    {
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_CABECERA'], 'cursos' =>  '']);
        }

        if (!(isset($_POST["carrera"]) && $_POST["carrera"] != ""
            && isset($_POST["plan"]) && $_POST["plan"] != ""
        )){
            return json_encode(['respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS'], 'cursos' =>  '']);
        }

        $search = str_replace(" ","%", $_POST['q'] ?? '');

        $cursos = Materia::find()
            ->select(['Curso as id','Curso as text'])
            ->distinct()
            ->where(['CodigoCarrera' => $_POST['carrera']])
            ->andWhere(['NumeroPlanEstudios' => $_POST['plan']])
            ->andWhere(['CodigoEstadoMateria' => 'A' ])
            ->andWhere(['like', 'Curso', $search])
            ->orderBy('Curso')
            ->asArray()->all();

        if (!$cursos) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'], 'cursos' =>  '']);
        }

        return json_encode( ['respuesta' => Yii::$app->params['PROCESO_CORRECTO'], 'cursos' =>  $cursos]);
    }

    public function actionObtenerEstadoEnvio()
    {
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_CABECERA'], 'estado' => '0']);
        }

        if (!(isset($_SESSION["gestion"]) && ($_SESSION["gestion"] != "") &&
            isset($_POST["carrera"]) && isset($_POST["sede"])  &&
            isset($_POST["plan"]))
        ) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS']]);
        }

        $gestion = intval($_SESSION["gestion"]) + 1;

        $estado = ControlEnvioPlanificacionCH::findOne(['GestionAcademica' => $gestion,
                                                        'CodigoCarrera' => $_POST["carrera"],
                                                        'CodigoSede' => $_POST["sede"],
                                                        'NumeroPlanEstudios' => $_POST["plan"],
        ]);

        if (!$estado) {
            return json_encode(['respuesta' => Yii::$app->params['PROCESO_CORRECTO'], 'estado' =>  '0']);
        }

        if ($estado->CodigoEstado !== 'V')
            return json_encode( ['respuesta' => Yii::$app->params['PROCESO_CORRECTO'], 'estado' =>  '0']);

        return json_encode( ['respuesta' => Yii::$app->params['PROCESO_CORRECTO'], 'estado' =>  '1']);
    }

    public function actionListarMaterias(){
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_CABECERA']]);
        }

        if (!(isset($_SESSION["gestion"]) && ($_SESSION["gestion"] != "") &&
            isset($_POST["carrera"]) && isset($_POST["sede"]) && isset($_POST["curso"]) &&
            isset($_POST["plan"]) && isset($_POST["flag"])
        )) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS']]);
        }

        $gestion = intval($_SESSION['gestion']);

        if ($_POST['flag'] == 0)
            return json_encode( ['respuesta' => Yii::$app->params['PROCESO_CORRECTO']]);

        $Materias = Materia::find()->alias('M')
            ->select(['Chp.GestionAcademica','M.CodigoCarrera','M.NumeroPlanEstudios','M.Curso','M.SiglaMateria','M.NombreMateria',
                'isnull(M.HorasTeoria,0) as HorasTeoria','isnull(M.HorasPractica,0) as HorasPractica','isnull(M.HorasLaboratorio,0) as HorasLaboratorio',

                "isnull(sum(case when [Chp].[TipoGrupo] = 'T' then [Chp].[Programados] end),0) as ProgT",
                "isnull(sum(case when [Chp].[TipoGrupo] = 'T' then [Chp].[Aprobados] end),0) as AproT",
                "isnull(sum(case when [Chp].[TipoGrupo] = 'T' then [Chp].[Reprobados] end),0) as ReproT",
                "isnull(sum(case when [Chp].[TipoGrupo] = 'T' then [Chp].[Abandonos] end),0) as AbanT",

                "isnull(sum(case when [Chp].[TipoGrupo] = 'L' then [Chp].[Programados] end),0) as ProgL",
                "isnull(sum(case when [Chp].[TipoGrupo] = 'L' then [Chp].[Aprobados] end),0) as AproL",
                "isnull(sum(case when [Chp].[TipoGrupo] = 'L' then [Chp].[Reprobados] end),0) as ReproL",
                "isnull(sum(case when [Chp].[TipoGrupo] = 'L' then [Chp].[Abandonos] end),0) as AbanL",

                "isnull(sum(case when [Chp].[TipoGrupo] = 'P' then [Chp].[Programados] end),0) as ProgP",
                "isnull(sum(case when [Chp].[TipoGrupo] = 'P' then [Chp].[Aprobados] end),0) as AproP",
                "isnull(sum(case when [Chp].[TipoGrupo] = 'P' then [Chp].[Reprobados] end),0) as ReproP",
                "isnull(sum(case when [Chp].[TipoGrupo] = 'P' then [Chp].[Abandonos] end),0) as AbanP",

                'avg([chp].[ProyectadosGeneral]) as CantidadProyeccion'])
            ->join('INNER JOIN','CargaHorariaPropuesta Chp', 'Chp.CodigoCarrera = M.CodigoCarrera and Chp.SiglaMateria = M.SiglaMateria')
            ->where(['in','Chp.GestionAcademica',[(string)($gestion+1), '1/' . ($gestion + 1)]])
            ->andWhere(['M.CodigoCarrera' => $_POST["carrera"]])
            ->andWhere(['like','M.Curso', '%' .  ($_POST["curso"] > 0 ? $_POST["curso"] : '')      , false])
            ->andWhere(['M.NumeroPlanEstudios' => $_POST["plan"]])->andWhere(['Chp.CodigoSede' => $_POST["sede"]])
            ->groupBy('Chp.GestionAcademica,M.CodigoCarrera,M.NumeroPlanEstudios,M.Curso,M.SiglaMateria,M.NombreMateria,M.HorasTeoria,M.HorasPractica,M.HorasLaboratorio')
            ->orderBy('M.Curso')
            ->asArray()->all();

        return json_encode($Materias);
    }

    /**
     * @throws InvalidConfigException
     */
    public function actionListarGrupos(){
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_CABECERA'], 'grupos' => '']);
        }

        if (!(isset($_SESSION["gestion"]) && ($_SESSION["gestion"] != "") &&
            isset($_POST["carrera"]) && isset($_POST["sede"]) && isset($_POST["curso"]) &&
            isset($_POST["plan"]) && isset($_POST["tipoGrupo"])
        )) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS'], 'grupos' => '']);
        }

        $gestion = intval($_SESSION['gestion']);

        $grupos = (new Query)
            ->select(['Chp.GestionAcademica as MGA','Chp.gestionAcademica as CGA',
                      'M.CodigoCarrera','M.NumeroPlanEstudios','M.Curso','M.SiglaMateria','M.NombreMateria',
                      'isnull(M.HorasTeoria,0) as HorasTeoria','isnull(M.HorasPractica,0) as HorasPractica','isnull(M.HorasLaboratorio,0) as HorasLaboratorio',
                      'Chp.Grupo','Chp.TipoGrupo','Chp.IdPersona',"isnull(P.Paterno,'') + ' ' + isnull(P.Materno,'') + ' ' + isnull(P.Nombres,'') as Nombre",'P.FechaNacimiento',
                      'isnull(Chp.Programados,0) as Programados',
                      'isnull(Chp.Aprobados,0) as Aprobados','isnull(Chp.Reprobados,0) as Reprobados','isnull(Chp.Abandonos,0) as Abandonos',
                      'isnull(Chp.ProyectadosGeneral,0) as CantidadProyeccion' ,'chp.CodigoEstado','Chp.Observaciones',
                      "case [[chp.TipoGrupo]] when 'T' THEN [[HorasTeoria]]
                                            when 'L' THEN [[HorasLaboratorio]]
                                            when 'P' THEN [[HorasPractica]]
                      end as HorasSemana"])
            ->from(['Materias M'])
            ->join('INNER JOIN', 'CargaHorariaPropuesta Chp', 'Chp.CodigoCarrera = M.CodigoCarrera and Chp.NumeroPlanEstudios = M.NumeroPlanEstudios and Chp.SiglaMateria = M.SiglaMateria')
            ->join('INNER JOIN','Personas P', 'P.IdPersona = Chp.IdPersona')
            ->where(['in','Chp.GestionAcademica',[(string)($gestion+1),'1/'.($gestion+1)]])
            ->andWhere(['M.CodigoCarrera' => $_POST["carrera"]])->andWhere(['Chp.CodigoSede' => $_POST['sede']])->andWhere(['M.NumeroPlanEstudios' => $_POST["plan"]])
            ->andWhere(['M.Curso' => $_POST["curso"]])->andWhere(['M.SiglaMateria' => $_POST["sigla"]])
            ->andWhere(['Chp.TipoGrupo' => $_POST['tipoGrupo']])
            ->groupBy('Chp.GestionAcademica,
                               M.CodigoCarrera,M.NumeroPlanEstudios,M.Curso,M.SiglaMateria,M.NombreMateria,
                               M.HorasTeoria,M.HorasPractica,M.HorasLaboratorio,
                               Chp.Grupo,Chp.TipoGrupo,Chp.IdPersona,P.Paterno,P.Materno,P.Nombres, 
                               Chp.Programados, Chp.Aprobados, Chp.reprobados, Chp.abandonos, Chp.proyectadosgeneral, chp.CodigoEstado,Chp.Observaciones,P.FechaNacimiento')
            ->orderBy("M.SiglaMateria,
                                case when (ISNUMERIC([Chp].[Grupo])) between 1 and 100 then  CONVERT(int,[Chp].[grupo]) end,
                                case when ([Chp].[Grupo]) between 'A' and 'Z' then  [Chp].[grupo] end,
                                case when ([Chp].[Grupo]) between 'a' and 'z' then  [Chp].[grupo] end
            ")->all(Yii::$app->get('dbAcademica'));

        return json_encode( ['respuesta' => Yii::$app->params['PROCESO_CORRECTO'], 'grupos' => $grupos]);
    }

    /**
     * @throws InvalidConfigException
     */
    public function actionListarDocentes() {
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_CABECERA'], 'docentes' => '']);
        }

        $search = '%' . str_replace(" ","%", $_POST['q'] ?? '') . '%';

        $docentes = (new Query)
            ->select(['convert(varchar(max),P.IdPersona) as id',"ltrim(rtrim(isnull(p.Paterno,''))) + ' ' + ltrim(rtrim(isnull(p.materno,''))) + ' ' + ltrim(rtrim(isnull(p.nombres,''))) as text",
                'cl.DescripcionCondicionLaboral as condicion'])
            ->from(['DetalleItemFuncionario Dif'])
            ->join('INNER JOIN', 'Items i', 'Dif.NroItem = I.NroItem')
            ->join('INNER JOIN','Cargos C', 'c.IdCargo = i.IdCargo and C.CodigoSectorTrabajo = i.CodigoSectorTrabajo')
            ->join('INNER JOIN','Organigrama U', 'U.IdUnidad = i.IdUnidad ')
            ->join('INNER JOIN','Funcionarios F', 'F.IdFuncionario = Dif.IdFuncionario and f.CodigoSectorTrabajo = i.CodigoSectorTrabajo')
            ->join('INNER JOIN','Personas P', 'P.IdPersona = F.IdPersona')
            ->join('INNER JOIN','CondicionesLaborales Cl', 'Cl.CodigoCondicionLaboral = Dif.CodigoCondicionLaboral')
            ->where(['Dif.CodigoEstadoCargo' => 'V'])->andWhere(['i.CodigoSectorTrabajo' => 'DOC'])->andWhere(['i.EstadoCargoUnidad' => 'V'])
            ->andWhere(['c.CodigoEstadoCargo' => 'V'])
            ->andWhere(['u.CodigoEstadoUnidad' => 'V'])
            ->andWhere(['F.CodigoEstadoFuncionario' => 'V'])
            ->andWhere(['like',"ltrim(rtrim(isnull(p.Paterno,''))) + ' ' + ltrim(rtrim(isnull(p.materno,''))) + ' ' + ltrim(rtrim(isnull(p.nombres,''))) + ' ' + p.IdPersona " ,$search,false])
            ->groupBy('convert(varchar(max),[[P.IdPersona]]),P.Paterno,P.Materno,P.Nombres,cl.DescripcionCondicionLaboral')
            ->orderBy('p.Paterno,P.Materno,P.Nombres')
            ->all( Yii::$app->get('dbrrhh') );

        if (!$docentes) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'], 'docentes' => '']);
        }

        return json_encode( ['respuesta' => Yii::$app->params['PROCESO_CORRECTO'], 'docentes' =>  $docentes]);
    }

    public function actionObtenerChPersona() {
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return 'ERROR_CABECERA';
        }

        $gestion = intval($_SESSION['gestion']);

        $chPersona = CargaHorariaPropuesta::find()->alias('Chp')
            ->select([
                "isnull(sum(case when [Chp].[CodigoEstado] = 'V' then HorasSemana * 4 end),0) as vigente",
                "isnull(sum(case when [Chp].[CodigoEstado] = 'E' then HorasSemana * 4 end),0) as eliminada",
                "isnull(sum(case when [Chp].[CodigoEstado] in ('A','C') then HorasSemana * 4 end),0) as agregada"
            ])->where(['in','Chp.GestionAcademica',[(string)($gestion+1), '1/' . ($gestion + 1)]])
            ->andWhere(['Chp.Idpersona' => $_POST['persona']])
            ->asArray()->one();

        $chReal = vCargaHoraria::find()->alias('V')
            ->select(['SUM(HorasSemana)*4 as ch','V.CondicionLaboral as condicion','V.AniosAntiguedad as antiguedad'])
            ->where(['V.IdPersona' => $_POST['persona']])
            ->andWhere(['!=','V.CodigoCarrera','0'])
            ->groupBy('V.CondicionLaboral,V.AniosAntiguedad')
            ->asArray()->one();

        $chDetallada = CargaHorariaPropuesta::find()->alias('Chp')
            ->select(['ltrim(rtrim(Chp.IdPersona)) as IdPersona','M.CodigoCarrera as codigo','c.NombreCortoCarrera as carrera','Chp.siglaMateria as materia',
                'M.NombreMateria',"sum(case Chp.TipoGrupo
                                                     when 'T' THEN m.HorasTeoria *4
                                                     when 'L' THEN m.HorasLaboratorio *4
                                                     when 'P' THEN m.HorasPractica *4
                                                 end) as Ch"])
            ->join('INNER JOIN', 'Materias M', 'Chp.CodigoCarrera = M.CodigoCarrera and Chp.NumeroPlanEstudios = M.NumeroPlanEstudios and Chp.SiglaMateria = M.SiglaMateria ')
            ->join('INNER JOIN', 'Carreras c','M.CodigoCarrera = c.CodigoCarrera')
            ->where(['in','Chp.GestionAcademica',[(string)($gestion+1), '1/' . ($gestion + 1)]])
            ->andWhere(['Chp.Idpersona' => $_POST['persona']])
            ->andWhere(['chp.TransferidoCargaHoraria' => 1])->andWhere(['Chp.CodigoEstado' => 'V'])
            ->groupBy('Chp.IdPersona,M.CodigoCarrera,c.NombreCortoCarrera,Chp.SiglaMateria, M.NombreMateria')
            ->asArray()->all();

        return json_encode( ['respuesta' => Yii::$app->params['PROCESO_CORRECTO'], 'ch' => $chPersona, 'chReal' => $chReal, 'chDetallada' => $chDetallada] );
    }


    /**
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionCambiarEstadoGrupo(){
        if ($this->checkHeader(Yii::$app->request,$_SESSION,$_POST) !== 'ok')
            return $this->checkHeader(Yii::$app->request,$_SESSION,$_POST);

        $gestion = intval($_SESSION['gestion']);

        $row = CargaHorariaPropuesta::findOne([
                'GestionAcademica' => [(string)($gestion+1),'1/'.($gestion+1)],
                'CodigoCarrera' => intval($_POST['carrera']),
                'CodigoSede' => $_POST['sede'],
                'NumeroPlanEstudios' => intval($_POST['plan']),
                'SiglaMateria' => $_POST["sigla"],
                'TipoGrupo' => $_POST["tipoGrupo"],
                'Grupo' => $_POST["grupo"],
                'CodigoEstado' => $_POST['estado']
        ]);

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

    public function actionVerificarGrupo(): bool
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

    /**
     * @throws Exception
     */
    public function actionGuardarGrupo()
    {
        if ($this->checkHeader(Yii::$app->request,$_SESSION,$_POST) !== 'ok')
            return $this->checkHeader(Yii::$app->request,$_SESSION,$_POST);

        $gestion = intval($_SESSION['gestion']);

        $materiaDocente = MateriaDocente::findOne([
            'GestionAcademica' => (string)($gestion+1),'1/'.($gestion+1),
            'CodigoCarrera' => intval($_POST['carrera']),
            'CodigoSede' => $_POST['sede'],
            'NumeroPlanEstudios' => intval($_POST['plan']),
            'SiglaMateria' => $_POST['sigla'],
            'CodigoTipoGrupoMateria' => $_POST['tipoGrupo'],
            'CodigoModalidadCurso' => 'NA','NA'
        ]);

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
            default: $horas = $materia->HorasTeoria;
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
        if ($this->checkHeader(Yii::$app->request,$_SESSION,$_POST) !== 'ok')
            return $this->checkHeader(Yii::$app->request,$_SESSION,$_POST);

        $gestion = intval($_SESSION['gestion']);

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

    /**
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionActualizarGrupo(){
        if ($this->checkHeader(Yii::$app->request,$_SESSION,$_POST) !== 'ok')
            return $this->checkHeader(Yii::$app->request,$_SESSION,$_POST);

        $gestion = intval($_SESSION['gestion']);

        $row = CargaHorariaPropuesta::findOne([
            'GestionAcademica' => [(string)($gestion+1),'1/'.($gestion+1)],
            'CodigoCarrera' => intval($_POST['carrera']),
            'CodigoSede' => $_POST['sede'],
            'NumeroPlanEstudios' => intval($_POST['plan']),
            'SiglaMateria' => $_POST["sigla"],
            'TipoGrupo' => $_POST["tipoGrupo"],
            'Grupo' => $_POST["grupo"]
        ]);

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

    /**
     * @throws Exception
     */
    public function actionEnviarCargahoraria(){
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_CABECERA']]);
        }

        if (!(isset($_SESSION["gestion"]) && ($_SESSION["gestion"] != "") &&
            isset($_POST["carrera"]) && isset($_POST["sede"]) && isset($_POST["plan"]))
        ) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS']]);
        }

        $envio = new ControlEnvioPlanificacionCH();
        $envio->GestionAcademica = $_SESSION["gestion"];
        $envio->CodigoCarrera = $_POST["carrera"];
        $envio->CodigoSede = $_POST["sede"];
        $envio->NumeroPlanEstudios = $_POST["plan"];
        $envio->CodigoEstado = '1';

        $envio->save();

        return json_encode( ['respuesta' => Yii::$app->params['PROCESO_CORRECTO']]);
    }

    public function checkHeader($request,$session,$post){
        if (!($request->isAjax && $request->isPost)) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_CABECERA']]);
        }

        if (!(isset($session["gestion"]) && ($session["gestion"] != "") &&
            isset($post["carrera"]) && isset($post["sede"]) && isset($post["plan"]) &&
            isset($post["grupo"]) && isset($post["tipoGrupo"])
        )) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS']]);
        }

        return 'ok';
    }
}