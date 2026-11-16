<?php

use app\modules\Planificacion\assets\PlanificacionAsset;
use yii\web\JqueryAsset;

PlanificacionAsset::register($this);

$this->registerJsFile('@planificacionModule/js/indicador-estrategico-programacion-global/dt-declaration.js', [
    'depends' => [JqueryAsset::class],
]);

$this->registerJsFile('@planificacionModule/js/indicador-estrategico-programacion-global/programacion.js', [
    'depends' => [JqueryAsset::class],
]);

$this->registerCssFile('@planificacionModule/css/indicador-estrategico-programacion-global/style.css', [
    'depends' => [PlanificacionAsset::class],
]);

$this->title = 'Planificación Institucional';
$this->params['subtitle'] = 'Programación global de metas por gestión';
$this->params['icon'] = '';
$this->params['iconColor'] = 'info';
$this->params['actions'] = '
    <div class="gestion-badge">
      <span class="dot"></span>
      <span class="text">Gestión Activa: <strong>2026 - 2030</strong></span>
    </div>
';
$this->params['breadcrumbs'][] = [
    'label' => '/ Ind. Estratégicos — Programación Global',
];
?>

<div class="body">
    <div class="container">
        <div class="header">
            <div class="header-left">
                <div class="logo">📈</div>
                <div>
                    <h1>Programación global de indicadores</h1>
                    <p id="pgSubtitleTotal" class="subtitle">0 indicadores registrados</p>
                </div>
            </div>
        </div>

        <div class="stats pg-stats">
            <div class="stat">
                <div class="stat-icon emerald">✅</div>
                <div><p id="pgCountCompletos" class="stat-count">0</p><p class="stat-label">Completos</p></div>
            </div>
            <div class="stat">
                <div class="stat-icon amber">⏳</div>
                <div><p id="pgCountParciales" class="stat-count">0</p><p class="stat-label">Parciales</p></div>
            </div>
            <div class="stat">
                <div class="stat-icon rose">✖</div>
                <div><p id="pgCountPendientes" class="stat-count">0</p><p class="stat-label">Pendientes</p></div>
            </div>
        </div>

        <div class="toolbar">
            <div class="search">
                <input id="buscarProgramacionGlobal" type="text" placeholder="Buscar por código, descripción o AEI...">
            </div>
            <button type="button" id="refreshTable" class="btn-refresh" title="Recargar listado">
                <i class="fa fa-sync-alt fa-spin"></i>
            </button>
        </div>

        <div class="col-headers pg-col-headers">
            <span>#</span><span>Indicador estratégico</span><span>Meta / avance</span>
        </div>

        <div id="dticTableLoading" class="p-4">
            <div class="table-loading"></div>
            <div class="table-loading"></div>
            <div class="table-loading"></div>
        </div>

        <div class="p-0" id="dticTableContainer" style="display:none;">
            <table id="tablaListaProgramacionGlobal" class="w-100"></table>
            <div class="pagination" id="pgPagination">
                <p class="pagination-info">Mostrando <strong>0</strong> de <strong>0</strong> indicadores</p>
                <div class="pagination-nav"></div>
            </div>
        </div>
    </div>
</div>
