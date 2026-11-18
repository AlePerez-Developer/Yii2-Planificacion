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
$this->registerCssFile('@planificacionModule/css/obj-institucional/style.css', [
    'depends' => [PlanificacionAsset::class],
]);

$this->title = 'Planificación Institucional';

$this->params['subtitle'] = 'Administración de objetivos institucionales';

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
    'label' => '/ Obj. institucionales',
];
?>

<style>
    .body .container #tablaListaObjInstitucionales.dataTable {
        width: 100% !important;
        border: none !important;
        border-collapse: separate;
        border-spacing: 0;
    }
    .body .container #tablaListaObjInstitucionales.dataTable tbody tr,
    .body .container #tablaListaObjInstitucionales.dataTable tbody td {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
    }
    .body .container #tablaListaObjInstitucionales.dataTable tbody td {
        padding: 0 0 16px 0 !important;
    }
    .body .container .oi-card .fa,
    .body .container .oi-card .fas,
    .body .container .oi-card .far,
    .body .container .toolbar .fa {
        font-family: "Font Awesome 5 Free";
    }
    .body .container .oi-card .far { font-weight: 400; }
    .body .container .oi-card .fa,
    .body .container .oi-card .fas,
    .body .container .toolbar .fa { font-weight: 900; }
    .body .container #tablaListaObjInstitucionales .dt-empty {
        background: #fff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 16px;
        padding: 32px 16px !important;
        text-align: center;
        color: #64748b;
        font-weight: 600;
    }
    .body .container #dticTableContainer .dt-container { margin: 0; }

    .body .container .oi-card {
        display: flex;
        flex-direction: column;
        gap: 20px;
        background: #fff;
        border: 1px solid rgba(226, 232, 240, .9);
        border-radius: 16px;
        padding: 20px 24px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .body .container .oi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, .05), 0 8px 10px -6px rgba(0, 0, 0, .04);
    }
    .body .container .oi-card-body {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .body .container .oi-head {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }
    .body .container .oi-num {
        width: 28px;
        height: 28px;
        margin-top: 2px;
        border-radius: 999px;
        background: #0d9488;
        color: #fff;
        font-size: 12px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 1px 2px rgba(13, 148, 136, .35);
    }
    .body .container .oi-head-text { min-width: 0; display: flex; flex-direction: column; gap: 4px; }
    .body .container .oi-badges {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }
    .body .container .oi-badge-code {
        display: inline-flex;
        align-items: center;
        background: #ccfbf1;
        color: #0f766e;
        border: 1px solid #99f6e4;
        border-radius: 6px;
        padding: 2px 10px;
        font-size: 12px;
        font-weight: 700;
    }
    .body .container .oi-title {
        margin: 0;
        font-size: 16px;
        line-height: 1.35;
        font-weight: 700;
        color: #0f172a;
    }
    .body .container .oi-meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px 24px;
        background: #f8fafc;
        border: 1px solid #f1f5f9;
        border-radius: 12px;
        padding: 12px;
        font-size: 12px;
        color: #475569;
    }
    .body .container .oi-meta-item {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .body .container .oi-meta-item i { color: #64748b; }
    .body .container .oi-meta-label { font-weight: 700; color: #334155; }
    .body .container .oi-meta-value { font-weight: 600; color: #0f172a; }
    .body .container .oi-actions {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 8px;
        flex-shrink: 0;
        padding-top: 16px;
        border-top: 1px solid #f1f5f9;
    }
    .body .container .oi-card .actions {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 4px;
    }
    .body .container .oi-card .btn-action-new {
        padding: 8px 12px;
        border-radius: 8px;
        background: transparent;
        border: none;
        font-size: 13px;
        font-weight: 500;
        color: #94a3b8;
        cursor: pointer;
        transition: all .2s;
    }
    .body .container .oi-card .btn-action-new.edit:hover {
        background: #f0f9ff;
        color: #0284c7;
        transform: scale(1.05);
    }
    .body .container .oi-card .btn-action-new.delete:hover {
        background: #fff1f2;
        color: #e11d48;
        transform: scale(1.05);
    }
    .body .container .oi-card .estado {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 600;
        border-radius: 999px;
        padding: 6px 12px;
        border: 1px solid;
        cursor: pointer;
    }
    .body .container .oi-card .estado .dot,
    .body .container .oi-card .estado-option .dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .body .container .oi-card .estado.vigente { background: #ecfdf5; color: #047857; border-color: #a7f3d0; }
    .body .container .oi-card .estado.vigente .dot,
    .body .container .oi-card .estado-option.vigente .dot { background: #10b981; }
    .body .container .oi-card .estado.cancelada { background: #fff1f2; color: #e11d48; border-color: #fecdd3; }
    .body .container .oi-card .estado.cancelada .dot,
    .body .container .oi-card .estado-option.cancelada .dot { background: #fb7185; }
    .body .container .oi-card .estado .chevron { font-size: 10px; margin-left: 2px; opacity: .8; }
    .body .container .oi-card .estado-wrap { position: relative; display: inline-block; }
    .body .container .oi-card .estado-menu {
        display: none;
        position: absolute;
        top: calc(100% + 6px);
        right: 0;
        left: auto;
        min-width: 160px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 8px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, .12);
        z-index: 30;
    }
    .body .container .oi-card .estado-wrap.open .estado-menu {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .body .container .oi-card .estado-option {
        display: flex;
        align-items: center;
        gap: 8px;
        width: 100%;
        border: none;
        background: transparent;
        padding: 8px 10px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        color: #334155;
        cursor: pointer;
        text-align: left;
    }
    .body .container .oi-card .estado-option:hover { background: #f8fafc; }
    .body .container .oi-card .estado-option.selected { background: #f1f5f9; }
    .body .container .oi-card .estado-option .check { margin-left: auto; color: #0369a1; font-size: 13px; }

    @media (min-width: 992px) {
        .body .container .oi-card {
            flex-direction: row;
            align-items: flex-start;
            justify-content: space-between;
        }
        .body .container .oi-actions {
            padding-top: 0;
            border-top: none;
        }
        .body .container .oi-title { font-size: 18px; }
    }
</style>

<div class="body">
    <div class="container">
        <div class="header">
            <div class="header-left">
                <div class="logo">📋</div>
                <div>
                    <h1>Objetivos Institucionales</h1>
                    <p id="oiSubtitleTotal" class="subtitle">0 objetivos institucionales registrados</p>
                </div>
            </div>
            <div class="header-actions">
                <button type="button" id="btnReportePdf" class="btn-reporte">⬇ Exportar PDF</button>
                <button type="button" id="btnNuevoObjInstitucional" class="btn-crear">＋ Nuevo registro</button>
            </div>
        </div>

        <div class="stats">
            <div class="stat">
                <div class="stat-icon emerald">✅</div>
                <div><p id="oiCountVigentes" class="stat-count">0</p><p class="stat-label">Vigentes</p></div>
            </div>
            <div class="stat">
                <div class="stat-icon rose">✖</div>
                <div><p id="oiCountCaducos" class="stat-count">0</p><p class="stat-label">Caducos</p></div>
            </div>
        </div>

        <div class="toolbar">
            <div class="search">
                <input id="buscarOi" type="text" placeholder="Buscar por código, objetivo o objetivo estratégico...">
            </div>
            <select id="filtroEstadoOi">
                <option value="">Todos los estados</option>
                <option value="V">Vigente</option>
                <option value="C">Caduco</option>
            </select>
            <button type="button" id="refreshTable" class="btn-refresh" title="Recargar listado">
                <i class="fa fa-sync-alt fa-spin"></i>
            </button>
        </div>

        <div id="dticTableLoading" class="p-4">
            <div class="table-loading"></div>
            <div class="table-loading"></div>
            <div class="table-loading"></div>
        </div>
        <div class="p-0" id="dticTableContainer" style="display:none;">
            <table id="tablaListaObjInstitucionales" class="w-100"></table>
            <div class="pagination" id="oiPagination">
                <p class="pagination-info">Mostrando <strong>0</strong> de <strong>0</strong> objetivos institucionales</p>
                <div class="pagination-nav"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalObjInstitucional" tabindex="-1" role="dialog" aria-labelledby="oiModalTitulo" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content da-modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="oiModalTitulo">Ingreso de datos</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formObjInstitucional" action="" method="post" autocomplete="off">
                    <div class="form-group da-form-group">
                        <label for="idObjEstrategico" class="control-label">Objetivo estratégico</label>
                        <select id="idObjEstrategico" name="idObjEstrategico" class="form-control codigo_group dtic-input"></select>
                    </div>
                    <div class="form-group da-form-group oi-codigo">
                        <label for="codigo" class="control-label">Código</label>
                        <input type="text" class="form-control input-sm num codigo_group dtic-input" id="codigo" name="codigo"
                               maxlength="2" inputmode="numeric" placeholder="01">
                        <small class="form-text text-muted">Formato: 01, 02, 03...</small>
                    </div>
                    <div class="oi-form-grid">
                        <div class="form-group da-form-group">
                            <label for="objetivo" class="control-label">Objetivo institucional</label>
                            <textarea class="form-control input-sm dtic-input txt" id="objetivo" name="objetivo" rows="3"
                                      maxlength="500" placeholder="Descripción del objetivo institucional"></textarea>
                        </div>
                        <div class="form-group da-form-group">
                            <label for="producto" class="control-label">Resultado / producto esperado</label>
                            <textarea class="form-control input-sm dtic-input txt" id="producto" name="producto" rows="3"
                                      maxlength="500" placeholder="Resultado o producto esperado"></textarea>
                        </div>
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
