<?php

use app\modules\Planificacion\assets\PlanificacionAsset;
use app\modules\Planificacion\common\helpers\PoaEdicionHelper;
use yii\web\JqueryAsset;

PlanificacionAsset::register($this);
PoaEdicionHelper::registrarFlagJs();
$edicion = PoaEdicionHelper::accionesUi();
$cssPath = Yii::getAlias('@app/modules/Planificacion/css/operacion/style.css');
$cssVersion = is_file($cssPath) ? filemtime($cssPath) : time();
$this->registerCssFile(
    '@planificacionModule/css/operacion/style.css?v=' . $cssVersion,
    ['depends' => [PlanificacionAsset::class]]
);
foreach (['s2-declaration.js', 'dt-declaration.js', 'index.js'] as $archivoJs) {
    $rutaJs = Yii::getAlias("@app/modules/Planificacion/js/operacion/{$archivoJs}");
    $versionJs = is_file($rutaJs) ? filemtime($rutaJs) : time();
    $this->registerJsFile(
        "@planificacionModule/js/operacion/{$archivoJs}?v={$versionJs}",
        ['depends' => [JqueryAsset::class]]
    );
}

$this->title = 'Planificación Institucional';
$this->params['subtitle'] = 'Operaciones POA';
$this->params['icon'] = 'fas fa-tasks';
$this->params['iconColor'] = 'primary';
$this->params['actions'] = $edicion['puedeCrear'] ? '
    <button id="btnMostrarCrear" class="btn-crear closed">
        <span class="circle">
            <span class="horizontal"></span>
            <span class="vertical"></span>
        </span>
        <span class="btn-text">Nueva operación</span>
    </button>' : '';
$this->params['breadcrumbs'][] = ['label' => '/ Operaciones POA'];
?>

<div class="card">
    <div id="divDatos" class="card-body" style="display:none">
        <div class="col d-flex justify-content-center">
            <div class="card-dtic-form" style="width: 120rem;">
                <div class="card-header card-dtic-form-header" id="tituloFormulario">Nueva operación POA</div>
                <div class="card-body card-dtic-form-body">
                    <form id="formOperacion" autocomplete="off">
                        <input type="hidden" id="idIndicador" name="idIndicador">

                        <div class="form-group">
                            <label for="idLlavePresupuestaria">Llave presupuestaria</label>
                            <select id="idLlavePresupuestaria" name="idLlavePresupuestaria"
                                    class="form-control dtic-input" style="width:100%"></select>
                            <small class="form-text text-muted">
                                Se listan las llaves con programación anual de indicadores para la unidad y gestión activas.
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="idIndicadorEstrategico">Indicador estratégico</label>
                            <select id="idIndicadorEstrategico" name="idIndicadorEstrategico"
                                    class="form-control dtic-input" style="width:100%"></select>
                        </div>

                        <div class="form-group">
                            <label for="idIndicadorPoa">Indicador POA</label>
                            <select id="idIndicadorPoa" name="idIndicadorPoa"
                                    class="form-control dtic-input" style="width:100%"></select>
                            <small class="form-text text-muted">
                                Un mismo indicador puede usarse en varias operaciones. Elija un indicador estratégico o uno POA.
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="idObjEspecifico">Objetivo específico</label>
                            <select id="idObjEspecifico" name="idObjEspecifico"
                                    class="form-control dtic-input" style="width:100%"></select>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="codigo">Código</label>
                                    <input id="codigo" name="codigo" type="text" maxlength="2"
                                           inputmode="numeric" class="form-control dtic-input" placeholder="01">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="tipoOperacion">Tipo de operación</label>
                                    <select id="tipoOperacion" name="tipoOperacion" class="form-control dtic-input">
                                        <option value="Funcionamiento">Funcionamiento</option>
                                        <option value="Inversion">Inversión</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="descripcion">Descripción de la operación</label>
                            <textarea id="descripcion" name="descripcion" rows="4"
                                      maxlength="300" class="form-control dtic-input"></textarea>
                        </div>
                    </form>
                </div>
                <div class="card-footer card-dtic-form-footer">
                    <button id="btnGuardar" type="button" class="btn-guardar">
                        <i class="fa fa-check-circle"></i>
                        <span class="btn_text">Guardar</span>
                    </button>
                    <button id="btnCancelar" type="button" class="btn-cancel btn-cancelar">
                        <i class="fa fa-times-circle"></i>
                        <span class="btn_text">Cancelar</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="divTabla" class="card-body">
        <div class="card-dtic-style">
            <div class="card-dtic-style-header">
                <div class="card-dtic-style-title">Operaciones POA de la unidad ejecutora</div>
            </div>
            <div id="dticTableLoading" class="p-4">
                <div class="table-loading"></div>
                <div class="table-loading"></div>
                <div class="table-loading"></div>
            </div>
            <div id="dticTableContainer" class="p-2" style="display:none">
                <div class="table-responsive">
                    <table id="tablaOperaciones" class="table w-100 dtic-table"></table>
                </div>
            </div>
        </div>
    </div>
</div>
