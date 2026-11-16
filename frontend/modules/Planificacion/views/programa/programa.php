<?php

use yii\web\JqueryAsset;

app\modules\Planificacion\assets\PlanificacionAsset::register($this);

$this->registerJsFile("@planificacionModule/js/programa/dt-declaration.js", [
        'depends' => [
                JqueryAsset::class
        ]
]);

$this->registerJsFile("@planificacionModule/js/programa/Programa.js", [
        'depends' => [
                JqueryAsset::class
        ]
]);

$this->title = 'Planificación Institucional';

$this->params['subtitle'] = 'Administración de programas';

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
        'label' => '/ Programas',
];
?>

<div class="body">
    <div class="container">
        <div class="header">
            <div class="header-left">
                <div class="logo">📋</div>
                <div>
                    <h1>Listado de programas</h1>
                    <p id="programaSubtitleTotal" class="subtitle">0 programas registrados</p>
                </div>
            </div>
            <div class="header-actions">
                <button type="button" id="btnReportePdf" class="btn-reporte">⬇ Exportar PDF</button>
                <button type="button" id="btnNuevoPrograma" class="btn-crear">＋ Nuevo registro</button>
            </div>
        </div>

        <div class="stats">
            <div class="stat">
                <div class="stat-icon emerald">✅</div>
                <div><p id="programaCountVigentes" class="stat-count">0</p><p class="stat-label">Vigentes</p></div>
            </div>
            <div class="stat">
                <div class="stat-icon rose">✖</div>
                <div><p id="programaCountCaducos" class="stat-count">0</p><p class="stat-label">Caducos</p></div>
            </div>
        </div>

        <div class="toolbar">
            <div class="search">
                <input id="buscarPrograma" type="text" placeholder="Buscar por código o descripción...">
            </div>
            <select id="filtroEstadoPrograma">
                <option value="">Todos los estados</option>
                <option value="V">Vigente</option>
                <option value="C">Caduco</option>
            </select>
            <button type="button" id="refreshTable" class="btn-refresh" title="Recargar listado">
                <i class="fa fa-sync-alt fa-spin"></i>
            </button>
        </div>

        <div class="col-headers">
            <span>#</span><span>Programa</span><span>Estado</span><span>Acciones</span>
        </div>

        <div id="dticTableLoading" class="p-4">
            <div class="table-loading"></div>
            <div class="table-loading"></div>
            <div class="table-loading"></div>
        </div>

        <div class="p-0" id="dticTableContainer" style="display:none;">
            <table id="tablaListaProgramas" class="w-100"></table>
            <div class="pagination" id="programaPagination">
                <p class="pagination-info">Mostrando <strong>0</strong> de <strong>0</strong> programas</p>
                <div class="pagination-nav"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPrograma" tabindex="-1" role="dialog" aria-labelledby="programaModalTitulo" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content da-modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="programaModalTitulo">Ingreso de datos</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formPrograma" action="" method="post" autocomplete="off">
                    <div class="form-group da-form-group">
                        <label for="codigo" class="control-label">Código de programa</label>
                        <input type="text" class="form-control input-sm dtic-input num" id="codigo" name="codigo"
                               pattern="\d{3}" maxlength="3" inputmode="numeric" placeholder="Ej: 023">
                    </div>
                    <div class="form-group da-form-group">
                        <label for="descripcion" class="control-label">Descripción</label>
                        <textarea class="form-control input-sm dtic-input txt" id="descripcion" name="descripcion" rows="2"
                                  placeholder="Descripción del programa"></textarea>
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
