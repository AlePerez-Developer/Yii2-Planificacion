<?php
use yii\web\JqueryAsset;

app\modules\Planificacion\assets\PlanificacionAsset::register($this);

$this->registerJsFile("@web/js/unidades-soa/UnidadesSoa.js",[
    'depends' => [
        JqueryAsset::className()
    ]
]);
$this->title = 'SOA';
$this->params['breadcrumbs'] = [['label' => 'Unidades']];
?>

<div class="card ">
    <div class="card-header">
        <button id="btnMostrarCrearUnidad" class="btn btn-primary bg-gradient-primary" >
            <div class="icon closed">
                <div class="circle">
                    <div class="horizontal"></div>
                    <div class="vertical"></div>
                </div>
                Agregar Unidad
            </div>
        </button>
    </div>
    <div id="IngresoDatos" class="card-body" >
        <div class="col d-flex justify-content-center">
            <div class="card " style="width: 80rem;" >
                <div class="card-header bg-gradient-primary">Ingreso Datos</div>
                <div class="card-body">
                    <input type="text" id="codigo" name="codigo" disabled hidden >
                    <form id="formunidadsoa" action="" method="post">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="form-group">
                                            <div class="searchArea">
                                                <button type="button" id="search" class="btn btn-primary btn-sm">Search</button>
                                                <div class="inputDiv">
                                                    <input id="search-term" class="form-control input-sm" placeholder="Buscar" autofocus>
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
                                        <div class="form-group">
                                            <label for="nombreUnidad" class="control-label">Nombre Unidad</label>
                                            <input type="text" class="form-control input-sm txt" id="nombreUnidad"
                                                   name="nombreUnidad" placeholder="nombre de unidad">
                                        </div>
                                        <div class="form-group">
                                            <label for="nombreCorto" class="control-label">Nombre corto</label>
                                            <input type="text" class="form-control input-sm txt" id="nombreCorto"
                                                   name="nombreCorto"  placeholder="nombre de unidad">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <button class='btn btn-primary bg-gradient-primary btnGuardar'><i class='fa fa-check-circle-o'>Guardar</i></button>
                    <button class='btn btn-danger btn- btnCancel'><i class='fa fa-warning'>Cancelar</i></button>
                </div>
            </div>
        </div>
    </div>
    <div id="Divtabla" class="card-body">
        <table class="table table-bordered table-striped dt-responsive tablaListaUnidades" style="width: 100%" >
            <thead>
            <th style="text-align: center; vertical-align: middle;">#</th>
            <th style="text-align: center; vertical-align: middle;">Codigo unidad</th>
            <th style="text-align: center; vertical-align: middle;">Nombre de la unidad</th>
            <th style="text-align: center; vertical-align: middle;">Nombre corto</th>
            <th style="text-align: center; vertical-align: middle;">Unidad padre</th>
            <th style="text-align: center; vertical-align: middle;">Estado</th>
            <th style="text-align: center; vertical-align: middle; width: 140px">Acciones</th>
            </thead>
        </table>
    </div>
</div>