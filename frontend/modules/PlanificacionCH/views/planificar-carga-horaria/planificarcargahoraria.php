<?php

use app\modules\PlanificacionCH\assets\PlanificacionCHAsset;
use app\modules\PlanificacionCH\assets\PlanificacionChjs;

PlanificacionCHAsset::register($this);
PlanificacionChjs::register($this);

$this->title = 'Administración Planificación C.H.';
$this->params['breadcrumbs'] = [['label' => ' / Admin. Planificación C.H.']];
?>

<style>

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


    #rowUno{
        background-color: #f8bb86;
        height: 90px;
    }

    #rowDos{
        background-color: peachpuff;
        height: 90px;
    }

    .select2-results__option{
        font-size: 12px;
        font-weight: bold;
    }
    .select2-selection{
        font-size: 12px;
        font-weight: bold;
        background-color: #1C005A; !important;
    }

    .lblTitulo {
        font-size: 12px;
    }
</style>
<div>
<?php var_dump(Yii::$app->user->identity->esDirector);?>
    <div >
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
            <table id="tablaMaterias" class="table table-bordered  dt-responsive tablaMateriass" style="width: 100%" >
                <thead>
                <th>#</th>
                <th>Sigla</th>
                <th>Materia</th>
                <th>Hrs.Teo</th>
                <th>Hrs.Pra</th>
                <th>Hrs.Lab</th>
                <th>Prog.</th>
                <th>Apro.</th>
                <th>Repro.</th>
                <th>Abandonos</th>
                <th>Proy.</th>
                </thead>
            </table>
        </div>
    </div>
</div>

<?php include_once "modalPlanificar.php"; ?>


