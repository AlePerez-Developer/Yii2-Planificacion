<?php

use app\modules\Planificacion\assets\PlanificacionAsset;
use yii\web\JqueryAsset;

PlanificacionAsset::register($this);
$cssPath = Yii::getAlias(
    '@app/modules/Planificacion/css/indicador-poa-programacion-trimestral/style.css'
);
$cssVersion = is_file($cssPath) ? filemtime($cssPath) : time();
$this->registerCssFile(
    '@planificacionModule/css/indicador-poa-programacion-trimestral/style.css?v=' . $cssVersion,
    ['depends' => [PlanificacionAsset::class]]
);
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
            <div class="card-dtic-style-header programacion-trimestral-header">
                <div class="card-dtic-style-title">Objetivos específicos</div>
                <select id="idObjEspecifico" class="form-control dtic-input" style="width:100%"></select>
            </div>

            <div id="resumenTrimestral" class="resumen-trimestral">
                <span>Anual: <b data-total="anual">0</b></span>
                <span>T1: <b data-total="t1">0</b></span>
                <span>T2: <b data-total="t2">0</b></span>
                <span>T3: <b data-total="t3">0</b></span>
                <span>T4: <b data-total="t4">0</b></span>
                <span>Total trimestral: <b data-total="trimestral">0</b></span>
            </div>

            <div id="mensajeInicial" class="programacion-empty-state">
                <i class="fas fa-bullseye"></i>
                <span>Seleccione un objetivo específico para consultar sus relaciones anuales.</span>
            </div>
            <div id="dticTableLoading" class="p-4" style="display:none">
                <div class="table-loading"></div>
                <div class="table-loading"></div>
                <div class="table-loading"></div>
            </div>
            <div id="dticTableContainer" class="p-2" style="display:none">
                <div class="table-responsive programacion-table-container">
                    <table id="tablaProgramacionPoaTrimestral"
                           class="table table-sm table-bordered w-100 dtic-gestion-table"></table>
                </div>
            </div>
        </div>
    </div>
</div>
