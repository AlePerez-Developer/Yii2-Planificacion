<?php

use app\modules\Planificacion\assets\PlanificacionAsset;
use yii\web\JqueryAsset;

PlanificacionAsset::register($this);

$this->registerJsFile("@planificacionModule/js/indicador-estrategico/dt-declaration.js", [
        'depends' => [
                JqueryAsset::class
        ]
]);

$this->registerJsFile("@planificacionModule/js/indicador-estrategico/s2-declaration.js", [
        'depends' => [
                JqueryAsset::class
        ]
]);

$this->registerJsFile("@planificacionModule/js/indicador-estrategico/IndicadorEstrategico.js", [
        'depends' => [
                JqueryAsset::class
        ]
]);

$this->registerCssFile("@planificacionModule/css/indicador-estrategico/style.css", [
        'depends' => [
                PlanificacionAsset::class
        ]
]);

$this->title = 'Planificación Institucional';

$this->params['subtitle'] = 'Administración de indicadores estratégicos';

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
        'label' => '/ Ind. Estratégicos',
];
?>

<div class="body">
    <div class="container">
        <div class="header">
            <div class="header-left">
                <div class="logo">📈</div>
                <div>
                    <h1>Listado de indicadores estratégicos</h1>
                    <p id="indEstrategicoSubtitleTotal" class="subtitle">0 indicadores registrados</p>
                </div>
            </div>
            <div class="header-actions">
                <button type="button" id="btnReportePdf" class="btn-reporte">⬇ Exportar PDF</button>
                <button type="button" id="btnNuevoIndicador" class="btn-crear">＋ Nuevo registro</button>
            </div>
        </div>

        <div class="stats">
            <div class="stat">
                <div class="stat-icon emerald">✅</div>
                <div><p id="indEstrategicoCountVigentes" class="stat-count">0</p><p class="stat-label">Vigentes</p></div>
            </div>
            <div class="stat">
                <div class="stat-icon rose">✖</div>
                <div><p id="indEstrategicoCountCaducos" class="stat-count">0</p><p class="stat-label">Caducos</p></div>
            </div>
        </div>

        <div class="toolbar">
            <div class="search">
                <input id="buscarIndicador" type="text" placeholder="Buscar por código, descripción o AEI...">
            </div>
            <select id="filtroEstadoIndicador">
                <option value="">Todos los estados</option>
                <option value="V">Vigente</option>
                <option value="C">Caduco</option>
            </select>
            <button type="button" id="refreshTable" class="btn-refresh" title="Recargar listado">
                <i class="fa fa-sync-alt fa-spin"></i>
            </button>
        </div>

        <div class="col-headers ie-col-headers">
            <span>#</span><span>Indicador estratégico</span><span>Estado</span><span>Acciones</span>
        </div>

        <div id="dticTableLoading" class="p-4">
            <div class="table-loading"></div>
            <div class="table-loading"></div>
            <div class="table-loading"></div>
        </div>

        <div class="p-0" id="dticTableContainer" style="display:none;">
            <table id="tablaListaIndicadoresEstrategicos" class="w-100"></table>
            <div class="pagination" id="indEstrategicoPagination">
                <p class="pagination-info">Mostrando <strong>0</strong> de <strong>0</strong> indicadores</p>
                <div class="pagination-nav"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalIndicadorEstrategico" tabindex="-1" role="dialog" aria-labelledby="indEstrategicoModalTitulo" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content da-modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="indEstrategicoModalTitulo">Ingreso de datos</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formIndicadorEstrategico" action="" method="post" autocomplete="off">
                    <div class="form-group da-form-group">
                        <label for="idObjEstrategico" class="control-label">Objetivo estratégico</label>
                        <select class="form-control input-sm objEstrategico codigo_group dtic-input"
                                id="idObjEstrategico" name="idObjEstrategico"></select>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group da-form-group">
                                <label for="codigo" class="control-label">Código</label>
                                <input type="text" class="form-control input-sm num codigo_group dtic-input"
                                       id="codigo" name="codigo" maxlength="3" inputmode="numeric"
                                       placeholder="Código">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group da-form-group">
                                <label for="meta" class="control-label">Meta</label>
                                <input type="text" class="form-control input-sm num dtic-input" id="meta" name="meta"
                                       inputmode="numeric" placeholder="Meta">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group da-form-group">
                                <label for="lineaBase" class="control-label">Línea base</label>
                                <input type="text" class="form-control input-sm num dtic-input" id="lineaBase"
                                       name="lineaBase" inputmode="numeric" placeholder="Línea base">
                            </div>
                        </div>
                    </div>
                    <div class="form-group da-form-group">
                        <label for="descripcion" class="control-label">Descripción</label>
                        <textarea class="form-control input-sm txt dtic-input" id="descripcion" name="descripcion"
                                  rows="2" placeholder="Descripción del indicador"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group da-form-group">
                                <label for="idTipoResultado" class="control-label">Tipo de resultado</label>
                                <select class="form-control input-sm dtic-input" id="idTipoResultado" name="idTipoResultado"></select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group da-form-group">
                                <label for="idCategoriaIndicador" class="control-label">Categoría</label>
                                <select class="form-control input-sm dtic-input" id="idCategoriaIndicador"
                                        name="idCategoriaIndicador"></select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group da-form-group">
                                <label for="idUnidadIndicador" class="control-label">Unidad</label>
                                <select class="form-control input-sm dtic-input" id="idUnidadIndicador"
                                        name="idUnidadIndicador"></select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group da-form-group">
                        <label for="idAccionEstrategica" class="control-label">Acción estratégica</label>
                        <select class="form-control input-sm dtic-input" id="idAccionEstrategica" name="idAccionEstrategica"></select>
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
