<?php

use yii\web\JqueryAsset;

app\modules\Planificacion\assets\PlanificacionAsset::register($this);

$this->registerJsFile("@planificacionModule/js/llave-presupuestaria/dt-declaration.js", [
        'depends' => [
                JqueryAsset::class
        ]
]);

$this->registerJsFile("@planificacionModule/js/llave-presupuestaria/LlavePresupuestaria.js", [
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

$this->params['icon'] = '';

$this->params['iconColor'] = 'info';

$this->params['actions'] =
        '
            <div class="gestion-badge">
              <span class="dot"></span>
              <span class="text">Gestión Activa: <strong>2026 - 2030</strong></span>
            </div>
        ';

$this->params['breadcrumbs'][] = [
        'label' => '/ Llaves presupuestarias',
];
?>

<div class="body">
    <div class="container">
        <div class="header">
            <div class="header-left">
                <div class="logo">🔑</div>
                <div>
                    <h1>Listado de llaves presupuestarias</h1>
                    <p id="llaveSubtitleTotal" class="subtitle">0 llaves registradas</p>
                </div>
            </div>
            <div class="header-actions">
                <button type="button" id="btnReportePdf" class="btn-reporte">⬇ Exportar PDF</button>
                <button type="button" id="btnNuevoLlave" class="btn-crear">＋ Nuevo registro</button>
            </div>
        </div>

        <div class="stats">
            <div class="stat">
                <div class="stat-icon emerald">✅</div>
                <div><p id="llaveCountVigentes" class="stat-count">0</p><p class="stat-label">Vigentes</p></div>
            </div>
            <div class="stat">
                <div class="stat-icon rose">✖</div>
                <div><p id="llaveCountCaducos" class="stat-count">0</p><p class="stat-label">Caducos</p></div>
            </div>
        </div>

        <div class="toolbar">
            <select id="filtroUnidadEjecutora">
                <option value="">Todas las unidades ejecutoras</option>
            </select>
            <div class="search">
                <input id="buscarLlave" type="text" placeholder="Buscar por DA, UE, descripción, llave...">
            </div>
            <select id="filtroEstadoLlave">
                <option value="">Todos los estados</option>
                <option value="V">Vigente</option>
                <option value="C">Caduco</option>
            </select>
            <button type="button" id="refreshTable" class="btn-refresh" title="Recargar listado">
                <i class="fa fa-sync-alt fa-spin"></i>
            </button>
        </div>

        <div class="col-headers">
            <span>#</span><span>Unidad / Llave</span><span>Estado</span><span>Acciones</span>
        </div>

        <div id="dticTableLoading" class="p-4">
            <div class="table-loading"></div>
            <div class="table-loading"></div>
            <div class="table-loading"></div>
        </div>

        <div class="p-0" id="dticTableContainer" style="display:none;">
            <table id="tablaListaLlavesPresupuestarias" class="w-100"></table>
            <div class="pagination" id="llavePagination">
                <p class="pagination-info">Mostrando <strong>0</strong> de <strong>0</strong> llaves</p>
                <div class="pagination-nav"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalLlavePresupuestaria" tabindex="-1" role="dialog" aria-labelledby="llaveModalTitulo" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content da-modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="llaveModalTitulo">Ingreso de datos</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formLlavePresupuestaria" action="" method="post" autocomplete="off">
                    <div class="form-group da-form-group">
                        <label for="idUnidadEjecutora" class="control-label">Unidad ejecutora</label>
                        <select class="form-control input-sm codigo_group dtic-input" id="idUnidadEjecutora" name="idUnidadEjecutora"
                                data-default="00-000"></select>
                    </div>
                    <div class="form-group da-form-group">
                        <label for="idPrograma" class="control-label">Programa</label>
                        <select class="form-control input-sm codigo_group dtic-input" id="idPrograma" name="idPrograma"
                                data-default="000"></select>
                    </div>
                    <div class="form-group da-form-group">
                        <label for="idProyecto" class="control-label">Proyecto</label>
                        <select class="form-control input-sm codigo_group dtic-input" id="idProyecto" name="idProyecto"
                                data-default="0000"></select>
                    </div>
                    <div class="form-group da-form-group">
                        <label for="idActividad" class="control-label">Actividad</label>
                        <select class="form-control input-sm codigo_group dtic-input" id="idActividad" name="idActividad"
                                data-default="000"></select>
                    </div>
                    <div class="form-group da-form-group form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="organizacional" name="organizacional">
                        <label class="form-check-label control-label" for="organizacional">¿Unidad organizacional?</label>
                    </div>
                    <div class="form-group da-form-group">
                        <label for="fechaInicio" class="control-label">Fecha de inicio</label>
                        <input type="date" class="form-control input-sm dtic-input" id="fechaInicio" name="fechaInicio"
                               pattern="\d{4}-\d{2}-\d{2}" placeholder="yyyy-mm-dd">
                    </div>
                    <div class="form-group da-form-group">
                        <label for="llave" class="control-label">Llave presupuestaria</label>
                        <input type="text" class="form-control input-sm dtic-input text-center" id="llave" name="llave"
                               value="00-000-000-0000-000" readonly>
                    </div>
                </form>
            </div>
            <div class="modal-footer da-modal-footer">
                <button type="button" id="btnCancelar" class="btn btn-modal btn-modal-cancel">Cancelar</button>
                <button type="button" id="btnGuardarContinuar" class="btn btn-modal btn-modal-continue">Guardar y continuar</button>
                <button type="button" id="btnGuardar" class="btn btn-modal btn-modal-save">Guardar</button>
            </div>
        </div>
    </div>
</div>
