<?php

use yii\helpers\Url;
use yii\web\JqueryAsset;

app\modules\Planificacion\assets\PlanificacionAsset::register($this);

$this->registerJsFile("@planificacionModule/js/obj-estrategico/s2-declaration.js", [
        'depends' => [
                JqueryAsset::class
        ]
]);

$this->registerJsFile("@planificacionModule/js/obj-estrategico/dt-declaration.js", [
        'depends' => [
                JqueryAsset::class
        ]
]);

$this->registerJsFile("@planificacionModule/js/obj-estrategico/ObjEstrategico.js", [
        'depends' => [
                JqueryAsset::class
        ]
]);

$this->registerJs("
    urlProgramar = '" . Url::to(['indicador-estrategico-programacion-trimestral/index']) . "';
");

$this->title = 'Planificación Institucional';

$this->params['subtitle'] = 'Administración de objetivos estratégicos institucionales';

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
        'label' => '/ Obj. estratégicos institucionales',
];
?>

<style>
    .body .container #tablaListaObjEstrategicos.dataTable {
        width: 100% !important;
        border: none !important;
        border-collapse: separate;
        border-spacing: 0;
    }
    .body .container #tablaListaObjEstrategicos.dataTable tbody tr,
    .body .container #tablaListaObjEstrategicos.dataTable tbody td {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
    }
    .body .container #tablaListaObjEstrategicos.dataTable tbody td {
        padding: 0 0 16px 0 !important;
    }
    .body .container .oe-card .fa,
    .body .container .oe-card .fas,
    .body .container .oe-card .far,
    .body .container .toolbar .fa {
        font-family: "Font Awesome 5 Free";
    }
    .body .container .oe-card .far { font-weight: 400; }
    .body .container .oe-card .fa,
    .body .container .oe-card .fas,
    .body .container .toolbar .fa { font-weight: 900; }
    .body .container #tablaListaObjEstrategicos .dt-empty {
        background: #fff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 16px;
        padding: 32px 16px !important;
        text-align: center;
        color: #64748b;
        font-weight: 600;
    }
    .body .container #dticTableContainer .dt-container { margin: 0; }
    .body .container .oe-page-size {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: #475569;
        font-weight: 600;
    }

    .body .container .oe-card {
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
    .body .container .oe-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, .05), 0 8px 10px -6px rgba(0, 0, 0, .04);
    }
    .body .container .oe-card-body {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .body .container .oe-head {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }
    .body .container .oe-num {
        width: 28px;
        height: 28px;
        margin-top: 2px;
        border-radius: 999px;
        background: #2563eb;
        color: #fff;
        font-size: 12px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 1px 2px rgba(37, 99, 235, .35);
    }
    .body .container .oe-head-text { min-width: 0; display: flex; flex-direction: column; gap: 4px; }
    .body .container .oe-badges {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }
    .body .container .oe-badge-code {
        display: inline-flex;
        align-items: center;
        background: #cffafe;
        color: #155e75;
        border: 1px solid #a5f3fc;
        border-radius: 6px;
        padding: 2px 10px;
        font-size: 12px;
        font-weight: 700;
    }
    .body .container .oe-title {
        margin: 0;
        font-size: 16px;
        line-height: 1.35;
        font-weight: 700;
        color: #0f172a;
    }
    .body .container .oe-meta {
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
    .body .container .oe-meta-item,
    .body .container .oe-meta-hint,
    .body .container .oe-block-label {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .body .container .oe-meta-item i { color: #64748b; }
    .body .container .oe-meta-label { font-weight: 700; color: #334155; }
    .body .container .oe-meta-value { font-weight: 600; color: #0f172a; }
    .body .container .oe-meta-hint { color: #94a3b8; font-size: 11px; }
    .body .container .oe-meta-hint em { font-style: italic; }
    .body .container .oe-blocks {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
    .body .container .oe-block {
        background: #fff;
        border: 1px solid rgba(226, 232, 240, .7);
        border-radius: 12px;
        padding: 12px;
        transition: border-color .2s ease;
    }
    .body .container .oe-block:hover { border-color: #93c5fd; }
    .body .container .oe-block-label {
        margin-bottom: 4px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #64748b;
    }
    .body .container .oe-block-label .fa-map-marker-alt { color: #2563eb; }
    .body .container .oe-block-label .fa-landmark { color: #4f46e5; }
    .body .container .oe-block p {
        margin: 0;
        font-size: 12px;
        font-weight: 600;
        color: #334155;
        line-height: 1.4;
    }
    .body .container .oe-chip {
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        font-size: 11px;
        font-weight: 700;
        border-radius: 4px;
        padding: 0 6px;
        letter-spacing: 0;
        text-transform: none;
    }
    .body .container .oe-chip-area { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
    .body .container .oe-chip-politica { background: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe; }
    .body .container .oe-actions {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 8px;
        flex-shrink: 0;
        padding-top: 16px;
        border-top: 1px solid #f1f5f9;
    }
    .body .container .oe-card .actions {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 4px;
    }
    .body .container .oe-card .btn-action-new {
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
    .body .container .oe-card .btn-action-new.programar:hover,
    .body .container .oe-card .btn-action-new.edit:hover {
        background: #f0f9ff;
        color: #0284c7;
        transform: scale(1.05);
    }
    .body .container .oe-card .btn-action-new.delete:hover {
        background: #fff1f2;
        color: #e11d48;
        transform: scale(1.05);
    }
    .body .container .oe-card .estado {
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
    .body .container .oe-card .estado .dot,
    .body .container .oe-card .estado-option .dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .body .container .oe-card .estado.vigente { background: #ecfdf5; color: #047857; border-color: #a7f3d0; }
    .body .container .oe-card .estado.vigente .dot,
    .body .container .oe-card .estado-option.vigente .dot { background: #10b981; }
    .body .container .oe-card .estado.cancelada { background: #fff1f2; color: #e11d48; border-color: #fecdd3; }
    .body .container .oe-card .estado.cancelada .dot,
    .body .container .oe-card .estado-option.cancelada .dot { background: #fb7185; }
    .body .container .oe-card .estado .chevron { font-size: 10px; margin-left: 2px; opacity: .8; }
    .body .container .oe-card .estado-wrap { position: relative; display: inline-block; }
    .body .container .oe-card .estado-menu {
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
    .body .container .oe-card .estado-wrap.open .estado-menu {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .body .container .oe-card .estado-option {
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
    .body .container .oe-card .estado-option:hover { background: #f8fafc; }
    .body .container .oe-card .estado-option.selected { background: #f1f5f9; }
    .body .container .oe-card .estado-option .check { margin-left: auto; color: #0369a1; font-size: 13px; }

    @media (min-width: 992px) {
        .body .container .oe-card {
            flex-direction: row;
            align-items: flex-start;
            justify-content: space-between;
        }
        .body .container .oe-actions {
            padding-top: 0;
            border-top: none;
        }
        .body .container .oe-title { font-size: 18px; }
    }
    @media (max-width: 768px) {
        .body .container .oe-blocks { grid-template-columns: 1fr; }
        .body .container .oe-page-size { width: 100%; }
    }
</style>

<div class="body">
    <div class="container">
        <div class="header">
            <div class="header-left">
                <div class="logo">🎯</div>
                <div>
                    <h1>Objetivos Estratégicos Institucionales</h1>
                    <p id="oeSubtitleTotal" class="subtitle">0 objetivos estratégicos registrados</p>
                </div>
            </div>
            <div class="header-actions">
                <button type="button" id="btnReportePdf" class="btn-reporte">⬇ Exportar PDF</button>
                <button type="button" id="btnNuevoObjEstrategico" class="btn-crear">＋ Nuevo registro</button>
            </div>
        </div>

        <div class="stats">
            <div class="stat">
                <div class="stat-icon emerald">✅</div>
                <div><p id="oeCountVigentes" class="stat-count">0</p><p class="stat-label">Vigentes</p></div>
            </div>
            <div class="stat">
                <div class="stat-icon rose">✖</div>
                <div><p id="oeCountCaducos" class="stat-count">0</p><p class="stat-label">Caducos</p></div>
            </div>
        </div>

        <div class="toolbar">
            <div class="search">
                <input id="buscarOe" type="text" placeholder="Buscar por código, objetivo, área o política...">
            </div>
            <select id="filtroEstadoOe">
                <option value="">Todos los estados</option>
                <option value="V">Vigente</option>
                <option value="C">Caduco</option>
            </select>
            <div class="oe-page-size">
                <span>Mostrar</span>
                <select id="oePageLength" aria-label="Registros por página">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
                <span>registros</span>
            </div>
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
            <table id="tablaListaObjEstrategicos" class="w-100"></table>
            <div class="pagination" id="oePagination">
                <p class="pagination-info">Mostrando <strong>0</strong> de <strong>0</strong> objetivos estratégicos</p>
                <div class="pagination-nav"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalObjEstrategico" tabindex="-1" role="dialog" aria-labelledby="oeModalTitulo" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content da-modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="oeModalTitulo">Ingreso de datos</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formObjEstrategico" action="" method="post" autocomplete="off">
                    <div class="form-group da-form-group">
                        <label for="areasEstrategicas" class="control-label">Área estratégica</label>
                        <select id="areasEstrategicas" name="areasEstrategicas" class="form-control codigo_group dtic-input"></select>
                    </div>
                    <div class="form-group da-form-group">
                        <label for="politicasEstrategicas" class="control-label">Política estratégica</label>
                        <select id="politicasEstrategicas" name="politicasEstrategicas" class="form-control codigo_group dtic-input"></select>
                    </div>
                    <div class="form-group da-form-group oe-codigo">
                        <label for="codigo" class="control-label">Código OE</label>
                        <input type="text" class="form-control input-sm num codigo_group dtic-input" id="codigo" name="codigo"
                               maxlength="1" inputmode="numeric" placeholder="1–9">
                    </div>
                    <div class="oe-form-grid">
                        <div class="form-group da-form-group">
                            <label for="objetivo" class="control-label">Descripción del objetivo estratégico</label>
                            <textarea class="form-control input-sm dtic-input txt" id="objetivo" name="objetivo" rows="3"
                                      placeholder="Descripción del objetivo estratégico"></textarea>
                        </div>
                        <div class="form-group da-form-group">
                            <label for="producto" class="control-label">Resultado / producto esperado</label>
                            <textarea class="form-control input-sm dtic-input txt" id="producto" name="producto" rows="3"
                                      placeholder="Resultado o producto esperado"></textarea>
                        </div>
                    </div>
                    <div class="oe-indicador">
                        <p class="oe-indicador-title">Datos del indicador del objetivo</p>
                        <div class="oe-form-grid">
                            <div class="form-group da-form-group">
                                <label for="descripcion" class="control-label">Descripción</label>
                                <textarea class="form-control input-sm dtic-input txt" id="descripcion" name="descripcion" rows="3"
                                          placeholder="Descripción del indicador"></textarea>
                            </div>
                            <div class="form-group da-form-group">
                                <label for="formula" class="control-label">Fórmula</label>
                                <textarea class="form-control input-sm dtic-input txt" id="formula" name="formula" rows="3"
                                          placeholder="Fórmula del indicador"></textarea>
                            </div>
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
