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
</style>
<div class="card">
    <div class="mt-2 ml-3 mb-3">
        <div class="card-header">
            <div class="row" style="background-color: #f8bb86; height: 90px" >
                <div class="col-sm-6">
                    <label for="facultades" style="font-size: 12px">Seleccione la facultad</label>
                    <select id="facultades" name="facultades" style="width: 95%">
                        <option></option>
                        <?php
                        foreach ($facultades as $codigo => $nombre) {
                            echo "<option value='" . $codigo . "'>" . $nombre . "</option>";
                        }
                        ?>
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
                <table id="tablaMaterias" class="table table-bordered table-striped dt-responsive tablaMateriass" style="width: 100%" >
                    <thead>
                    <th style="text-align: center; vertical-align: middle;">#</th>
                    <th style="text-align: center; vertical-align: middle;">Sigla</th>
                    <th style="text-align: center; vertical-align: middle;">Materia</th>
                    <th style="text-align: center; vertical-align: middle;">Horas Teoricas</th>
                    <th style="text-align: center; vertical-align: middle;">Horas Practicas</th>
                    <th style="text-align: center; vertical-align: middle;">Horas Laboratorio</th>
                    <th style="text-align: center; vertical-align: middle;">Proyecccion Estudiantes</th>
                    <th style="text-align: center; vertical-align: middle;">Programados</th>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
