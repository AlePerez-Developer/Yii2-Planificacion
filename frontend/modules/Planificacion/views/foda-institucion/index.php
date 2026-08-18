<?php

use app\modules\Planificacion\assets\PlanificacionAsset;
use app\modules\Planificacion\models\FODAInstitucion;
use yii\web\JqueryAsset;

PlanificacionAsset::register($this);
$cssPath = Yii::getAlias('@app/modules/Planificacion/css/foda-institucion/style.css');
$version = is_file($cssPath) ? filemtime($cssPath) : time();
$this->registerCssFile('@planificacionModule/css/foda-institucion/style.css?v=' . $version, [
    'depends' => [PlanificacionAsset::class],
]);
$this->registerJsFile('@planificacionModule/js/foda-institucion/dt-declaration.js', ['depends' => [JqueryAsset::class]]);
$this->registerJsFile('@planificacionModule/js/foda-institucion/index.js', ['depends' => [JqueryAsset::class]]);

$this->title = 'Planificación Institucional';
$this->params['subtitle'] = 'FODA Institución';
$this->params['icon'] = 'fas fa-university';
$this->params['iconColor'] = 'info';
$this->params['actions'] = '
    <button id="btnMostrarCrear" class="btn-crear closed">
        <i class="fas fa-plus-circle"></i>
        <span class="btn-text">Nuevo registro</span>
    </button>

     <button id="btnReportePdf" class="btn-reporte">
        <i class="fas fa-file-pdf"></i>
         <span class="btn-text">Exportar</span>
     </button>';
?>

<div class="card">
    <div id="divDatos" class="card-body" style="display:none">
        <form id="formFodaInstitucion" autocomplete="off">
            <div class="card-dtic-style">
                <div class="card-dtic-style-header">
                    <div id="tituloFormulario" class="card-dtic-style-title">Registrar FODA</div>
                </div>
                <div class="p-3">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="tipo">Tipo</label>
                                <select id="tipo" name="tipo" class="form-control dtic-input">
                                    <option value="">Seleccione un tipo</option>
                                    <?php foreach (FODAInstitucion::tipos() as $tipo): ?>
                                        <option value="<?= $tipo ?>"><?= $tipo ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="descripcion">Descripción</label>
                                <textarea id="descripcion" name="descripcion" rows="4"
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
                <div class="card-dtic-style-title">Registros FODA</div>
            </div>
            <div id="dticTableLoading" class="p-4">
                <div class="table-loading"></div><div class="table-loading"></div>
            </div>
            <div id="dticTableContainer" class="p-2" style="display:none">
                <table id="tablaFodaInstitucion" class="table w-100 dtic-table"></table>
            </div>
        </div>
    </div>
</div>
