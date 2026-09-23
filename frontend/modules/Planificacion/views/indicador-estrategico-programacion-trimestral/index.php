<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\Planificacion\assets\PlanificacionAsset;
use yii\web\JqueryAsset;

/** @var array|null $objetivo */

PlanificacionAsset::register($this);

$this->registerJsFile('@planificacionModule/js/indicador-estrategico-programacion-trimestral/dt-declaration.js', [
    'depends' => [JqueryAsset::class],
]);
$this->registerJsFile('@planificacionModule/js/indicador-estrategico-programacion-trimestral/index.js', [
    'depends' => [JqueryAsset::class],
]);
$this->registerCssFile('@planificacionModule/css/programacion-indicador/style.css', [
    'depends' => [PlanificacionAsset::class],
]);
$this->registerCssFile('@planificacionModule/css/indicador-estrategico-programacion-trimestral/style.css', [
    'depends' => [PlanificacionAsset::class],
]);

$this->title = 'Planificación Institucional';
$this->params['subtitle'] = 'Programación trimestral de indicadores estratégicos';
$this->params['icon'] = 'fas fa-calendar-alt';
$this->params['iconColor'] = 'info';
$this->params['actions'] = '';
$this->params['breadcrumbs'][] = [
    'label' => '/ Objetivos estratégicos',
    'url' => ['obj-estrategico/index'],
];
$this->params['breadcrumbs'][] = ['label' => '/ Programación trimestral'];
?>

<div class="card">
    <div class="card-body">
        <div class="card-dtic-style">
            <?php if ($objetivo): ?>
                <input type="hidden" id="idObjEstrategico"
                       value="<?= Html::encode($objetivo['IdObjEstrategico']) ?>">

                <div class="objetivo-programacion-card">
                    <div class="dtic-code-container">
                        <span class="dtic-code-text">Objetivo:</span>
                        <div class="dtic-code-badge"><?= Html::encode($objetivo['Compuesto']) ?></div>
                    </div>
                    <div class="dtic-item-main"><?= Html::encode($objetivo['Objetivo']) ?></div>
                    <div class="dtic-item-sub">
                        <b>PRODUCTO:</b> <?= Html::encode($objetivo['Producto']) ?>
                    </div>
                    <div class="dtic-item-sub2 group-container">
                        <div class="sub-group-container">
                            <div class="item-container">
                                <div>AREA</div>
                                <div>&lt; <?= Html::encode($objetivo['areaEstrategica']['Codigo'] ?? '') ?> &gt;</div>
                            </div>
                            <div><?= Html::encode($objetivo['areaEstrategica']['Descripcion'] ?? '') ?></div>
                        </div>
                        <div class="sub-group-container">
                            <div class="item-container">
                                <div>POLITICA</div>
                                <div>&lt; <?= Html::encode($objetivo['politicaEstrategica']['Codigo'] ?? '') ?> &gt;</div>
                            </div>
                            <div><?= Html::encode($objetivo['politicaEstrategica']['Descripcion'] ?? '') ?></div>
                        </div>
                    </div>
                    <div class="dtic-item-sub">
                        <b>Gestión:</b>
                        <?= Html::encode($objetivo['pei']['GestionInicio'] ?? '') ?>
                        -
                        <?= Html::encode($objetivo['pei']['GestionFin'] ?? '') ?>
                    </div>
                    <div class="dtic-item-sub">
                        <small>
                            (<?= Html::encode($objetivo['Indicador_Descripcion']) ?>
                            - <?= Html::encode($objetivo['Indicador_Formula']) ?>)
                        </small>
                    </div>
                </div>

                <div id="dticTableLoading" class="p-4" style="display:none;">
                    <div class="table-loading"></div>
                    <div class="table-loading"></div>
                    <div class="table-loading"></div>
                </div>
                <div class="p-2" id="dticTableContainer" style="display:none;">
                    <table id="tablaListaIndicadoresTrimestrales" class="table w-100 dtic-table"></table>
                </div>
            <?php else: ?>
                <div id="mensajeInicial" class="programacion-empty-state">
                    <i class="fas fa-bullseye"></i>
                    <span>
                        Seleccione un objetivo estratégico desde
                        <a href="<?= Url::to(['obj-estrategico/index']) ?>">objetivos estratégicos</a>
                        para programar sus indicadores.
                    </span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
