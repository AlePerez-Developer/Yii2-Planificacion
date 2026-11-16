<?php

use app\modules\Planificacion\assets\PlanificacionAsset;
use yii\web\JqueryAsset;

PlanificacionAsset::register($this);

$this->registerJsFile('@planificacionModule/js/indicador-estrategico-programacion-anual/dt-declaration.js', [
    'depends' => [JqueryAsset::class],
]);
$this->registerJsFile('@planificacionModule/js/indicador-estrategico-programacion-anual/s2-declaration.js', [
    'depends' => [JqueryAsset::class],
]);
$this->registerJsFile('@planificacionModule/js/indicador-estrategico-programacion-anual/programacion.js', [
    'depends' => [JqueryAsset::class],
]);
$this->registerCssFile('@planificacionModule/css/indicador-estrategico-programacion-anual/style.css', [
    'depends' => [PlanificacionAsset::class],
]);

$this->title = 'Planificación Institucional';
$this->params['subtitle'] = 'Programación anual de indicadores estratégicos';
$this->params['icon'] = '';
$this->params['iconColor'] = 'info';
$this->params['actions'] = '
    <div class="gestion-badge">
      <span class="dot"></span>
      <span class="text">Gestión Activa: <strong>2026 - 2030</strong></span>
    </div>
';
$this->params['breadcrumbs'][] = [
    'label' => '/ Ind. Estratégicos — Programación Anual',
];
?>

<div class="body">
    <div class="container">
        <div class="header">
            <div class="header-left">
                <div class="logo">📅</div>
                <div>
                    <h1>Programación anual</h1>
                    <p id="progAnualSubtitle" class="subtitle">Seleccione un objetivo estratégico</p>
                </div>
            </div>
        </div>

        <div class="toolbar iepa-toolbar">
            <select id="idObjEstrategico" class="form-control dtic-input iepa-obj-select"></select>
            <button type="button" id="refreshTable" class="btn-refresh" title="Recargar listado">
                <i class="fa fa-sync-alt fa-spin"></i>
            </button>
        </div>

        <div id="mensajeInicial" class="programacion-empty-state">
            <i class="fas fa-bullseye"></i>
            <span>Seleccione un objetivo estratégico vigente para mostrar sus indicadores.</span>
        </div>

        <div id="dticTableLoading" class="p-4" style="display:none;">
            <div class="table-loading"></div>
            <div class="table-loading"></div>
            <div class="table-loading"></div>
        </div>

        <div class="p-0" id="dticTableContainer" style="display:none;">
            <table id="tablaListaIndicadores" class="w-100"></table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalLlaves" data-bs-backdrop="static" tabindex="-1" aria-labelledby="modalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content iepa-modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel">Selección de llaves presupuestarias</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="iepa-modal-hint">
                    Solo se listan llaves de las DAs asignadas al indicador.
                </p>
                <input type="hidden" id="modal_Indicador">
                <input type="hidden" id="modal_Gestion">
                <input type="hidden" id="modal_tableIdOriginal">

                <div class="table-responsive">
                    <table id="tblModalDetalle" class="table table-striped table-bordered w-100">
                        <thead>
                        <tr>
                            <th>Llave</th>
                            <th>Descripción</th>
                            <th>Acciones</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer da-modal-footer">
                <button type="button" class="btn btn-modal btn-modal-save" data-bs-dismiss="modal">
                    Cerrar y actualizar
                </button>
            </div>
        </div>
    </div>
</div>
