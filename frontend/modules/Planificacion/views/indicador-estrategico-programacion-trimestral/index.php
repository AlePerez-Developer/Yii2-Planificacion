<?php

use app\modules\Planificacion\assets\PlanificacionAsset;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JqueryAsset;

/** @var array|null $objetivo */

PlanificacionAsset::register($this);

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
$this->params['icon'] = '';
$this->params['iconColor'] = 'info';
$this->params['actions'] = '
    <div class="gestion-badge">
      <span class="dot"></span>
      <span class="text">Gestión Activa: <strong>2026 - 2030</strong></span>
    </div>
';
$this->params['breadcrumbs'][] = [
    'label' => '/ Objetivos estratégicos',
    'url' => ['obj-estrategico/index'],
];
$this->params['breadcrumbs'][] = ['label' => '/ Programación trimestral'];

$compuesto = $objetivo['Compuesto'] ?? '';
$textoObjetivo = $objetivo['Objetivo'] ?? '';
$producto = $objetivo['Producto'] ?? '';
?>

<div class="body">
    <div class="container">
        <div class="header">
            <div class="header-left">
                <div class="logo">🗓️</div>
                <div>
                    <h1>Programación trimestral</h1>
                    <p id="progTrimSubtitle" class="subtitle">
                        <?= $objetivo ? 'Indicadores del objetivo seleccionado' : 'Seleccione un objetivo estratégico' ?>
                    </p>
                </div>
            </div>
            <div class="header-actions">
                <a href="<?= Url::to(['obj-estrategico/index']) ?>" class="btn-reporte">← Objetivos</a>
            </div>
        </div>

        <?php if ($objetivo): ?>
            <input type="hidden" id="idObjEstrategico"
                   value="<?= Html::encode($objetivo['IdObjEstrategico']) ?>">

            <div class="toolbar iept-toolbar">
                <div class="iept-obj-card">
                    <div class="mi-render-select2">
                        <div class="titulo-producto">Código: <?= Html::encode($compuesto) ?></div>
                        <div class="titulo-producto"><?= Html::encode($textoObjetivo) ?></div>
                        <?php if ($producto !== ''): ?>
                            <div class="subtitulo-producto"><?= Html::encode($producto) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <button type="button" id="refreshTable" class="btn-refresh" title="Recargar listado">
                    <i class="fa fa-sync-alt fa-spin"></i>
                </button>
            </div>

            <div id="dticTableLoading" class="p-4">
                <div class="table-loading"></div>
                <div class="table-loading"></div>
                <div class="table-loading"></div>
            </div>

            <div class="p-0" id="dticTableContainer" style="display:none;">
                <table id="tablaListaIndicadoresTrimestrales" class="w-100"></table>
            </div>
        <?php else: ?>
            <div id="mensajeInicial" class="programacion-empty-state">
                <i class="fas fa-bullseye"></i>
                <span>
                    Seleccione un objetivo estratégico desde
                    <a href="<?= Url::to(['obj-estrategico/index']) ?>">objetivos estratégicos</a>
                    (botón Programar) para programar sus indicadores.
                </span>
            </div>
        <?php endif; ?>
    </div>
</div>
