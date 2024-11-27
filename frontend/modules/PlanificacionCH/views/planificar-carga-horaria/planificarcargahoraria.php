<?php
use yii\web\JqueryAsset;

app\modules\PlanificacionCH\assets\PlanificacionCHAsset::register($this);

$this->registerJsFile("@planificacionCHModule/js/planificar-carga-horaria/planificarcargahoraria.js", [
    'depends' => [
        JqueryAsset::className()
    ]
]);

$this->title = 'Administración Planificación C.H.';
$this->params['breadcrumbs'] = [['label' => 'Admin. Planificación C.H.']];
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


    #tablaTeoria tr td{
        background-color:  whitesmoke; !important;
    }

    #tablaLaboratorio tr td{
        background-color:  whitesmoke; !important;
    }

    #tablaPractica tr td{
        background-color:  whitesmoke; !important;
    }

</style>
<div class="card">
    <div class="mt-2 ml-3 mb-3">
        <div class="card-header">
            <div class="row" style="background-color: #f8bb86; height: 90px" >
                <div class="col-sm-6">
                    <label for="facultades" style="font-size: 12px">Seleccione la facultad</label>
                    <select id="facultades" name="facultades" style="width: 95%">
                        <option></option>
                    </select>
                </div>

                <div class="col-sm-6" id="divCarreras" hidden>
                    <label for="carreras" style="font-size: 12px">Seleccione la carrera</label>
                    <select id="carreras" name="carreras" class="form-control" style="width: 95%">
                        <option></option>
                    </select>
                </div>

            </div>
            <div id="rowDos" name="rowDos" class="row" style="background-color: peachpuff; height: 90px" hidden>
                <div class="col-sm-4 mt-2" id="divSedes" hidden>
                    <label for="sedes" style="font-size: 12px">Seleccione la sede</label>
                    <select id="sedes" name="sedes" class="form-control" style="width: 95%">
                        <option></option>
                    </select>
                </div>

                <div class="col-sm-4 mt-2" id="divPlanes" hidden>
                    <label for="planes" style="font-size: 12px">Seleccione el plan de estudios</label>
                    <select id="planes" name="planes" class="form-control" style="width: 95%">
                        <option></option>
                    </select>
                </div>
                <div class="col-sm-4 mt-2" id="divCursos" hidden>
                    <label for="cursos" style="font-size: 12px">Seleccione el curso</label>
                    <select id="cursos" name="cursos" class="form-control" style="width: 95%">
                        <option></option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div id="divTabla" class="card-body" hidden>
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
</div>
<button type="button" class="form-control btn btn-outline-info" id="rrrr"><i class="fa-plus-square">orale</i></button>

<?php include_once "modalPlanificar.php"; ?>
