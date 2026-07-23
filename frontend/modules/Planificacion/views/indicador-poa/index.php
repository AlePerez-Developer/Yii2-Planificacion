<?php

use app\modules\Planificacion\assets\PlanificacionAsset;
use yii\web\JqueryAsset;

PlanificacionAsset::register($this);
$this->registerJsFile('@planificacionModule/js/indicador-poa/s2-declaration.js', ['depends' => [JqueryAsset::class]]);
$this->registerJsFile('@planificacionModule/js/indicador-poa/dt-declaration.js', ['depends' => [JqueryAsset::class]]);
$this->registerJsFile('@planificacionModule/js/indicador-poa/index.js', ['depends' => [JqueryAsset::class]]);

$this->title = 'Planificación Institucional';
$this->params['subtitle'] = 'Indicadores POA';
$this->params['icon'] = 'fas fa-chart-line';
$this->params['iconColor'] = 'primary';
$this->params['breadcrumbs'][] = ['label' => '/ Indicadores POA'];
?>

<div class="card">
    <div class="card-header">
        <button id="btnMostrarCrear" class="btn btn-primary bg-gradient-primary" type="button">
            <span class="icon closed"><span class="circle"><span class="horizontal"></span><span class="vertical"></span></span>
                Agregar indicador POA
            </span>
        </button>
    </div>

    <div id="divDatos" class="card-body" style="display:none;">
        <div class="card-dtic-style">
            <div class="card-dtic-style-header"><div class="card-dtic-style-title">Datos de indicador POA</div></div>
            <div class="p-3">

<form id="formIndicadorPoa" autocomplete="off">
    <div class="form-group">
        <label for="idObjEspecifico">Objetivo específico</label>
        <select id="idObjEspecifico" name="idObjEspecifico" class="form-control dtic-input" style="width:100%;"></select>
    </div>
    <div class="row">
        <div class="col-md-2"><div class="form-group">
            <label for="codigo">Código</label>
            <input id="codigo" name="codigo" type="number" min="1" class="form-control dtic-input">
        </div></div>
        <div class="col-md-2"><div class="form-group">
            <label for="meta">Meta</label>
            <input id="meta" name="meta" type="number" min="0" class="form-control dtic-input">
        </div></div>
        <div class="col-md-3"><div class="form-group">
            <label for="tipo">Tipo</label>
            <select id="tipo" name="tipo" class="form-control dtic-input"></select>
        </div></div>
        <div class="col-md-2"><div class="form-group">
            <label for="categoria">Categoría</label>
            <select id="categoria" name="categoria" class="form-control dtic-input"></select>
        </div></div>
        <div class="col-md-3"><div class="form-group">
            <label for="unidad">Unidad</label>
            <select id="unidad" name="unidad" class="form-control dtic-input"></select>
        </div></div>
    </div>
    <div class="form-group">
        <label for="descripcion">Descripción del indicador</label>
        <textarea id="descripcion" name="descripcion" class="form-control dtic-input" rows="4" maxlength="500"></textarea>
    </div>
</form>

            </div>
            <div class="card-footer text-center">
                <button id="btnGuardar" type="button" class="btn btn-primary bg-gradient-primary">
                    <i class="fa fa-check-circle"></i> <span class="btn_text">Guardar</span>
                </button>
                <button id="btnCancelar" type="button" class="btn btn-danger">
                    <i class="fa fa-times-circle"></i> Cancelar
                </button>
            </div>
        </div>
    </div>

    <div id="divTabla" class="card-body">
        <div class="card-dtic-style">
            <div class="card-dtic-style-header"><div class="card-dtic-style-title">Listado de indicadores POA</div></div>
            <div id="dticTableLoading" class="p-4">
                <div class="table-loading"></div><div class="table-loading"></div><div class="table-loading"></div>
            </div>
            <div id="dticTableContainer" class="p-2" style="display:none;">
                <table id="tablaListaIndicadoresPoa" class="table w-100 dtic-table"></table>
            </div>
        </div>
    </div>
</div>
