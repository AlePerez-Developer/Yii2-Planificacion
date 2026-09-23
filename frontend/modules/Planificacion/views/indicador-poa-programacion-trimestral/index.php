<?php

use app\modules\Planificacion\assets\PlanificacionAsset;
use app\modules\Planificacion\common\helpers\PoaEdicionHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JqueryAsset;

/** @var array|null $objetivo */

PlanificacionAsset::register($this);
PoaEdicionHelper::registrarFlagJs();
$edicion = PoaEdicionHelper::accionesUi();
$cssPath = Yii::getAlias('@app/modules/Planificacion/css/indicador-poa-programacion-trimestral/style.css');
$cssVersion = is_file($cssPath) ? filemtime($cssPath) : time();
$this->registerCssFile('@planificacionModule/css/programacion-indicador/style.css', [
    'depends' => [PlanificacionAsset::class],
]);
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
$this->params['breadcrumbs'][] = [
    'label' => '/ Objetivos específicos',
    'url' => ['obj-especifico/index'],
];
$this->params['breadcrumbs'][] = ['label' => '/ Programación trimestral de indicadores POA'];
?>

<div class="card">
    <div class="card-body">
        <div class="card-dtic-style">
            <?php if ($objetivo): ?>
                <input type="hidden" id="idObjEspecifico"
                       value="<?= Html::encode($objetivo['IdObjEspecifico']) ?>">

                <div class="objetivo-programacion-card">
                    <div class="dtic-code-container">
                        <span class="dtic-code-text">Objetivo:</span>
                        <div class="dtic-code-badge"><?= Html::encode($objetivo['Compuesto']) ?></div>
                    </div>
                    <div class="dtic-item-main"><?= Html::encode($objetivo['Objetivo']) ?></div>
                    <div class="dtic-item-sub2 group-container">
                        <div class="sub-group-container">
                            <div class="item-container">
                                <div>Objetivo institucional</div>
                            </div>
                            <div><?= Html::encode($objetivo['objetivosInstitucionales']['Objetivo'] ?? '') ?></div>
                        </div>
                        <div class="sub-group-container">
                            <div class="item-container">
                                <div>Producto</div>
                            </div>
                            <div><?= Html::encode($objetivo['objetivosInstitucionales']['Producto'] ?? '') ?></div>
                        </div>
                    </div>
                </div>

                <div class="programacion-toolbar">
                    <?php if ($edicion['puedeCrear']): ?>
                    <button id="btnAgregarIndicador" type="button" class="btn-crear">
                        <i class="fas fa-plus-circle"></i>
                        <span class="btn-text">Agregar indicador</span>
                    </button>
                    <?php endif; ?>
                </div>

                <div id="dticTableLoading" class="p-4" style="display:none">
                    <div class="table-loading"></div>
                    <div class="table-loading"></div>
                    <div class="table-loading"></div>
                </div>
                <div id="dticTableContainer" class="p-2" style="display:none">
                    <table id="tablaListaIndicadoresPoaTrimestrales" class="table w-100 dtic-table"></table>
                </div>
            <?php else: ?>
                <div id="mensajeInicial" class="programacion-empty-state">
                    <i class="fas fa-bullseye"></i>
                    <span>
                        Seleccione un objetivo específico desde
                        <a href="<?= Url::to(['obj-especifico/index']) ?>">objetivos específicos</a>
                        para programar sus indicadores POA.
                    </span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="modalRelacionPoa" class="modal fade" tabindex="-1" role="dialog"
     aria-labelledby="tituloModalRelacionPoa" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="tituloModalRelacionPoa" class="modal-title">Agregar indicador POA</h5>
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
