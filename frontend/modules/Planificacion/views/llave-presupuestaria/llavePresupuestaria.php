<?php

use yii\web\JqueryAsset;

app\modules\Planificacion\assets\PlanificacionAsset::register($this);

$this->registerJsFile("@planificacionModule/js/llave-presupuestaria/LlavePresupuestaria.js", [
    'depends' => [
        JqueryAsset::class,
    ],
]);

$this->registerJsFile("@planificacionModule/js/llave-presupuestaria/dt-declaration.js",[
    'depends' => [
        JqueryAsset::class
    ]
]);

$this->registerJsFile("@planificacionModule/js/llave-presupuestaria/s2-declaration.js",[
    'depends' => [
        JqueryAsset::class
    ]
]);

$this->title = 'Planificación';
$this->params['breadcrumbs'] = [['label' => '/Llave Presupuestaria']];
?>
<div class="btnFinalizar"></div>
<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col-6">
                <button id="btnMostrarCrear" name="btnMostrarCrear" class="btn btn-primary bg-gradient-primary">
                    <span class="icon">
                        <span class="circle">
                            <span class="horizontal"></span>
                            <span class="vertical"></span>
                        </span>
                        Agregar Llave
                    </span>
                </button>
            </div>
        </div>
    </div>
    <div id="divDatos" class="card-body" style="display: none">
        <div class="col d-flex justify-content-center">
            <div class="card" style="width: 60rem;">
                <div class="card-header bg-gradient-primary">Ingreso Datos</div>
                <div class="card-body">
                    <form id="formLlavePresupuestaria" action="" method="post">

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="unidad">Unidad</label>
                                <select class="form-control" id="unidad" name="unidad">
                                </select>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="programa">Programa</label>
                                <select class="form-control" id="programa" name="programa">
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="proyecto">Proyecto</label>
                                <select class="form-control" id="proyecto" name="codigoProyecto">
                                </select>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="actividad">Actividad</label>
                                <select class="form-control" id="actividad" name="codigoActividad">
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <textarea class="form-control" rows="3" id="descripcion" name="descripcion" placeholder="Descripción"></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="techoPresupuestario">Techo Presupuestario</label>
                                <input type="number" step="0.01" min="0" class="form-control" id="techoPresupuestario" name="techoPresupuestario" placeholder="0.00">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="fechaInicio">Fecha Inicio</label>
                                <input type="date" class="form-control" id="fechaInicio" name="fechaInicio">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <button id="btnGuardar" name="btnGuardar" class='btn btn-primary bg-gradient-primary'><i class='fa fa-check-circle'></i> <span class='btn_text'> Guardar </span> </button>
                    <button id="btnCancelar" name="btnCancelar" class='btn btn-danger'><span class='fa fa-times-circle'></span> Cancelar </button>
                </div>
            </div>
        </div>
    </div>
    <div id="divTabla" class="card-body overflow-auto">
        <table id="tablaListaLlavesPresupuestarias" class="table table-bordered table-striped dt-responsive" style="width: 100%;">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Llave</th>
                    <th>Descripción</th>
                    <th>Techo</th>
                    <th>Período</th>
                    <th>Período</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<style>
    .btnFinalizar {
        width: 60px;
        height: 30px;
    }
</style>
