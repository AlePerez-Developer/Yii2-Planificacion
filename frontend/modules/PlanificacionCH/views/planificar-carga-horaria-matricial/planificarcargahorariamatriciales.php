<?php

use app\modules\PlanificacionCH\assets\PlanificacionCHAsset;
use app\modules\PlanificacionCH\assets\planificacionChMatricialjs;

PlanificacionCHAsset::register($this);
planificacionChMatricialjs::register($this);

$this->title = 'Administración Planificación C.H. Matriciales';
$this->params['breadcrumbs'] = [['label' => ' / Admin. Planificación C.H. Matriciales']];
?>

<style>
    .select2-results__option{
        font-size: 12px;
    }
    .select2-selection{
        font-size: 12px
    }
    td.details-control {
        text-align:center;
        color:forestgreen; !important;
        cursor: pointer;
    }
    tr.shown td.details-control {
        text-align:center;
        color:red;
    }

    #tablaMaterias thead th{
        text-align: center; !important;
        vertical-align: middle; !important;
        background-color: #2d4b73; !important;
        color: white;
    }

    #tablaMaterias tr td{
        background-color:  #EAF3FA; !important;
    }

    #tablaTeoria thead th{
        text-align: center; !important;
        vertical-align: middle; !important;
        background-color:  #99ab69; !important;
        color: white;
    }

    #tablaLaboratorio thead th{
        text-align: center; !important;
        vertical-align: middle; !important;
        background-color:  #99ab69; !important;
        color: white;
    }

    #tablaPractica thead th{
        text-align: center; !important;
        vertical-align: middle; !important;
        background-color:  #99ab69; !important;
        color: white;
    }

    table tbody tr.vigente td {
        background-color: whitesmoke !important;
    }

    table tbody tr.editado td {
        background-color: #FFEAAF !important;
    }

    table tbody tr.eliminado td {
        background-color: #FFB9B9 !important;
    }

    table tbody tr.agregado td {
        background-color: #CFE4C2 !important;
    }

    .flex-container {
        display: flex;
    }
    .flex-child {
        flex: 1;
        border: 2px solid yellow;
        width: 20px;
        height: 20px;
    }
    .flex-child:first-child {
        margin-right: 20px;
    }



    .popover{
        max-width: 100%; /* Max Width of the popover (depending on the container!) */
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

    .articles {
        font-size: 10px;
        color: #a1aab9
    }

    .number1 {
        font-weight: 500
    }

    .followers {
        font-size: 10px;
        color: #a1aab9
    }

    .number2 {
        font-weight: 500
    }

    .rating {
        font-size: 10px;
        color: #a1aab9
    }

    .number3 {
        font-weight: 500
    }

    .divGrupos {
        overflow-y: auto;
        overflow-x: hidden;
        height: 360px;
        width: 100%
    }


</style>

<div class="card">
    <div class="card-header">
        <div class="row" style="background-color: #f8bb86; height: 90px;">
            <div class="col-sm-6">
                <label for="facultades" style="font-size: 12px">Seleccione la facultad</label>
                <select id="facultades" name="facultades" style="width: 95%">
                    <option></option>
                </select>
            </div>

            <div class="col-sm-6" id="divMaterias" hidden>
                <label for="materias" style="font-size: 12px">Seleccione la materia</label>
                <select id="materias" name="materias" class="form-control" style="width: 95%">
                    <option></option>
                </select>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div id="divTabla"  hidden>
            <div style="background-color: white">
                <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="pills-teoria-tab" data-bs-toggle="pill" data-bs-target="#pills-teoria" type="button" role="tab" aria-controls="pills-home" aria-selected="true">Grupos Teoria</button>
                        </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-laboratorio-tab" data-bs-toggle="pill" data-bs-target="#pills-laboratorio" type="button" role="tab" aria-controls="pills-profile" aria-selected="false">Grupos Laboratorio</button>
                        </li>
                      <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-practica-tab" data-bs-toggle="pill" data-bs-target="#pills-practica" type="button" role="tab" aria-controls="pills-contact" aria-selected="false">Grupos Practica</button>
                        </li>
                </ul>
                <div class="tab-content" id="pills-tabContent" >
                    <div class="tab-pane fade show active" id="pills-teoria" role="tabpanel" aria-labelledby="pills-home-tab">
                        <div class="row"><div class="col-10"></div><div class="col-2"><button type="button" grupo = "T" class="form-control btn-xs btn-info btnCrear">Crear Grupo de Teoria</button></div></div>
                            <table id="tablaTeoriaMatricial" class="table table-bordered  dt-responsive tablaTeoria" style="width: 100%" >
                                <thead>
                                    <th>#</th>
                                    <th>IdPersona</th>
                                    <th>Nombre Docente</th>
                                    <th>Grupo</th>
                                    <th>Hrs.Teo</th>
                                    <th>Prog.</th>
                                    <th>Aprobados</th>
                                    <th>Reprobados</th>
                                    <th>Abandonos</th>
                                    <th>Proy.</th>
                                    <th>Accion</th>
                               </thead>
                            </table>
                    </div>
                    <div class="tab-pane fade" id="pills-laboratorio" role="tabpanel" aria-labelledby="pills-profile-tab">
                        <div class="row"><div class="col-10"></div><div class="col-2"><button type="button" grupo = "L" class="form-control btn-xs btn-info btnCrear">Crear Grupo de Laboratorio</button></div></div>
                            <table id="tablaLaboratorioMatricial" class="table table-bordered  dt-responsive" style="width: 100%" >
                                <thead>
                                    <th>#</th>
                                    <th>IdPersona</th>
                                    <th>Nombre Docente</th>
                                    <th>Grupo</th>
                                    <th>Hrs.Lab</th>
                                    <th>Prog.</th>
                                    <th>Aprobados</th>
                                    <th>Reprobados</th>
                                    <th>Abandonos</th>
                                    <th>Proy.</th>
                                    <th>Accion</th>
                                </thead>
                            </table>
                        </div>
                    <div class="tab-pane fade" id="pills-practica" role="tabpanel" aria-labelledby="pills-contact-tab">
                        <div class="row"><div class="col-10"></div><div class="col-2"><button type="button" grupo = "P" class="form-control btn-xs btn-info btnCrear">Crear Grupo de Practica</button></div></div>
                            <table id="tablaPracticaMatricial" class="table table-bordered  dt-responsive" style="width: 100%" >
                                <thead>
                                    <th>#</th>
                                    <th>IdPersona</th>
                                    <th>Nombre Docente</th>
                                    <th>Grupo</th>
                                    <th>Hrs.Prac</th>
                                    <th>Prog.</th>
                                    <th>Aprobados</th>
                                    <th>Reprobados</th>
                                    <th>Abandonos</th>
                                    <th>Proy.</th>
                                    <th>Accion</th>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
        </div>
    </div>
</div>

<?php include_once "modalPlanificar.php"; ?>