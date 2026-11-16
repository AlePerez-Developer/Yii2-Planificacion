<?php

use app\modules\Planificacion\assets\PlanificacionAsset;
use yii\web\JqueryAsset;

PlanificacionAsset::register($this);

$this->registerJsFile('@planificacionModule/js/indicador-estrategico-da/dt-declaration.js', [
    'depends' => [JqueryAsset::class],
]);

$this->registerJsFile('@planificacionModule/js/indicador-estrategico-da/asignacion.js', [
    'depends' => [JqueryAsset::class],
]);

$this->registerCssFile('@planificacionModule/css/indicador-estrategico-da/style.css', [
    'depends' => [PlanificacionAsset::class],
]);

$this->title = 'Planificación Institucional';
$this->params['subtitle'] = 'Asignación de indicadores estratégicos a DA';
$this->params['icon'] = '';
$this->params['iconColor'] = 'info';
$this->params['actions'] = '
    <div class="gestion-badge">
      <span class="dot"></span>
      <span class="text">Gestión Activa: <strong>2026 - 2030</strong></span>
    </div>
';
$this->params['breadcrumbs'][] = [
    'label' => '/ Ind. Estratégicos — Asignar DA',
];
?>

<div class="body">
    <div class="container">
        <div class="header">
            <div class="header-left">
                <div class="logo">🏷️</div>
                <div>
                    <h1>Asignar indicador estratégico</h1>
                    <p id="indDaSubtitleTotal" class="subtitle">0 indicadores registrados</p>
                </div>
            </div>
        </div>

        <div class="toolbar">
            <div class="search">
                <input id="buscarIndicadorDa" type="text" placeholder="Buscar por código, descripción o AEI...">
            </div>
            <button type="button" id="refreshTable" class="btn-refresh" title="Recargar listado">
                <i class="fa fa-sync-alt fa-spin"></i>
            </button>
        </div>

        <div class="col-headers ieda-col-headers">
            <span>#</span><span>Indicador estratégico</span><span>DAs</span>
        </div>

        <div id="dticTableLoading" class="p-4">
            <div class="table-loading"></div>
            <div class="table-loading"></div>
            <div class="table-loading"></div>
        </div>

        <div class="p-0" id="dticTableContainer" style="display:none;">
            <table id="tablaListaIndicadoresDa" class="w-100"></table>
            <div class="pagination" id="indDaPagination">
                <p class="pagination-info">Mostrando <strong>0</strong> de <strong>0</strong> indicadores</p>
                <div class="pagination-nav"></div>
            </div>
        </div>
    </div>
</div>
