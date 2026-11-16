<?php

use app\modules\Planificacion\assets\PlanificacionAsset;
use app\modules\Planificacion\common\helpers\PoaEdicionHelper;
use app\modules\Planificacion\models\FODAUnidad;
use yii\web\JqueryAsset;

PlanificacionAsset::register($this);
PoaEdicionHelper::registrarFlagJs();

$cssPath = Yii::getAlias('@app/modules/Planificacion/css/foda-unidad/style.css');
$version = is_file($cssPath) ? filemtime($cssPath) : time();
$this->registerCssFile('@planificacionModule/css/foda-unidad/style.css?v=' . $version, [
    'depends' => [PlanificacionAsset::class],
]);
$this->registerJsFile('@planificacionModule/js/foda-unidad/dt-declaration.js', [
    'depends' => [JqueryAsset::class],
]);
$this->registerJsFile('@planificacionModule/js/foda-unidad/FodaUnidad.js', [
    'depends' => [JqueryAsset::class],
]);

$this->title = 'Planificación Institucional';
$this->params['subtitle'] = 'FODA Unidad';
$this->params['icon'] = '';
$this->params['iconColor'] = 'info';
$this->params['actions'] = '
    <div class="gestion-badge">
      <span class="dot"></span>
      <span class="text">Gestión Activa</span>
    </div>
';
$this->params['breadcrumbs'][] = ['label' => '/ FODA Unidad'];

$tabs = [
    'Fortaleza' => 'Fortalezas',
    'Oportunidad' => 'Oportunidades',
    'Debilidad' => 'Debilidades',
    'Amenaza' => 'Amenazas',
];
?>

<style>
    .foda-tabs {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 0;
    }
    .foda-tab-btn {
        border: 1px solid transparent;
        background: transparent;
        color: #64748b;
        font-size: 14px;
        font-weight: 600;
        padding: 10px 16px;
        border-radius: 12px 12px 0 0;
        cursor: pointer;
        transition: all .2s;
    }
    .foda-tab-btn:hover { color: #0284c7; background: #f0f9ff; }
    .foda-tab-btn.active {
        color: #0369a1;
        background: #fff;
        border-color: #e2e8f0 #e2e8f0 #fff;
        box-shadow: 0 -1px 0 #fff;
    }
    .foda-tab-btn .tab-count {
        display: inline-block;
        margin-left: 6px;
        min-width: 22px;
        padding: 1px 7px;
        border-radius: 999px;
        font-size: 11px;
        background: #f1f5f9;
        color: #475569;
    }
    .foda-tab-btn.active .tab-count { background: #e0f2fe; color: #0369a1; }
    .foda-tab-pane { display: none; }
    .foda-tab-pane.active { display: block; }
    .body .container .col-headers.foda-col-headers,
    .body .container .card-row.foda-card-row {
        grid-template-columns: 1fr 6fr 2fr 2fr 3fr;
    }
    .body .container .incidencia-badge {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-width: 88px;
        padding: 8px 14px !important;
        border-radius: 999px !important;
        border: 1px solid transparent !important;
        font-size: 12px !important;
        font-weight: 700 !important;
        letter-spacing: .02em;
        line-height: 1.2;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .08);
    }
    .body .container .incidencia-badge .dot {
        width: 8px !important;
        height: 8px !important;
        border-radius: 50% !important;
        flex-shrink: 0;
    }
    .body .container .incidencia-badge.alta {
        background: #fee2e2 !important;
        color: #b91c1c !important;
        border-color: #fca5a5 !important;
    }
    .body .container .incidencia-badge.alta .dot { background: #dc2626 !important; }
    .body .container .incidencia-badge.media {
        background: #fef3c7 !important;
        color: #b45309 !important;
        border-color: #fcd34d !important;
    }
    .body .container .incidencia-badge.media .dot { background: #f59e0b !important; }
    .body .container .incidencia-badge.baja {
        background: #dcfce7 !important;
        color: #15803d !important;
        border-color: #86efac !important;
    }
    .body .container .incidencia-badge.baja .dot { background: #22c55e !important; }
    #modalFodaUnidad #incidencia {
        font-weight: 700;
        min-height: 42px;
    }
    #modalFodaUnidad #incidencia.incidencia-alta {
        background: #fee2e2; color: #b91c1c; border-color: #fca5a5;
    }
    #modalFodaUnidad #incidencia.incidencia-media {
        background: #fef3c7; color: #b45309; border-color: #fcd34d;
    }
    #modalFodaUnidad #incidencia.incidencia-baja {
        background: #dcfce7; color: #15803d; border-color: #86efac;
    }
    #modalFodaUnidad #incidencia option[value="Alta"] { color: #b91c1c; background: #fee2e2; }
    #modalFodaUnidad #incidencia option[value="Media"] { color: #b45309; background: #fef3c7; }
    #modalFodaUnidad #incidencia option[value="Baja"] { color: #15803d; background: #dcfce7; }
    #modalFodaUnidad .da-modal-content {
        display: block; height: auto !important; max-height: none !important; flex: none;
        border: 1px solid #e2e8f0; border-radius: 16px; overflow: visible;
        box-shadow: 0 20px 40px rgba(15, 23, 42, .12);
    }
    #modalFodaUnidad .modal-header { border-bottom: 1px solid #e2e8f0; padding: 12px 16px; background: #f8fafc; }
    #modalFodaUnidad .modal-title { font-size: 16px; font-weight: 700; color: #0f172a; }
    #modalFodaUnidad .modal-body { padding: 16px; }
    #modalFodaUnidad .da-form-group { margin-bottom: 14px; }
    #modalFodaUnidad .da-form-group label { font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px; }
    #modalFodaUnidad .form-control {
        border: 1px solid #e2e8f0; border-radius: 10px; padding: 8px 12px; font-size: 14px;
    }
    #modalFodaUnidad .form-control:focus { outline: 2px solid #7dd3fc; border-color: transparent; }
    #modalFodaUnidad .da-modal-footer {
        border-top: 1px solid #e2e8f0; padding: 10px 16px; gap: 8px; flex-wrap: wrap; height: auto !important;
    }
    #modalFodaUnidad .btn-modal {
        padding: 8px 14px; border-radius: 10px; font-size: 13px; font-weight: 600; border: none; cursor: pointer;
    }
    #modalFodaUnidad .btn-modal-cancel { background: #fff; border: 1px solid #e2e8f0; color: #64748b; }
    #modalFodaUnidad .btn-modal-continue { background: #f0f9ff; color: #0369a1; border: 1px solid #bae6fd; }
    #modalFodaUnidad .btn-modal-save { background: #0284c7; color: #fff; }
    #modalFodaUnidad .invalid-feedback { font-size: 12px; margin-top: 4px; color: #e11d48; }
    .tipo-tab-hint {
        display: inline-block; margin-top: 4px; font-size: 12px; color: #64748b;
    }
</style>

<div class="body">
    <div class="container">
        <div class="header">
            <div class="header-left">
                <div class="logo">🧩</div>
                <div>
                    <h1>FODA Unidad</h1>
                    <p id="fodaSubtitleTotal" class="subtitle">0 registros</p>
                </div>
            </div>
            <div class="header-actions">
                <button type="button" id="btnReportePdf" class="btn-reporte">⬇ Exportar PDF</button>
                <button type="button" id="btnNuevoFoda" class="btn-crear">＋ Nuevo registro</button>
            </div>
        </div>

        <div class="stats">
            <div class="stat" data-estado="V">
                <div class="stat-icon emerald">✅</div>
                <div><p id="fodaCountVigentes" class="stat-count">0</p><p class="stat-label">Vigentes</p></div>
            </div>
            <div class="stat" data-estado="C">
                <div class="stat-icon rose">✖</div>
                <div><p id="fodaCountCaducos" class="stat-count">0</p><p class="stat-label">Caducos</p></div>
            </div>
        </div>

        <div class="foda-tabs" role="tablist">
            <?php $first = true; foreach ($tabs as $tipo => $label): ?>
                <button type="button"
                        class="foda-tab-btn<?= $first ? ' active' : '' ?>"
                        data-tipo="<?= htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8') ?>"
                        data-label="<?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                    <span class="tab-count" data-count-tipo="<?= htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8') ?>">0</span>
                </button>
                <?php $first = false; endforeach; ?>
        </div>

        <div class="toolbar">
            <div class="search">
                <input id="buscarFoda" type="text" placeholder="Buscar por descripción o incidencia...">
            </div>
            <select id="filtroEstadoFoda">
                <option value="">Todos los estados</option>
                <option value="V">Vigente</option>
                <option value="C">Caduco</option>
            </select>
            <select id="filtroIncidenciaFoda">
                <option value="">Todas las incidencias</option>
                <?php foreach (FODAUnidad::incidencias() as $incidencia): ?>
                    <option value="<?= htmlspecialchars($incidencia, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($incidencia, ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
            <button type="button" id="refreshTable" class="btn-refresh" title="Recargar listado">
                <i class="fa fa-sync-alt fa-spin"></i>
            </button>
        </div>

        <div class="col-headers foda-col-headers">
            <span>#</span><span>Descripción</span><span>Incidencia</span><span>Estado</span><span>Acciones</span>
        </div>

        <div id="dticTableLoading" class="p-4">
            <div class="table-loading"></div>
            <div class="table-loading"></div>
            <div class="table-loading"></div>
        </div>

        <div id="dticTableContainer" class="p-0" style="display:none;">
            <?php $first = true; foreach ($tabs as $tipo => $label): ?>
                <?php
                $slug = strtolower($tipo);
                $tableId = 'tablaFoda' . $tipo;
                $pagId = 'fodaPagination' . $tipo;
                ?>
                <div class="foda-tab-pane<?= $first ? ' active' : '' ?>"
                     data-tipo="<?= htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8') ?>"
                     id="pane-<?= htmlspecialchars($slug, ENT_QUOTES, 'UTF-8') ?>">
                    <table id="<?= htmlspecialchars($tableId, ENT_QUOTES, 'UTF-8') ?>" class="w-100 tabla-foda"></table>
                    <div class="pagination" id="<?= htmlspecialchars($pagId, ENT_QUOTES, 'UTF-8') ?>">
                        <p class="pagination-info">Mostrando <strong>0</strong> de <strong>0</strong> registros</p>
                        <div class="pagination-nav"></div>
                    </div>
                </div>
                <?php $first = false; endforeach; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="modalFodaUnidad" tabindex="-1" role="dialog" aria-labelledby="fodaModalTitulo" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content da-modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fodaModalTitulo">Ingreso de datos</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formFodaUnidad" action="" method="post" autocomplete="off">
                    <input type="hidden" id="tipo" name="tipo" value="">
                    <div class="form-group da-form-group">
                        <label class="control-label">Tipo</label>
                        <div id="tipoActivoLabel" class="tipo-tab-hint">Fortaleza</div>
                    </div>
                    <div class="form-group da-form-group">
                        <label for="incidencia" class="control-label">Incidencia</label>
                        <select class="form-control input-sm dtic-input" id="incidencia" name="incidencia">
                            <option value="">Seleccione una incidencia</option>
                            <?php foreach (FODAUnidad::incidencias() as $incidencia): ?>
                                <option value="<?= htmlspecialchars($incidencia, ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($incidencia, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group da-form-group">
                        <label for="descripcion" class="control-label">Descripción</label>
                        <textarea class="form-control input-sm dtic-input txt" id="descripcion" name="descripcion" rows="3"
                                  maxlength="500" placeholder="Descripción del factor FODA"></textarea>
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
