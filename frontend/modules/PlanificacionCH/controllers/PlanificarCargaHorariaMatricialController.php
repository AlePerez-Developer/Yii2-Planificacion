<?php

namespace app\modules\PlanificacionCH\controllers;

use yii\web\Controller;

class PlanificarCargaHorariaMatricialController extends Controller
{
    public function actionIndex()
    {
        return $this->render('planificarcargahorariamatriciales');
    }

    public function actionListarMateriasMatriciales(){
        if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_CABECERA']]);
        }

        if (!(isset($_POST["gestion"]) && ($_POST["gestion"] != "") &&
            isset($_POST["carrera"]) && isset($_POST["sede"]) && isset($_POST["curso"]) &&
            isset($_POST["plan"]) && isset($_POST["flag"])
        )) {
            return json_encode(['respuesta' => Yii::$app->params['ERROR_ENVIO_DATOS']]);
        }

        $gestion = intval($_POST['gestion']);

        if ($_POST['flag'] == 0)
            return json_encode( ['respuesta' => Yii::$app->params['PROCESO_CORRECTO']]);

        Yii::$app->session->set('curso', $_POST['curso']);
        Yii::$app->session->set('sede', $_POST['sede']);
        $Materias = Materia::find()->alias('M')
            ->select(['Md.GestionAcademica','M.CodigoCarrera','M.NumeroPlanEstudios','M.Curso','M.SiglaMateria','M.NombreMateria','Md.CodigoModalidadCurso',
                'isnull(M.HorasTeoria,0) as HorasTeoria','isnull(M.HorasPractica,0) as HorasPractica','isnull(M.HorasLaboratorio,0) as HorasLaboratorio',
                'SUM(Chp.Programados) AS [Programados]', 'SUM(Chp.Aprobados) AS [Aprobados]', 'SUM(chp.Reprobados) AS [Reprobados]', 'SUM(Chp.Abandonos) AS [Abandonos]',
                'chp.ProyectadosGeneral as CantidadProyeccion'])
            ->join('INNER JOIN','MateriasDocentes Md', 'Md.CodigoCarrera = M.CodigoCarrera and Md.NumeroPlanEstudios = M.NumeroPlanEstudios and Md.SiglaMateria = M.SiglaMateria')
            ->join('INNER JOIN','CargaHorariaPropuesta Chp', 'Chp.CodigoCarrera = M.CodigoCarrera and Chp.NumeroPlanEstudios = M.NumeroPlanEstudios and Chp.SiglaMateria = M.SiglaMateria and Md.CodigoTipoGrupoMateria = Chp.TipoGrupo and Md.Grupo = Chp.Grupo')
            ->where(['in','Md.GestionAcademica',[(string)$gestion,'1/'.$gestion]])
            ->andWhere(['M.CodigoCarrera' => $_POST["carrera"]])->andWhere(['M.Curso' => $_POST["curso"]])
            ->andWhere(['M.NumeroPlanEstudios' => $_POST["plan"]])->andWhere(['Md.CodigoSede' => $_POST["sede"]])
            ->andWhere("Md.CodigoModalidadCurso in ('NS','NA')")
            ->groupBy('Md.GestionAcademica,M.CodigoCarrera,M.NumeroPlanEstudios,M.Curso,M.SiglaMateria,M.NombreMateria,Md.CodigoModalidadCurso,M.HorasTeoria,M.HorasPractica,M.HorasLaboratorio, chp.ProyectadosGeneral')
            ->orderBy('M.SiglaMateria')
            ->asArray()
            ->all();

        return json_encode($Materias);
    }


}