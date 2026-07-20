<?php

use app\modules\Planificacion\assets\PlanificacionAsset;
use yii\web\JqueryAsset;

PlanificacionAsset::register($this);

$this->registerJsFile('@planificacionModule/js/obj-institucional/s2-declaration.js', [
    'depends' => [JqueryAsset::class],
]);
$this->registerJsFile('@planificacionModule/js/obj-institucional/dt-declaration.js', [
    'depends' => [JqueryAsset::class],
]);
$this->registerJsFile('@planificacionModule/js/obj-institucional/ObjInstitucional.js', [
    'depends' => [JqueryAsset::class],
]);

$this->title = 'Planificación Institucional';
$this->params['subtitle'] = 'Objetivos institucionales';
$this->params['icon'] = 'fas fa-bullseye';
$this->params['iconColor'] = 'primary';
$this->params['actions'] = '';
$this->params['breadcrumbs'][] = ['label' => '/ Objetivos institucionales'];
?>

<div class="card">
    <div class="card-header">
        <button id="btnMostrarCrear" class="btn btn-primary bg-gradient-primary" type="button">
            <span class="icon closed">
                <span class="circle">
                    <span class="horizontal"></span>
                    <span class="vertical"></span>
                </span>
                Agregar objetivo institucional
            </span>
        </button>
    </div>

    <div id="divDatos" class="card-body" style="display:none;">
        <div class="card-dtic-style">
            <div class="card-dtic-style-header">
                <div class="card-dtic-style-title">Datos del objetivo institucional</div>
            </div>

            <div class="p-3">
                <form id="formObjInstitucional" method="post" autocomplete="off">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="idObjEstrategico">Objetivo estratégico</label>
                                <select
                                    id="idObjEstrategico"
                                    name="idObjEstrategico"
                                    class="form-control dtic-input"
                                    style="width:100%;"
                                ></select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="codigo">Código</label>
                                <input
                                    id="codigo"
                                    name="codigo"
                                    type="text"
                                    class="form-control dtic-input"
                                    maxlength="2"
                                    inputmode="numeric"
                                    placeholder="01"
                                >
                                <small class="form-text text-muted">Formato: 01, 02, 03...</small>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="gestion">Gestión</label>
                                <input
                                    id="gestion"
                                    name="gestion"
                                    type="number"
                                    class="form-control dtic-input"
                                    min="2000"
                                    max="2100"
                                >
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="objetivo">Objetivo institucional</label>
                                <textarea
                                    id="objetivo"
                                    name="objetivo"
                                    class="form-control dtic-input"
                                    rows="4"
                                    maxlength="200"
                                    placeholder="Descripción del objetivo institucional"
                                ></textarea>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="producto">Resultado / producto esperado</label>
                                <textarea
                                    id="producto"
                                    name="producto"
                                    class="form-control dtic-input"
                                    rows="4"
                                    maxlength="200"
                                    placeholder="Resultado o producto esperado"
                                ></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card-footer text-center">
                <button id="btnGuardar" type="button" class="btn btn-primary bg-gradient-primary">
                    <i class="fa fa-check-circle"></i>
                    <span class="btn_text">Guardar</span>
                </button>
                <button id="btnCancelar" type="button" class="btn btn-danger">
                    <i class="fa fa-times-circle"></i> Cancelar
                </button>
            </div>
        </div>
    </div>

    <div id="divTabla" class="card-body">
        <div class="card-dtic-style">
            <div class="card-dtic-style-header">
                <div class="card-dtic-style-title">Listado de objetivos institucionales</div>
            </div>

            <div id="dticTableLoading" class="p-4">
                <div class="table-loading"></div>
                <div class="table-loading"></div>
                <div class="table-loading"></div>
            </div>

            <div id="dticTableContainer" class="p-2" style="display:none;">
                <table id="tablaListaObjInstitucionales" class="table w-100 dtic-table"></table>
            </div>
        </div>
    </div>
</div>
