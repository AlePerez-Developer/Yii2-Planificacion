<?php

use app\modules\Planificacion\assets\PlanificacionAsset;
use app\modules\Planificacion\common\helpers\PoaEdicionHelper;
use yii\helpers\Url;
use yii\web\JqueryAsset;

PlanificacionAsset::register($this);
PoaEdicionHelper::registrarFlagJs();
$edicion = PoaEdicionHelper::accionesUi();

$this->registerJsFile('@planificacionModule/js/obj-especifico/s2-declaration.js', [
    'depends' => [JqueryAsset::class],
]);
$this->registerJsFile('@planificacionModule/js/obj-especifico/dt-declaration.js', [
    'depends' => [JqueryAsset::class],
]);
$this->registerJsFile('@planificacionModule/js/obj-especifico/index.js', [
    'depends' => [JqueryAsset::class],
]);
$this->registerCssFile('@planificacionModule/css/obj-especifico/style.css', [
    'depends' => [PlanificacionAsset::class],
]);
$this->registerJs("
    urlProgramarPoa = '" . Url::to(['indicador-poa-programacion-trimestral/index']) . "';
");

$this->title = 'Planificación Institucional';
$this->params['subtitle'] = 'Administración de objetivos específicos';
$this->params['icon'] = '';
$this->params['iconColor'] = 'info';
$this->params['actions'] =
    '
            <div class="gestion-badge">
              <span class="dot"></span>
              <span class="text">Gestión Activa</span>
            </div>
        ';
$this->params['breadcrumbs'][] = ['label' => '/ Obj. específicos'];
?>

<style>
    .body .container #tablaListaObjEspecificos.dataTable {
        width: 100% !important;
        border: none !important;
        border-collapse: separate;
        border-spacing: 0;
    }
    .body .container #tablaListaObjEspecificos.dataTable tbody tr,
    .body .container #tablaListaObjEspecificos.dataTable tbody td {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
    }
    .body .container #tablaListaObjEspecificos.dataTable tbody td {
        padding: 0 0 16px 0 !important;
    }
    .body .container .oes-card .fa,
    .body .container .oes-card .fas,
    .body .container .oes-card .far,
    .body .container .toolbar .fa {
        font-family: "Font Awesome 5 Free";
    }
    .body .container .oes-card .far { font-weight: 400; }
    .body .container .oes-card .fa,
    .body .container .oes-card .fas,
    .body .container .toolbar .fa { font-weight: 900; }
    .body .container #tablaListaObjEspecificos .dt-empty {
        background: #fff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 16px;
        padding: 32px 16px !important;
        text-align: center;
        color: #64748b;
        font-weight: 600;
    }
    .body .container #dticTableContainer .dt-container { margin: 0; }

    .body .container .oes-card {
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
    .body .container .oes-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, .05), 0 8px 10px -6px rgba(0, 0, 0, .04);
    }
    .body .container .oes-card-body {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .body .container .oes-head {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }
    .body .container .oes-num {
        width: 28px;
        height: 28px;
        margin-top: 2px;
        border-radius: 999px;
        background: #ea580c;
        color: #fff;
        font-size: 12px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 1px 2px rgba(234, 88, 12, .35);
    }
    .body .container .oes-head-text { min-width: 0; display: flex; flex-direction: column; gap: 4px; }
    .body .container .oes-badges {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }
    .body .container .oes-badge-code {
        display: inline-flex;
        align-items: center;
        background: #ffedd5;
        color: #c2410c;
        border: 1px solid #fed7aa;
        border-radius: 6px;
        padding: 2px 10px;
        font-size: 12px;
        font-weight: 700;
    }
    .body .container .oes-title {
        margin: 0;
        font-size: 16px;
        line-height: 1.35;
        font-weight: 700;
        color: #0f172a;
    }
    .body .container .oes-meta {
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
    .body .container .oes-meta-item {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .body .container .oes-meta-item i { color: #64748b; }
    .body .container .oes-meta-label { font-weight: 700; color: #334155; }
    .body .container .oes-meta-value { font-weight: 600; color: #0f172a; }
    .body .container .oes-actions {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 8px;
        flex-shrink: 0;
        padding-top: 16px;
        border-top: 1px solid #f1f5f9;
    }
    .body .container .oes-card .actions {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 4px;
    }
    .body .container .oes-card .btn-action-new {
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
    .body .container .oes-card .btn-action-new.programar:hover,
    .body .container .oes-card .btn-action-new.edit:hover {
        background: #f0f9ff;
        color: #0284c7;
        transform: scale(1.05);
    }
    .body .container .oes-card .btn-action-new.delete:hover {
        background: #fff1f2;
        color: #e11d48;
        transform: scale(1.05);
    }
    .body .container .oes-card .estado {
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
    .body .container .oes-card .estado.readonly { cursor: default; }
    .body .container .oes-card .estado .dot,
    .body .container .oes-card .estado-option .dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .body .container .oes-card .estado.vigente { background: #ecfdf5; color: #047857; border-color: #a7f3d0; }
    .body .container .oes-card .estado.vigente .dot,
    .body .container .oes-card .estado-option.vigente .dot { background: #10b981; }
    .body .container .oes-card .estado.cancelada { background: #fff1f2; color: #e11d48; border-color: #fecdd3; }
    .body .container .oes-card .estado.cancelada .dot,
    .body .container .oes-card .estado-option.cancelada .dot { background: #fb7185; }
    .body .container .oes-card .estado .chevron { font-size: 10px; margin-left: 2px; opacity: .8; }
    .body .container .oes-card .estado-wrap { position: relative; display: inline-block; }
    .body .container .oes-card .estado-menu {
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
    .body .container .oes-card .estado-wrap.open .estado-menu {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .body .container .oes-card .estado-option {
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
    .body .container .oes-card .estado-option:hover { background: #f8fafc; }
    .body .container .oes-card .estado-option.selected { background: #f1f5f9; }
    .body .container .oes-card .estado-option .check { margin-left: auto; color: #0369a1; font-size: 13px; }

    @media (min-width: 992px) {
        .body .container .oes-card {
            flex-direction: row;
            align-items: flex-start;
            justify-content: space-between;
        }
        .body .container .oes-actions {
            padding-top: 0;
            border-top: none;
        }
        .body .container .oes-title { font-size: 18px; }
    }
</style>

<div class="body">
    <div class="container">
        <div class="header">
            <div class="header-left">
                <div class="logo">🎯</div>
                <div>
                    <h1>Objetivos Específicos</h1>
                    <p id="oesSubtitleTotal" class="subtitle">0 objetivos específicos registrados</p>
                </div>
            </div>
            <div class="header-actions">
                <button type="button" id="btnReportePdf" class="btn-reporte">⬇ Exportar PDF</button>
                <?php if ($edicion['puedeCrear']): ?>
                    <button type="button" id="btnNuevoObjEspecifico" class="btn-crear">＋ Nuevo registro</button>
                <?php endif; ?>
            </div>
        </div>

        <div class="stats">
            <div class="stat">
                <div class="stat-icon emerald">✅</div>
                <div><p id="oesCountVigentes" class="stat-count">0</p><p class="stat-label">Vigentes</p></div>
            </div>
            <div class="stat">
                <div class="stat-icon rose">✖</div>
                <div><p id="oesCountCaducos" class="stat-count">0</p><p class="stat-label">Caducos</p></div>
            </div>
        </div>

        <div class="toolbar">
            <div class="search">
                <input id="buscarOes" type="text" placeholder="Buscar por código u objetivo...">
            </div>
            <select id="filtroEstadoOes">
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
            <table id="tablaListaObjEspecificos" class="w-100"></table>
            <div class="pagination" id="oesPagination">
                <p class="pagination-info">Mostrando <strong>0</strong> de <strong>0</strong> objetivos específicos</p>
                <div class="pagination-nav"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalObjEspecifico" tabindex="-1" role="dialog" aria-labelledby="oesModalTitulo" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content da-modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="oesModalTitulo">Ingreso de datos</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formObjEspecifico" action="" method="post" autocomplete="off">
                    <div class="form-group da-form-group">
                        <label for="idObjInstitucional" class="control-label">Objetivo institucional</label>
                        <select id="idObjInstitucional" name="idObjInstitucional" class="form-control codigo_group dtic-input"></select>
                    </div>
                    <div class="form-group da-form-group oes-codigo">
                        <label for="codigo" class="control-label">Código</label>
                        <input type="text" class="form-control input-sm num codigo_group dtic-input" id="codigo" name="codigo"
                               maxlength="2" inputmode="numeric" placeholder="01">
                        <small class="form-text text-muted">Formato: 01, 02, 03...</small>
                    </div>
                    <div class="form-group da-form-group">
                        <label for="objetivo" class="control-label">Objetivo específico</label>
                        <textarea class="form-control input-sm dtic-input txt" id="objetivo" name="objetivo" rows="4"
                                  maxlength="500" placeholder="Descripción del objetivo específico"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer da-modal-footer">
                <button type="button" id="btnCancelar" class="btn btn-modal btn-modal-cancel">Cancelar</button>
                <?php if ($edicion['puedeCrear']): ?>
                    <button type="button" id="btnGuardarContinuar" class="btn btn-modal btn-modal-continue">Guardar y continuar</button>
                <?php endif; ?>
                <button type="button" id="btnGuardar" class="btn btn-modal btn-modal-save">Guardar</button>
            </div>
        </div>
    </div>
</div>
