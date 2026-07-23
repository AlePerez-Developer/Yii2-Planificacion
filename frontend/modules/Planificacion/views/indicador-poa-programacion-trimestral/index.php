<?php

use app\modules\Planificacion\assets\PlanificacionAsset;
use yii\web\JqueryAsset;

PlanificacionAsset::register($this);
$this->registerCssFile('@planificacionModule/css/indicador-poa-programacion-trimestral/style.css');
$this->registerJsFile('@planificacionModule/js/indicador-poa-programacion-trimestral/s2-declaration.js', ['depends' => [JqueryAsset::class]]);
$this->registerJsFile('@planificacionModule/js/indicador-poa-programacion-trimestral/dt-declaration.js', ['depends' => [JqueryAsset::class]]);
$this->registerJsFile('@planificacionModule/js/indicador-poa-programacion-trimestral/index.js', ['depends' => [JqueryAsset::class]]);

$this->title = 'Planificación Institucional';
$this->params['subtitle'] = 'Programación trimestral de indicadores POA';
$this->params['icon'] = 'fas fa-calendar-alt';
$this->params['iconColor'] = 'primary';
$this->params['breadcrumbs'][] = ['label' => '/ Programación trimestral de indicadores POA'];
?>

<div class="card">
    <div class="card-body">
        <div class="card-dtic-style">
            <div class="card-dtic-style-header">
                <div class="card-dtic-style-title">Seleccione un objetivo específico</div>
            </div>
            <div class="p-3">
                <select id="idObjEspecifico" class="form-control dtic-input" style="width:100%;"></select>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="card-dtic-style">
            <div class="card-dtic-style-header">
                <div class="card-dtic-style-title">Indicadores POA</div>
            </div>
            <div id="dticTableLoading" class="p-4">
                <div class="table-loading"></div><div class="table-loading"></div><div class="table-loading"></div>
            </div>
            <div id="dticTableContainer" class="p-2" style="display:none;">
                <table id="tablaListaIndicadoresPoaProgramacion" class="table w-100 dtic-table"></table>
            </div>
        </div>
    </div>
</div>
