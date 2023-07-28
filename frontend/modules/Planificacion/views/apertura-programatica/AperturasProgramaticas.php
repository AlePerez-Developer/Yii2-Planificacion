<?php

use yii\web\JqueryAsset;
app\modules\Planificacion\assets\PlanificacionAsset::register($this);

$this->registerJsFile("@web/js/apertura-programatica/AperturaProgramatica.js", [
    'depends' => [
        JqueryAsset::className()
    ]
]);
$this->title = 'Planificacion';
$this->params['breadcrumbs'] = [['label' => 'Aperturas Programaticas']];
?>

<div class="card">
    <div class="card-header">
        <button id="btnMostrarCrear" class="btn btn-primary bg-gradient-primary">
            <div class="icon closed">
                <div class="circle">
                    <div class="horizontal"></div>
                    <div class="vertical"></div>
                </div>
                Agregar Apertura Programatica
            </div>
        </button>
    </div>
    <div id="ingresoDatos" class="card-body">
        <div class="col d-flex justify-content-center">
            <div class="card " style="width: 50rem;">
                <div class="card-header bg-gradient-primary">Ingreso Datos</div>
                <div class="card-body">
                    <input type="text" id="codigo" name="codigo" disabled hidden>
                    <form id="formAperturasProgramaticas" action="" method="post">

                        <div class="form-group">
                            <label for="unidad">Seleccione una unidad</label>
                            <select class="form-control unidad" id="unidad" name="unidad" >
                                <option></option>
                                <?php foreach ($unidades as $unidad){  ?>
                                    <option value="<?= $unidad->CodigoUnidad ?>"><?= '('.  $unidad->Da . '-' . $unidad->Ue .') - ' . $unidad->Descripcion  ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="programa">Seleccione un programa</label>
                            <select class="form-control programa" id="programa" name="programa" >
                                <option></option>
                                <?php foreach ($programas as $programa){  ?>
                                    <option value="<?= $programa->CodigoPrograma ?>"><?= '('.  $programa->Codigo .') - ' . $programa->Descripcion  ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="proyecto">Seleccione un proyecto</label>
                            <select class="form-control proyecto" id="proyecto" name="proyecto" >
                                <option></option>
                                <?php foreach ($proyectos as $proyecto){  ?>
                                    <option value="<?= $proyecto->CodigoProyecto ?>"><?= '('.  $proyecto->Codigo .') - ' . $proyecto->Descripcion  ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="actividad">Seleccione una actividad</label>
                            <select class="form-control actividad" id="actividad" name="actividad" >
                                <option></option>
                                <?php foreach ($actividades as $actividad){  ?>
                                    <option value="<?= $actividad->CodigoActividad ?>"><?= '('.  $actividad->Codigo .') - ' . $actividad->Descripcion  ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="descripcion">Descripcion</label>
                            <textarea class="form-control input-sm txt" rows="4" id="descripcion" name="descripcion" placeholder="Descripcion"></textarea>
                        </div>

                        <div class="form-group ml-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="organizacional">
                                <label class="form-check-label" for="organizacional">Unidad Organizacional?</label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <button id="btnGuardar" name="btnGuardar" class='btn btn-primary bg-gradient-primary'><i class='fa fa-check-circle-o'>Guardar</i>
                    </button>
                    <button id="btnCancelar" name="btnCancelar" class='btn btn-danger'><i class='fa fa-warning'>Cancelar</i></button>
                </div>
            </div>
        </div>
    </div>
    <div id="divTabla" name="divTabla" class="card-body">
        <table id="tablaListaAperturasProgramaticas"  class="table table-bordered table-striped dt-responsive" >
            <thead>
            <th>#</th>
            <th>#</th>
            <th>Da</th>
            <th>Ue</th>
            <th>Prg</th>
            <th>Pry</th>
            <th>Act</th>
            <th>Apertura Programatica</th>
            <th>Descripcion</th>
            <th>Organizacional</th>
            <th>Estado</th>
            <th>Acciones</th>
            </thead>
        </table>
    </div>
</div>


