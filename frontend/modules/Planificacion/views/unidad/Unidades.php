<?php

use yii\web\JqueryAsset;

app\modules\Planificacion\assets\PlanificacionAsset::register($this);

$this->registerJsFile("@web/js/unidad/Unidad.js", [
    'depends' => [
        JqueryAsset::className()
    ]
]);
$this->title = 'Planificacion';
$this->params['breadcrumbs'] = [['label' => 'Unidades']];
?>

<div class="card">
    <div class="card-header">
        <button id="btnMostrarCrear" class="btn btn-primary bg-gradient-primary">
            <div class="icon closed">
                <div class="circle">
                    <div class="horizontal"></div>
                    <div class="vertical"></div>
                </div>
                Agregar Unidad
            </div>
        </button>
    </div>
    <div id="ingresoDatos" class="card-body">
        <div class="col d-flex justify-content-center">
            <div class="card " style="width: 50rem;">
                <div class="card-header bg-gradient-primary">Ingreso Datos</div>
                <div class="card-body">
                    <input type="text" id="codigo" name="codigo" disabled hidden>
                    <form id="formUnidades" action="" method="post">

                        <div class="form-group">
                            <label for="unidad" class="control-label">Unidad</label>
                            <div class="container" id="unidad">
                                <div class="row">
                                    <div class="col-2">
                                        <input type="text" id="da" name="da" class="form-control num" maxlength="2" placeholder="Da" style="width: 90px">
                                    </div>
                                    <div class="col-2">
                                        <input type="text" id="ue" name="ue" class="form-control num" maxlength="3" placeholder="Ue" style="width: 120px" >
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="descripcion">Descripcion</label>
                            <textarea class="form-control input-sm txt" rows="4" id="descripcion" name="descripcion" placeholder="Descripcion"></textarea>
                        </div>

                        <div class="form-group">
                            <div class="row">
                                <div class="col">
                                    <div class="form-group">
                                        <label for="fechaInicio" class="control-label">Fecha de inicio</label>
                                        <input type="date" class="form-control input-sm" id="fechaInicio" name="fechaInicio"
                                               pattern="\d{4}-\d{2}-\d{2}">
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-group">
                                        <label for="fechaFin" class="control-label">Fecha de fin</label>
                                        <input type="date" class="form-control input-sm" id="fechaFin" name="fechaFin"
                                               pattern="\d{4}-\d{2}-\d{2}">
                                    </div>
                                </div>
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
        <table id="tablaListaUnidades"  class="table table-bordered table-striped dt-responsive" >
            <thead>
            <th>#</th>
            <th>#</th>
            <th>Da</th>
            <th>Ue</th>
            <th>Descripcion</th>
            <th>Estado</th>
            <th>Acciones</th>
            </thead>
        </table>
    </div>
</div>

