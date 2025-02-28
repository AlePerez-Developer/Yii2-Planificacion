<?php

use app\modules\PlanificacionCH\assets\PlanificacionCHAsset;
use app\modules\PlanificacionCH\assets\PlanificacionChjs;

PlanificacionCHAsset::register($this);
PlanificacionChjs::register($this);

$this->registerCssFile('@web/css/planificar-carga-horaria/view.css');
$this->registerCssFile('@web/css/planificar-carga-horaria/s2-style.css');
$this->registerCssFile('@web/css/planificar-carga-horaria/dt-materias.css');
$this->registerCssFile('@web/css/planificar-carga-horaria/dt-grupos.css');

$this->title = 'Administración Planificación C.H.';
$this->params['breadcrumbs'] = [['label' => ' / Admin. Planificación C.H.']];
?>

<style>
    .popover{
        max-width: 100%; /* Max Width of the popover (depending on the container!) */
    }

    .btnClose{
        cursor: pointer;
    }

    .popover span {
        font-size: 12px;
        color: black;
    }

    .image span {
        color: black;
        font-size: 11px;
    }

    .card2 {
        width: 400px;
        border: none;
        border-radius: 10px;
        background-color: #fff
    }

    .stats {
        background: #f2f5f8 !important;
        color: #000 !important
    }

    .chValue {
        font-weight: 500
    }

    .chTitle {
        font-size: 10px;
        color: #a1aab9
    }

    .list-group{
        font-size: 12px;
    }

    .list-group-item {
        padding: 3px 10px
    }

    .c{
        font-size: 12px;
        border: none;
        width: 300px;
    }

    .badge {
        color: whitesmoke !important;
        font-size: 11px !important;
    }

</style>
<div>
    <div hidden >
        <label for="gestion">Gestion</label>
        <input id="gestion" name="gestion" value=<?=date("Y")-1?> >
        <input id="nivel" name="nivel" value="<?=(Yii::$app->user->identity->esDirector)?'1':'0'?>" >
        <input id="envio" name="envio" value="0" >
    </div>
</div>
<div class="card">
    <div class="card-header">
        <div id="rowUno" class="row">
            <div class="col-sm-6">
                <label for="facultades" class="lblTitulo">Seleccione la facultad</label>
                <select id="facultades" name="facultades" class="form-control">
                    <option></option>
                </select>
            </div>

            <div class="col-sm-6" id="divCarreras" hidden>
                <label for="carreras" class="lblTitulo">Seleccione la carrera</label>
                <select id="carreras" name="carreras" class="form-control">
                    <option></option>
                </select>
            </div>
        </div>
        <div id="rowDos" class="row" hidden>
            <div class="col-sm-4 mt-2" id="divSedes" hidden>
                <label for="sedes" class="lblTitulo">Seleccione la sede</label>
                <select id="sedes" name="sedes" class="form-control">
                    <option></option>
                </select>
            </div>

            <div class="col-sm-4  mt-2" id="divPlanes" hidden>
                <label for="planes" class="lblTitulo">Seleccione el plan de estudios</label>
                <select id="planes" name="planes" class="form-control">
                    <option></option>
                </select>
            </div>

            <div class="col-sm-4  mt-2" id="divCursos" hidden>
                <label for="cursos" class="lblTitulo">Seleccione el curso</label>
                <select id="cursos" name="cursos" class="form-control">
                    <option></option>
                </select>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div id="divTabla"  hidden>
            <div class='row'>
                <div class="col-9"></div>
                <div class="col-3">
                    <button id="enviarPlanificacion" class="btn btn-info form-control btn-xs" >Enviar Carrera</button>
                </div>
            </div>
            <table id="tablaMaterias" class="table table-bordered  dt-responsive" >
                <thead>
                    <th>#</th>
                    <th>Sigla</th>
                    <th>Materia</th>

                    <th>Hrs.T</th>
                    <th>Hrs.P</th>
                    <th>Hrs.L</th>

                    <th>Prog.T</th>
                    <th>Prog.L</th>
                    <th>Prog.P</th>

                    <th>Apro.T</th>
                    <th>Apro.L</th>
                    <th>Apro.P</th>

                    <th>Repro.T</th>
                    <th>Repro.L</th>
                    <th>Repro.P</th>

                    <th>Aban.T</th>
                    <th>Aban.L</th>
                    <th>Aban.P</th>

                    <th>Proy.</th>
                </thead>
            </table>
        </div>
        <button id="jijo" data-columns="3" data-index-number="12314" data-parent="cars">prueba</button>
    </div>
</div>

<?php include_once "modalPlanificar.php"; ?>


