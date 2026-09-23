<?php

use app\modules\Planificacion\assets\PlanificacionAsset;
use yii\web\JqueryAsset;

PlanificacionAsset::register($this);
$this->registerCssFile('@planificacionModule/css/catalogo/style.css', ['depends' => [PlanificacionAsset::class]]);
$this->registerJsFile('@planificacionModule/js/catalogo/dt-helpers.js', ['depends' => [JqueryAsset::class]]);
$this->registerJsFile('@planificacionModule/js/usuario-seguridad/dt-declaration.js', ['depends' => [JqueryAsset::class]]);
$this->registerJsFile('@planificacionModule/js/usuario-seguridad/index.js', ['depends' => [JqueryAsset::class]]);

$this->title = 'Planificación Institucional';
$this->params['subtitle'] = 'Usuarios y asignaciones';
$this->params['icon'] = 'fas fa-users';
$this->params['iconColor'] = 'info';
$this->params['actions'] = '
    <button id="btnMostrarCrear" class="btn-crear closed">
        <span class="circle"><span class="horizontal"></span><span class="vertical"></span></span>
        <span class="btn-text">Nuevo usuario</span>
    </button>';
$this->params['breadcrumbs'][] = ['label' => '/ Usuarios'];
?>

<div class="card">
    <div id="divDatos" class="card-body" style="display:none">
        <div class="col d-flex justify-content-center">
            <div class="card-dtic-form" style="width: 120rem;">
                <div class="card-header card-dtic-form-header">Datos del usuario</div>
                <div class="card-body card-dtic-form-body">
                    <form id="formUsuario" autocomplete="off">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="idPersona">Id Persona</label>
                                    <input id="idPersona" name="idPersona" class="form-control dtic-input" maxlength="15">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="codigoUsuario">Código usuario</label>
                                    <input id="codigoUsuario" name="codigoUsuario" class="form-control dtic-input" maxlength="50">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="nick">Nick</label>
                                    <input id="nick" name="nick" class="form-control dtic-input" maxlength="100">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="tokenPortal">Token portal</label>
                                    <input id="tokenPortal" name="tokenPortal" class="form-control dtic-input" maxlength="40">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Módulos</label>
                            <div id="listaModulos" class="d-flex flex-wrap"></div>
                        </div>
                        <hr>
                        <h6>Asignación unidad / gestión / estado POA</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <select id="idUnidadEjecutora" class="form-control"></select>
                            </div>
                            <div class="col-md-3">
                                <select id="idGestion" class="form-control"></select>
                            </div>
                            <div class="col-md-3">
                                <select id="idEstadoPoa" class="form-control"></select>
                            </div>
                            <div class="col-md-2">
                                <button id="btnAgregarAsignacion" type="button" class="btn btn-primary btn-block">Agregar</button>
                            </div>
                        </div>
                        <table class="table table-sm mt-2">
                            <thead>
                            <tr><th>Unidad</th><th>Gestión</th><th>Estado POA</th><th></th></tr>
                            </thead>
                            <tbody id="tablaAsignaciones"></tbody>
                        </table>
                    </form>
                </div>
                <div class="card-footer card-dtic-form-footer">
                    <button id="btnGuardar" type="button" class="btn-guardar">
                        <i class="fa fa-check-circle"></i><span class="btn_text">Guardar</span>
                    </button>
                    <button id="btnCancelar" type="button" class="btn-cancel">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
    <div id="divTabla" class="card-body">
        <div class="card-dtic-style">
            <div class="card-dtic-style-header">
                <div class="card-dtic-style-title">Usuarios</div>
            </div>
            <div id="dticTableLoading" class="p-4">
                <div class="table-loading"></div>
            </div>
            <div id="dticTableContainer" class="p-2" style="display:none">
                <table id="tablaListaUsuarios" class="table w-100 dtic-table"></table>
            </div>
        </div>
    </div>
</div>
