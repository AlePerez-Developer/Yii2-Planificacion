<?php

use app\modules\Planificacion\assets\PlanificacionAsset;
use yii\web\JqueryAsset;

PlanificacionAsset::register($this);
$cssPath = Yii::getAlias(
    '@app/modules/Planificacion/css/indicador-poa-programacion-anual/style.css'
);
$cssVersion = is_file($cssPath) ? filemtime($cssPath) : time();
$this->registerCssFile(
    '@planificacionModule/css/indicador-poa-programacion-anual/style.css?v=' . $cssVersion,
    ['depends' => [PlanificacionAsset::class]]
);
$this->registerJsFile('@planificacionModule/js/indicador-poa-programacion-anual/s2-declaration.js', ['depends' => [JqueryAsset::class]]);
$this->registerJsFile('@planificacionModule/js/indicador-poa-programacion-anual/dt-declaration.js', ['depends' => [JqueryAsset::class]]);
$this->registerJsFile('@planificacionModule/js/indicador-poa-programacion-anual/index.js', ['depends' => [JqueryAsset::class]]);

$this->title = 'Planificación Institucional';
$this->params['subtitle'] = 'Programación anual de indicadores POA';
$this->params['icon'] = 'fas fa-calendar-alt';
$this->params['iconColor'] = 'primary';
$this->params['actions'] = '
    <button id="btnAgregarRelacion" class="btn-crear" disabled>
        <i class="fas fa-plus-circle"></i>
        <span class="btn-text">Agregar relación</span>
    </button>';
$this->params['breadcrumbs'][] = ['label' => '/ Programación anual de indicadores POA'];
?>

<div class="card">
    <div class="card-body">
        <div class="card-dtic-style">
            <div class="card-dtic-style-header programacion-anual-header">
                <div class="card-dtic-style-title">Objetivos específicos</div>
                <select id="idObjEspecifico" class="form-control dtic-input" style="width:100%"></select>
            </div>

            <div class="programacion-header">
                <div class="card-dtic-style-title">Relaciones programadas</div>
                <div id="resumenAnual" class="meta-summary">Total programado: 0</div>
            </div>

            <div id="mensajeInicial" class="programacion-empty-state">
                <i class="fas fa-bullseye"></i>
                <span>Seleccione un objetivo específico para consultar su programación anual.</span>
            </div>
            <div id="dticTableLoading" class="p-4" style="display:none">
                <div class="table-loading"></div>
                <div class="table-loading"></div>
                <div class="table-loading"></div>
            </div>
            <div id="dticTableContainer" class="p-2" style="display:none">
                <div class="table-responsive programacion-table-container">
                    <table id="tablaProgramacionPoaAnual"
                           class="table table-sm table-bordered w-100 dtic-gestion-table"></table>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modalRelacionPoa" class="modal fade" tabindex="-1" role="dialog"
     aria-labelledby="tituloModalRelacionPoa" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="tituloModalRelacionPoa" class="modal-title">Agregar relación anual</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formRelacionPoa" autocomplete="off">
                    <div class="form-group">
                        <label for="idLlavePresupuestaria">Llave presupuestaria</label>
                        <select id="idLlavePresupuestaria" name="idLlavePresupuestaria"
                                class="form-control dtic-input" style="width:100%"></select>
                    </div>
                    <div class="form-group">
                        <label for="idIndicadorPoa">Indicador POA</label>
                        <select id="idIndicadorPoa" name="idIndicadorPoa"
                                class="form-control dtic-input" style="width:100%"></select>
                    </div>
                    <div class="form-group">
                        <label for="metaProgramada">Meta programada</label>
                        <input id="metaProgramada" name="metaProgramada" type="number"
                               min="0" step="1" value="0" class="form-control dtic-input">
                        <small class="form-text text-muted">
                            Solo se permiten números enteros mayores o iguales a cero.
                        </small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button id="btnGuardarRelacion" type="button" class="btn-guardar">
                    <i class="fa fa-check-circle"></i>
                    <span class="btn_text">Guardar</span>
                </button>
            </div>
        </div>
    </div>
</div>
