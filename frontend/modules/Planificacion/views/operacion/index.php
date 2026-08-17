<?php

use app\modules\Planificacion\assets\PlanificacionAsset;
use yii\web\JqueryAsset;

PlanificacionAsset::register($this);
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
$this->params['actions'] = '
    <button id="btnMostrarCrear" class="btn-crear closed">
        <i class="fas fa-plus-circle"></i>
        <span class="btn-text">Nueva operación</span>
    </button>';
$this->params['breadcrumbs'][] = ['label' => '/ Operaciones POA'];
?>

<div class="card">
    <div id="divDatos" class="card-body" style="display:none">
        <form id="formOperacion" autocomplete="off">
            <div class="card-dtic-style">
                <div class="card-dtic-style-header">
                    <div id="tituloFormulario" class="card-dtic-style-title">Nueva operación POA</div>
                </div>
                <div class="p-3">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="idObjEspecifico">Objetivo específico</label>
                                <select id="idObjEspecifico" name="idObjEspecifico"
                                        class="form-control dtic-input" style="width:100%"></select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="idLlavePresupuestaria">Llave presupuestaria</label>
                                <select id="idLlavePresupuestaria" name="idLlavePresupuestaria"
                                        class="form-control dtic-input" style="width:100%"></select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="idIndicador">Indicador programado anualmente</label>
                                <select id="idIndicador" name="idIndicador"
                                        class="form-control dtic-input" style="width:100%"></select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="codigo">Código</label>
                                <input id="codigo" name="codigo" type="text" maxlength="2"
                                       class="form-control dtic-input" placeholder="00">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-9">
                            <div class="form-group">
                                <label for="descripcion">Descripción</label>
                                <textarea id="descripcion" name="descripcion" rows="3"
                                          maxlength="300" class="form-control dtic-input"></textarea>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="tipoOperacion">Tipo de operación</label>
                                <select id="tipoOperacion" name="tipoOperacion"
                                        class="form-control dtic-input">
                                    <option value="Funcionamiento">Funcionamiento</option>
                                    <option value="Inversion">Inversión</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button id="btnCancelar" type="button" class="btn-cancel btn-cancelar mr-2">
                            <i class="fa fa-times-circle"></i>
                            <span class="btn_text">Cancelar</span>
                        </button>
                        <button id="btnGuardar" type="button" class="btn-guardar">
                            <i class="fa fa-check-circle"></i>
                            <span class="btn_text">Guardar</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div id="divTabla" class="card-body">
        <div class="card-dtic-style">
            <div class="card-dtic-style-header">
                <div class="card-dtic-style-title">Operaciones POA registradas</div>
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
