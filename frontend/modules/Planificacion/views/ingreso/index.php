<?php

use app\modules\Planificacion\assets\PlanificacionAsset;
use yii\web\JqueryAsset;

PlanificacionAsset::register($this);
$cssPath = Yii::getAlias('@app/modules/Planificacion/css/ingreso/style.css');
$version = is_file($cssPath) ? filemtime($cssPath) : time();
$this->registerCssFile('@planificacionModule/css/ingreso/style.css?v=' . $version, [
    'depends' => [PlanificacionAsset::class],
]);
$this->registerJsFile('@planificacionModule/js/ingreso/dt-declaration.js', ['depends' => [JqueryAsset::class]]);
$this->registerJsFile('@planificacionModule/js/ingreso/index.js', ['depends' => [JqueryAsset::class]]);

$this->title = 'Planificación Institucional';
$this->params['subtitle'] = 'Registro de ingresos';
$this->params['icon'] = 'fas fa-coins';
$this->params['iconColor'] = 'success';
$this->params['actions'] = '
    <button id="btnMostrarCrear" class="btn-crear closed">
        <i class="fas fa-plus-circle"></i>
        <span class="btn-text">Registrar ingreso</span>
    </button>';
?>

<div class="resumen-financiero mb-3">
    <div class="resumen-card ingreso"><span>Total ingresos</span><b id="totalIngresos">0</b></div>
    <div class="resumen-card techo"><span>Techos asignados</span><b id="totalTechos">0</b></div>
    <div class="resumen-card disponible"><span>Diferencia</span><b id="diferenciaIngresos">0</b></div>
</div>

<div class="card">
    <div id="divDatos" class="card-body" style="display:none">
        <form id="formIngreso" autocomplete="off">
            <div class="card-dtic-style">
                <div class="card-dtic-style-header">
                    <div id="tituloFormulario" class="card-dtic-style-title">Registrar ingreso</div>
                </div>
                <div class="p-3">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="cantidad">Cantidad</label>
                                <input id="cantidad" name="cantidad" type="number" min="1" step="1"
                                       class="form-control dtic-input">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="precio">Precio</label>
                                <input id="precio" name="precio" type="number" min="0.01" step="0.01"
                                       class="form-control dtic-input">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="descripcion">Descripción</label>
                                <textarea id="descripcion" name="descripcion" rows="3"
                                          maxlength="500" class="form-control dtic-input"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button id="btnCancelar" type="button" class="btn-cancel btn-cancelar mr-2">Cancelar</button>
                        <button id="btnGuardar" type="button" class="btn-guardar">Guardar</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div id="divTabla" class="card-body">
        <div class="card-dtic-style">
            <div class="card-dtic-style-header">
                <div class="card-dtic-style-title">Ingresos registrados</div>
            </div>
            <div id="dticTableLoading" class="p-4">
                <div class="table-loading"></div><div class="table-loading"></div>
            </div>
            <div id="dticTableContainer" class="p-2" style="display:none">
                <table id="tablaIngresos" class="table w-100 dtic-table"></table>
            </div>
        </div>
    </div>
</div>
