<?php

use app\modules\Planificacion\assets\PlanificacionAsset;
use yii\web\JqueryAsset;

PlanificacionAsset::register($this);
$this->registerCssFile('@planificacionModule/css/catalogo/style.css', ['depends' => [PlanificacionAsset::class]]);
$this->registerJsFile('@planificacionModule/js/catalogo/dt-helpers.js', ['depends' => [JqueryAsset::class]]);
$this->registerJsFile('@planificacionModule/js/control-envio-poa/dt-declaration.js', ['depends' => [JqueryAsset::class]]);
$this->registerJsFile('@planificacionModule/js/control-envio-poa/index.js', ['depends' => [JqueryAsset::class]]);

$this->title = 'Planificación Institucional';
$this->params['subtitle'] = 'Control de envío POA';
$this->params['icon'] = 'fas fa-paper-plane';
$this->params['iconColor'] = 'warning';
$this->params['breadcrumbs'][] = ['label' => '/ Envíos POA'];
?>

<div class="card">
    <div id="divTabla" class="card-body">
        <div class="card-dtic-style">
            <div class="card-dtic-style-header">
                <div class="card-dtic-style-title">Envíos de unidades</div>
            </div>
            <p class="px-3 pt-3 mb-0 text-muted">
                Elimine un envío para que la unidad pueda volver a modificar su POA dentro del cronograma vigente.
            </p>
            <div id="dticTableLoading" class="p-4">
                <div class="table-loading"></div>
                <div class="table-loading"></div>
                <div class="table-loading"></div>
            </div>
            <div id="dticTableContainer" class="p-2" style="display:none">
                <table id="tablaListaEnvios" class="table w-100 dtic-table"></table>
            </div>
        </div>
    </div>
</div>
