<?php

use yii\web\JqueryAsset;

app\modules\Planificacion\assets\PlanificacionAsset::register($this);

$this->registerJsFile("@planificacionModule/js/da/Da.js", [
        'depends' => [
                JqueryAsset::class
        ]
]);

$this->registerJsFile("@planificacionModule/js/da/dt-declaration.js", [
        'depends' => [
                JqueryAsset::class
        ]
]);

$this->title = 'Planificación Institucional';

$this->params['subtitle'] = 'Administración de direcciones administrativas';

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
        'label' => '/ Direccion Administrativa',
];
?>

<div class="body">
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <div class="logo">📅</div>
                <div>
                    <h1>Listado de direcciones administrativas</h1>
                    <p id="daSubtitleTotal" class="subtitle">0 direcciones administrativas registradas</p>
                </div>
            </div>
            <div class="header-actions">
                <button class="btn btn-pdf">⬇ Exportar PDF</button>
                <button class="btn btn-primary">＋ Nueva cita</button>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats">
            <div class="stat">
                <div class="stat-icon sky">✅</div>
                <div><p class="stat-count">0</p><p class="stat-label">Vigentes</p></div>
            </div>

            <div class="stat">
                <div class="stat-icon rose">✖</div>
                <div><p class="stat-count">0</p><p class="stat-label">Canceladas</p></div>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="toolbar">
            <div class="search">
                <input id="buscarDa" type="text" placeholder="Buscar por codigo de DA  o descripcion...">
            </div>
            <select id="filtroEstadoDa">
                <option value="">Todos los estados</option>
                <option value="V">Vigente</option>
                <option value="C">Caduca</option>
            </select>
            <button type="button" id="refreshTable" class="btn-refresh" title="Recargar listado">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>

        <!-- Encabezado de columnas -->
        <div class="col-headers">
            <span>#</span><span>Paciente</span><span>Fecha y hora</span><span>Estado</span><span>Acciones</span>
        </div>

        <div id="dticTableLoading" class="p-4">
            <div class="table-loading"></div>
            <div class="table-loading"></div>
            <div class="table-loading"></div>
        </div>

        <div class="p-0" id="dticTableContainer" style="display:none;">
            <table id="tablaListaDas" class="w-100" > </table>
        </div>
    </div>
</div>



<style>
td { padding-bottom: 10px !important; }
    thead { display: none}
    /** { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', system-ui, sans-serif; }*/
    .body { background: #f8fafc; padding: 8px 16px; color: #1e293b; }
    .container { max-width: 90%; margin: 0 auto; display: flex; flex-direction: column; gap: 24px; }
    .container * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', system-ui, sans-serif; }

    /* Header */
    .header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; }
    .header-left { display: flex; align-items: center; gap: 12px; }
    .logo { width: 48px; height: 48px; border-radius: 16px; background: #0284c7; color: #fff;
        display: flex; align-items: center; justify-content: center; font-size: 22px;
        box-shadow: 0 4px 10px rgba(2,132,199,.2); }
    h1 { font-size: 24px; font-weight: 700; }
    .subtitle { font-size: 14px; color: #64748b; }
    .header-actions { display: flex; gap: 8px; }
    .btn { padding: 10px 16px; border-radius: 10px; font-size: 14px; font-weight: 600;
        cursor: pointer; border: none; transition: all .2s; }
    .btn-pdf { background: #fff; border: 1px solid #e2e8f0; color: #475569; box-shadow: 0 1px 2px rgba(0,0,0,.05); }
    .btn-pdf:hover { border-color: #fecdd3; background: #fff1f2; color: #e11d48; }
    .btn-primary { background: #0284c7; color: #fff; box-shadow: 0 1px 2px rgba(0,0,0,.1); }
    .btn-primary:hover { background: #0369a1; }

    /* Stats */
    .stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
    .stat { display: flex; align-items: center; gap: 12px; background: #fff; border: 1px solid #e2e8f0;
        border-radius: 16px; padding: 16px; cursor: pointer; transition: box-shadow .2s; }
    .stat:hover { box-shadow: 0 4px 12px rgba(0,0,0,.08); }
    .stat.active { border-color: #7dd3fc; box-shadow: 0 0 0 2px #bae6fd; }
    .stat-icon { width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center;
        justify-content: center; font-size: 18px; }
    .stat-icon.amber { background: #fffbeb; color: #d97706; }
    .stat-icon.sky { background: #f0f9ff; color: #0284c7; }
    .stat-icon.emerald { background: #ecfdf5; color: #059669; }
    .stat-icon.rose { background: #fff1f2; color: #f43f5e; }
    .stat-count { font-size: 24px; font-weight: 700; }
    .stat-label { font-size: 12px; color: #64748b; font-weight: 500; }

    /* Search */
    .toolbar { display: flex; gap: 8px; flex-wrap: wrap; }
    .search { flex: 1; min-width: 220px; position: relative; }
    .search input { width: 100%; height: 44px; padding: 0 14px 0 38px; border: 1px solid #e2e8f0;
        border-radius: 12px; background: #fff; font-size: 14px; box-shadow: 0 1px 2px rgba(0,0,0,.05); }
    .search input:focus { outline: 2px solid #7dd3fc; border-color: transparent; }
    .search::before { content: "🔍"; position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-size: 13px; opacity: .5; }
    .toolbar select { height: 44px; padding: 0 14px; border: 1px solid #e2e8f0; border-radius: 12px;
        background: #fff; font-size: 14px; color: #334155; box-shadow: 0 1px 2px rgba(0,0,0,.05); }
    .toolbar .btn-refresh { flex-shrink: 0; width: 44px; height: 44px; margin: 0; padding: 0;
        border-radius: 12px; border: 1px solid #e2e8f0; background: #fff; color: #0284c7;
        box-shadow: 0 1px 2px rgba(0,0,0,.05); cursor: pointer; transition: all .2s; }
    .toolbar .btn-refresh:hover { border-color: #7dd3fc; background: #f0f9ff; }

    /* Column headers */
    .col-headers { display: grid; grid-template-columns: 1fr 4fr 2fr 2fr 3fr; gap: 16px;
        padding: 0 30px; font-size: 11px; font-weight: 600; text-transform: uppercase;
        letter-spacing: .05em; color: #94a3b8; }
    .col-headers span:last-child { text-align: right; }

    /* Row cards */
    .list { display: flex; flex-direction: column; gap: 12px; }
    .card-row { display: grid; grid-template-columns: 1fr 4fr 2fr 2fr 3fr; gap: 16px; align-items: center;
        background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 16px 20px;
        box-shadow: 0 1px 2px rgba(0,0,0,.05); transition: all .2s; }
    .card-row:hover { border-color: #bae6fd; box-shadow: 0 6px 16px rgba(0,0,0,.08); }
    .row-num { width: 32px; height: 32px; border-radius: 8px; background: #f1f5f9; color: #64748b;
        font-size: 14px; font-weight: 700; display: flex; align-items: center; justify-content: center; }
    .patient { display: flex; align-items: center; gap: 12px; min-width: 0; }
    .avatar { width: 40px; height: 40px; border-radius: 50%; color: #fff; font-size: 14px; font-weight: 700;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .patient-name { font-weight: 600; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
    .code-badge { font-family: monospace; font-size: 12px; font-weight: 600; color: #4f46e5;
        background: #eef2ff; border: 1px solid #c7d2fe; border-radius: 6px; padding: 2px 8px; }
    .patient-meta { font-size: 12px; color: #64748b; margin-top: 2px; }
    .patient-motivo { font-size: 12px; color: #94a3b8; margin-top: 2px; white-space: nowrap;
        overflow: hidden; text-overflow: ellipsis; }
    .datetime { font-size: 14px; color: #475569; }
    .datetime small { display: block; font-size: 12px; color: #94a3b8; margin-top: 2px; }

    /* Estado badge */
    .estado { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600;
        border-radius: 999px; padding: 6px 12px; border: 1px solid; cursor: pointer; transition: box-shadow .2s; }
    .estado:hover { box-shadow: 0 2px 6px rgba(0,0,0,.1); }
    .estado .dot { width: 6px; height: 6px; border-radius: 50%; }
    .estado.pendiente { background: #fffbeb; color: #b45309; border-color: #fde68a; }
    .estado.pendiente .dot { background: #fbbf24; }
    .estado.confirmada { background: #f0f9ff; color: #0369a1; border-color: #bae6fd; }
    .estado.confirmada .dot { background: #0ea5e9; }
    .estado.completada { background: #ecfdf5; color: #047857; border-color: #a7f3d0; }
    .estado.completada .dot { background: #10b981; }
    .estado.cancelada { background: #fff1f2; color: #e11d48; border-color: #fecdd3; }
    .estado.cancelada .dot { background: #fb7185; }

    /* Acciones */
    .actions { display: flex; justify-content: flex-end; gap: 4px; }
    .btn-action { padding: 8px 12px; border-radius: 8px; background: transparent; border: none;
        font-size: 13px; font-weight: 500; color: #94a3b8; cursor: pointer; transition: all .2s; }
    .btn-action.edit:hover { background: #f0f9ff; color: #0284c7; transform: scale(1.05); }
    .btn-action.delete:hover { background: #fff1f2; color: #e11d48; transform: scale(1.05); }

    /* Paginación */
    .pagination { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;
        gap: 12px; background: #fff; border: 1px solid #e2e8f0; border-radius: 16px;
        padding: 12px 20px; box-shadow: 0 1px 2px rgba(0,0,0,.05); }
    .pagination-info { font-size: 14px; color: #64748b; }
    .pagination-info strong { color: #334155; }
    .pagination-nav { display: flex; align-items: center; gap: 4px; }
    .page-btn { min-width: 32px; height: 32px; padding: 0 8px; border-radius: 8px; border: none;
        background: transparent; font-size: 14px; font-weight: 600; color: #64748b;
        cursor: pointer; transition: all .2s; }
    .page-btn:hover { background: #f0f9ff; color: #0284c7; }
    .page-btn.current { background: #0284c7; color: #fff; box-shadow: 0 1px 2px rgba(0,0,0,.1); }
    .page-btn:disabled { opacity: .4; cursor: not-allowed; }

    @media (max-width: 768px) {
        .stats { grid-template-columns: repeat(2, 1fr); }
        .col-headers { display: none; }
        .card-row { grid-template-columns: 1fr; gap: 12px; }
        .actions { justify-content: flex-start; }
    }

</style>