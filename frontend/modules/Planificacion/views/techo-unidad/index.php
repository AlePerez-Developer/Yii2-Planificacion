<?php

use app\modules\Planificacion\assets\PlanificacionAsset;
use yii\web\JqueryAsset;

PlanificacionAsset::register($this);
$cssPath = Yii::getAlias('@app/modules/Planificacion/css/techo-unidad/style.css');
$version = is_file($cssPath) ? filemtime($cssPath) : time();
$this->registerCssFile('@planificacionModule/css/techo-unidad/style.css?v=' . $version, [
    'depends' => [PlanificacionAsset::class],
]);
$this->registerJsFile('@planificacionModule/js/techo-unidad/dt-declaration.js', ['depends' => [JqueryAsset::class]]);
$this->registerJsFile('@planificacionModule/js/techo-unidad/index.js', ['depends' => [JqueryAsset::class]]);

$this->title = 'Planificación Institucional';
$this->params['subtitle'] = 'Asignación de techos por llave';
$this->params['icon'] = 'fas fa-wallet';
$this->params['iconColor'] = 'primary';
?>

<div id="resumenTechos" class="resumen-techos mb-3">
    <div class="resumen-card"><span>Ingresos disponibles</span><b data-resumen="ingresos">0</b></div>
    <div class="resumen-card"><span>Techos asignados</span><b data-resumen="techos">0</b></div>
    <div class="resumen-card"><span>Disponible</span><b data-resumen="disponible">0</b></div>
    <div class="resumen-card regla"><span>Regla activa</span><b data-resumen="regla">—</b></div>
</div>

<div class="card">
    <div class="card-body">
        <div class="card-dtic-style">
            <div class="card-dtic-style-header">
                <div class="card-dtic-style-title">Llaves presupuestarias de la unidad activa</div>
            </div>
            <div id="dticTableLoading" class="p-4">
                <div class="table-loading"></div><div class="table-loading"></div>
            </div>
            <div id="dticTableContainer" class="p-2" style="display:none">
                <table id="tablaTechos" class="table w-100 dtic-table"></table>
            </div>
        </div>
    </div>
</div>
