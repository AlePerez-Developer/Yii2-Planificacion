<?php

use app\modules\Planificacion\assets\PlanificacionAsset;
use yii\web\JqueryAsset;

PlanificacionAsset::register($this);
$this->registerCssFile('@planificacionModule/css/catalogo/style.css', ['depends' => [PlanificacionAsset::class]]);
$this->registerJsFile('@planificacionModule/js/catalogo/dt-helpers.js', ['depends' => [JqueryAsset::class]]);
$this->registerJsFile('@planificacionModule/js/cronograma-poa/dt-declaration.js', ['depends' => [JqueryAsset::class]]);
$this->registerJsFile('@planificacionModule/js/cronograma-poa/index.js', ['depends' => [JqueryAsset::class]]);

$this->title = 'Planificación Institucional';
$this->params['subtitle'] = 'Cronograma POA';
$this->params['icon'] = 'fas fa-calendar-alt';
$this->params['iconColor'] = 'info';
$this->params['actions'] = '
    <button id="btnMostrarCrear" class="btn-crear closed">
        <span class="circle">
            <span class="horizontal"></span>
            <span class="vertical"></span>
        </span>
        <span class="btn-text">Nuevo cronograma</span>
    </button>';
$this->params['breadcrumbs'][] = ['label' => '/ Cronograma POA'];
?>

<div class="card">
    <div id="divDatos" class="card-body" style="display:none">
        <div class="col d-flex justify-content-center">
            <div class="card-dtic-form" style="width: 80rem;">
                <div class="card-header card-dtic-form-header">Ingreso Datos</div>
                <div class="card-body card-dtic-form-body">
                    <form id="formCatalogo" autocomplete="off">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fechaInicio">Fecha inicio</label>
                                    <input id="fechaInicio" name="fechaInicio" type="datetime-local"
                                           class="form-control dtic-input">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fechaFin">Fecha fin</label>
                                    <input id="fechaFin" name="fechaFin" type="datetime-local"
                                           class="form-control dtic-input">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-footer card-dtic-form-footer">
                    <button id="btnGuardar" type="button" class="btn-guardar">
                        <i class="fa fa-check-circle"></i><span class="btn_text">Guardar</span>
                    </button>
                    <button id="btnCancelar" type="button" class="btn-cancel">
                        <i class="fa fa-times-circle"></i> Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div id="divTabla" class="card-body">
        <div class="card-dtic-style">
            <div class="card-dtic-style-header">
                <div class="card-dtic-style-title">Cronogramas POA</div>
            </div>
            <div id="dticTableLoading" class="p-4">
                <div class="table-loading"></div>
                <div class="table-loading"></div>
                <div class="table-loading"></div>
            </div>
            <div id="dticTableContainer" class="p-2" style="display:none">
                <table id="tablaListaCronogramas" class="table w-100 dtic-table"></table>
            </div>
        </div>
    </div>
</div>
