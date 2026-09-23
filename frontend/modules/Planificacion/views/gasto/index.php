<?php

use app\modules\Planificacion\assets\PlanificacionAsset;
use yii\web\JqueryAsset;

PlanificacionAsset::register($this);
$this->registerCssFile('@planificacionModule/css/catalogo/style.css', ['depends' => [PlanificacionAsset::class]]);
$this->registerJsFile('@planificacionModule/js/catalogo/dt-helpers.js', ['depends' => [JqueryAsset::class]]);
$this->registerJsFile('@planificacionModule/js/gasto/dt-declaration.js', ['depends' => [JqueryAsset::class]]);
$this->registerJsFile('@planificacionModule/js/gasto/index.js', ['depends' => [JqueryAsset::class]]);

$this->title = 'Planificación Institucional';
$this->params['subtitle'] = 'Administración de gastos';
$this->params['icon'] = 'fas fa-file-invoice-dollar';
$this->params['iconColor'] = 'info';
$this->params['actions'] = '
    <button id="btnMostrarCrear" class="btn-crear closed">
        <span class="circle">
            <span class="horizontal"></span>
            <span class="vertical"></span>
        </span>
        <span class="btn-text">Nuevo registro</span>
    </button>';
$this->params['breadcrumbs'][] = ['label' => '/ Gastos'];
?>

<div class="card">
    <div id="divDatos" class="card-body" style="display:none">
        <div class="col d-flex justify-content-center">
            <div class="card-dtic-form" style="width: 120rem;">
                <div class="card-header card-dtic-form-header">Ingreso Datos</div>
                <div class="card-body card-dtic-form-body">
                    <form id="formCatalogo" autocomplete="off">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="codigoGasto">Código</label>
                                    <input id="codigoGasto" name="codigoGasto" type="text" maxlength="10"
                                           class="form-control dtic-input">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="entidadTransferencia">Entidad de transferencia</label>
                                    <input id="entidadTransferencia" name="entidadTransferencia" type="text"
                                           maxlength="10" class="form-control dtic-input">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <textarea id="descripcion" name="descripcion" rows="4" maxlength="500"
                                      class="form-control dtic-input"></textarea>
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
                <div class="card-dtic-style-title">Gastos</div>
            </div>
            <div id="dticTableLoading" class="p-4">
                <div class="table-loading"></div>
                <div class="table-loading"></div>
                <div class="table-loading"></div>
            </div>
            <div id="dticTableContainer" class="p-2" style="display:none">
                <table id="tablaListaGastos" class="table w-100 dtic-table"></table>
            </div>
        </div>
    </div>
</div>
