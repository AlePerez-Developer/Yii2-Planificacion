<?php

use app\modules\Planificacion\assets\PlanificacionAsset;
use yii\web\JqueryAsset;

PlanificacionAsset::register($this);

$this->registerJsFile('@planificacionModule/js/indicador-estrategico-programacion-trimestral/s2-declaration.js', [
    'depends' => [JqueryAsset::class],
]);
$this->registerJsFile('@planificacionModule/js/indicador-estrategico-programacion-trimestral/dt-declaration.js', [
    'depends' => [JqueryAsset::class],
]);
$this->registerJsFile('@planificacionModule/js/indicador-estrategico-programacion-trimestral/index.js', [
    'depends' => [JqueryAsset::class],
]);
$this->registerCssFile('@planificacionModule/css/indicador-estrategico-programacion-trimestral/style.css', [
    'depends' => [PlanificacionAsset::class],
]);

$this->title = 'Planificación Institucional';
$this->params['subtitle'] = 'Programación trimestral de indicadores estratégicos';
$this->params['icon'] = 'fas fa-calendar-alt';
$this->params['iconColor'] = 'info';
$this->params['actions'] = '';
$this->params['breadcrumbs'][] = ['label' => '/ Programación trimestral'];
?>

<div class="card">
    <div class="card-body">
        <div class="card-dtic-style">
            <div class="card-dtic-style-header programacion-trimestral-header">
                <div class="card-dtic-style-title">Objetivos Estratégicos</div>
                <div class="selector-objetivo-wrapper">
                    <select id="idObjEstrategico" class="form-control dtic-input"></select>
                </div>
            </div>

            <div id="dticTableLoading" class="p-4" style="display:none;">
                <div class="table-loading"></div>
                <div class="table-loading"></div>
                <div class="table-loading"></div>
            </div>

            <div id="mensajeInicial" class="programacion-empty-state">
                <i class="fas fa-bullseye"></i>
                <span>Seleccione un objetivo estratégico para mostrar sus indicadores programados.</span>
            </div>

            <div class="p-2" id="dticTableContainer" style="display:none;">
                <table id="tablaListaIndicadoresTrimestrales" class="table w-100 dtic-table"></table>
            </div>
        </div>
    </div>
</div>
