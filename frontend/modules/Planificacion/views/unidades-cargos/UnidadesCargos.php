<?php
use yii\web\JqueryAsset;

app\modules\Planificacion\assets\PlanificacionAsset::register($this);


$this->registerJsFile("@web/js/unidades-cargos/unidadescargos.js", [
    'depends' => [
        JqueryAsset::class
    ]
]);
$this->title = 'SOA';
$this->params['breadcrumbs'] = [['label' => 'UnidadesCArgos']];
?>

<div class="card ">
    <div class="card-header">
        <button id="btnMostrarCrearUnidad" class="btn btn-primary bg-gradient-primary">
            <div class="icon closed">
                <div class="circle">
                    <div class="horizontal"></div>
                    <div class="vertical"></div>
                </div>
                Agregar Unidad
            </div>
        </button>
    </div>
    <div id="IngresoDatos" class="card-body">
        <div class="col d-flex justify-content-center">
            <div class="card " style="width: 80rem;">
                <div class="card-header bg-gradient-primary">Ingreso Datos</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="form-group">
                                        <div class="searchArea">
                                            <button type="button" id="search"
                                                class="btn btn-primary btn-sm">Search</button>
                                            <div class="inputDiv">
                                                <input id="search-term" class="form-control input-sm"
                                                    placeholder="Buscar" autofocus>
                                            </div>
                                        </div>
                                        <label for="tree1">Seleccione unidad padre</label>
                                        <div id="unidadPadre"> </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="card">
                                <div class="card-body">
                                    <table
                                        class="table table-bordered table-striped dt-responsive tablaListaCargos "
                                        style="width: 100%">
                                        <thead>
                                            <th style="text-align: center; vertical-align: middle;">#</th>
                                            <th style="text-align: center; vertical-align: middle;">Cargo</th>
                                            <th style="text-align: center; vertical-align: middle;">#</th>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <button class='btn btn-primary bg-gradient-primary btnGuardar'><i
                            class='fa fa-check-circle-o'>Guardar</i></button>
                    <button class='btn btn-danger btn- btnCancel'><i class='fa fa-warning'>Cancelar</i></button>
                </div>
            </div>
        </div>
    </div>
    <div id="Divtabla" class="card-body">
        <table class="table table-bordered table-striped dt-responsive tablaListaUnidadesCargos" style="width: 100%">
            <thead>
                <th style="text-align: center; vertical-align: middle;">#</th>
                <th style="text-align: center; vertical-align: middle;">Unidad</th>
                <th style="text-align: center; vertical-align: middle;">Cargo</th>
                <th style="text-align: center; vertical-align: middle;">Sector</th>
                <th style="text-align: center; vertical-align: middle;">Estado</th>
                <th style="text-align: center; vertical-align: middle; width: 140px">Acciones</th>
            </thead>
        </table>
    </div>
</div>