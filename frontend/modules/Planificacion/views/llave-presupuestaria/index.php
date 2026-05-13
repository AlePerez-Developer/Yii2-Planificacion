<?php

use yii\web\JqueryAsset;

app\modules\Planificacion\assets\PlanificacionAsset::register($this);

$this->registerJsFile("@planificacionModule/js/llave-presupuestaria/LlavePresupuestaria.js", [
    'depends' => [
        JqueryAsset::class,
    ],
]);

$this->registerJsFile("@planificacionModule/js/llave-presupuestaria/dt-declaration.js", [
    'depends' => [
        JqueryAsset::class
    ]
]);

$this->registerJsFile("@planificacionModule/js/llave-presupuestaria/s2-declaration.js", [
    'depends' => [
        JqueryAsset::class
    ]
]);

$this->title = 'Planificación Institucional';

$this->params['subtitle'] = 'Administración de llaves presupuestarias';

$this->params['icon'] = 'fas fa-clipboard-list';

$this->params['iconColor'] = 'info';

$this->params['actions'] =
    '<button id="btnMostrarCrear" class="btn btn-primary bg-gradient-primary">
        <span class="icon closed">
            <span class="circle" style="margin-right: 4px">
                <span class="horizontal"></span>
                <span class="vertical"></span>
            </span>
             Nuevo registro
        </span>
     </button>

     <a href="" id="btnReportePdf" class="btn btn-outline-danger btn-sm">
        <i class="fas fa-file-pdf"></i> Exportar
     </a>';


$this->params['breadcrumbs'][] = [
    'label' => '/ Llaves presupuestarias'
];
?>

<div class="card ">
    <div id="divDatos" class="card-body" style="display: none">
        <div class="col d-flex justify-content-center">
            <div class="card " style="width: 100rem;">
                <div class="card-header bg-gradient-primary">Ingreso Datos</div>
                <div class="card-body">
                    <form id="formLlavePresupuestaria" action="" method="post">

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="da">Direccion Administrativa</label>
                                <select class="form-control codigo_group" id="da" name="da" data-default = '00'>
                                </select>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="ue">Unidad Ejecutora</label>
                                <select class="form-control codigo_group" id="ue" name="ue" data-default = '000'>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label for="programa">Programa</label>
                                <select class="form-control codigo_group" id="programa" name="programa" data-default = '000'>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="proyecto">Proyecto</label>
                                <select class="form-control codigo_group" id="proyecto" name="proyecto" data-default = '0000'>
                                </select>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="actividad">Actividad</label>
                                <select class="form-control codigo_group" id="actividad" name="actividad" data-default = '000'>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <textarea class="form-control" rows="3" id="descripcion" name="descripcion"
                                      placeholder="Descripción"></textarea>
                        </div>

                        <div class="form-group form-switch">
                            <input class="form-check-input" type="checkbox" id="organizacional" name="organizacional">
                            <label class="form-check-label" for="organizacional">Unidad organizacional?</label>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="fechaInicio" class="control-label">Fecha de inicio</label>
                                <input type="date" class="form-control input-sm" id="fechaInicio" name="fechaInicio"
                                       pattern="\d{4}-\d{2}-\d{2}" placeholder="yyyy-mm-dd" style="width: 400px">
                            </div>

                            <div class="form-group col-md-6 text-center">
                                <label for="llave" class="control-label d-block">Llave presupuestaria</label>
                                <input type="text" class="form-control input-sm mx-auto text-center" id="llave" name="llave"
                                       value="00-000-000-0000-000" readonly style="max-width: 400px;">
                            </div>

                        </div>

                    </form>
                </div>
                <div class="card-footer text-center">
                    <button id="btnGuardar" name="btnGuardar" class='btn btn-primary bg-gradient-primary'><i
                                class='fa fa-check-circle'></i> <span class='btn_text'> Guardar </span></button>
                    <button id="btnCancelar" name="btnCancelar" class='btn btn-danger'><span
                                class='fa fa-times-circle'></span> Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="divTabla" class="card-body">
        <div class="card-dtic-style">

            <div class="card-dtic-style-header">
                <div class="card-dtic-style-title">
                    Planes Estratégicos Institucionales
                </div>

            </div>

            <div id="dticTableLoading" class="p-4">
                <div class="table-loading"></div>
                <div class="table-loading"></div>
                <div class="table-loading"></div>
            </div>

            <div class="p-2" id="dticTableContainer" style="display:none;">
                <table id="tablaListaLlavesPresupuestarias" class="table w-100 dtic-table"></table>
            </div>

        </div>
    </div>
</div>
