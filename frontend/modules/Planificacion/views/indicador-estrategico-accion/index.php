<?php

use yii\web\JqueryAsset;
use app\modules\Planificacion\assets\PlanificacionAsset;

PlanificacionAsset::register($this);

$this->registerJsFile("@planificacionModule/js/indicador-estrategico-accion/dt-declaration.js", [
        'depends' => [
                JqueryAsset::class
        ]
]);

$this->registerJsFile("@planificacionModule/js/indicador-estrategico-accion/s2-declaration.js", [
        'depends' => [
                JqueryAsset::class
        ]
]);

$this->registerJsFile("@planificacionModule/js/indicador-estrategico-accion/acciones.js", [
        'depends' => [
                JqueryAsset::class
        ]
]);

$this->registerCssFile("@planificacionModule/css/indicador-estrategico-accion/style.css", [
        'depends' => [
                PlanificacionAsset::class
        ]
]);

$this->title = 'Planificación Institucional';

$this->params['subtitle'] = 'Asignación de acciones estratégicas';

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
        'label' => '/ Ind. Estratégicos — Acciones',
];
?>

<div class="body">
    <div class="container">
        <div class="header">
            <div class="header-left">
                <div class="logo">🔗</div>
                <div>
                    <h1>Asignación de acciones estratégicas</h1>
                    <p id="indAccionSubtitleTotal" class="subtitle">Seleccione un objetivo estratégico</p>
                </div>
            </div>
        </div>

        <div class="toolbar iea-toolbar">
            <select id="idObjEstrategico" class="form-control dtic-input iea-obj-select"></select>
            <button type="button" id="refreshTable" class="btn-refresh" title="Recargar listado">
                <i class="fa fa-sync-alt fa-spin"></i>
            </button>
        </div>

        <div class="col-headers iea-col-headers">
            <span>#</span><span>Indicador estratégico</span><span>Acciones</span>
        </div>

        <div id="mensajeInicial" class="programacion-empty-state">
            <i class="fas fa-bullseye"></i>
            <span>Seleccione un objetivo estratégico para mostrar sus indicadores.</span>
        </div>

        <div id="dticTableLoading" class="p-4" style="display:none;">
            <div class="table-loading"></div>
            <div class="table-loading"></div>
            <div class="table-loading"></div>
        </div>

        <div class="p-0" id="dticTableContainer" style="display:none;">
            <table id="tablaListaIndicadoresEstrategicosAccion" class="w-100"></table>
            <div class="pagination" id="indAccionPagination">
                <p class="pagination-info">Mostrando <strong>0</strong> de <strong>0</strong> indicadores</p>
                <div class="pagination-nav"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAsignacion" data-bs-backdrop="static" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content da-modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel">Asignación de acción estratégica</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formAsignacionAccion" action="" method="post" autocomplete="off">
                    <div class="form-group da-form-group">
                        <label for="idAccionEstrategica" class="control-label">Acción estratégica</label>
                        <select class="form-control input-sm dtic-input" id="idAccionEstrategica" name="idAccionEstrategica"></select>
                    </div>
                </form>
            </div>
            <div class="modal-footer da-modal-footer">
                <button type="button" class="btn btn-modal btn-modal-cancel" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-modal btn-modal-save guardar">Guardar</button>
            </div>
        </div>
    </div>
</div>
