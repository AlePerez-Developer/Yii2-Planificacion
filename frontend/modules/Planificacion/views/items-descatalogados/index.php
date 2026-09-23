<?php

use app\modules\Planificacion\assets\PlanificacionAsset;
use app\modules\Planificacion\common\helpers\PoaEdicionHelper;
use yii\helpers\Url;
use yii\web\JqueryAsset;

PlanificacionAsset::register($this);
$edicion = PoaEdicionHelper::accionesUi();
PoaEdicionHelper::registrarFlagJs();
$this->registerCssFile('@planificacionModule/css/items-catalogados/style.css', ['depends' => [PlanificacionAsset::class]]);
$this->registerJsFile('@planificacionModule/js/items-descatalogados/s2-declaration.js', ['depends' => [JqueryAsset::class]]);
$this->registerJsFile('@planificacionModule/js/items-descatalogados/dt-declaration.js', ['depends' => [JqueryAsset::class]]);
$this->registerJsFile('@planificacionModule/js/items-descatalogados/index.js', ['depends' => [JqueryAsset::class]]);

$this->title = 'Planificación Institucional';
$this->params['subtitle'] = "Formulario {$formulario} - Ítems descatalogados";
$this->params['icon'] = 'fas fa-box-open';
$this->params['iconColor'] = 'primary';
$acciones = '';
if ($edicion['puedeCrear']) {
    $acciones .= '
    <button id="btnMostrarCrear" class="btn-crear closed">
        <span class="circle">
            <span class="horizontal"></span>
            <span class="vertical"></span>
        </span>
        <span class="btn-text">Nuevo ítem</span>
    </button>';
}
$acciones .= '
    <button id="btnReportePdf" class="btn-reporte">
        <i class="fas fa-file-pdf"></i>
        <span class="btn-text">Exportar</span>
    </button>';
$this->params['actions'] = $acciones;
$this->params['breadcrumbs'][] = ['label' => "/ Formulario {$formulario}"];
?>

<div id="itemsDescatalogadosPage" data-formulario="<?= (int)$formulario ?>"
     data-reporte="<?= Url::to(['items-descatalogados/reporte', 'formulario' => $formulario]) ?>">
    <div class="card">
        <div id="divDatos" class="card-body" style="display:none">
            <div class="col d-flex justify-content-center">
                <div class="card-dtic-form" style="width: 120rem;">
                    <div class="card-header card-dtic-form-header">Ingreso de ítem descatalogado</div>
                    <div class="card-body card-dtic-form-body">
                        <form id="formItemDescatalogado" autocomplete="off">
                            <input type="hidden" id="idItem" name="idItem">

                            <div class="form-group">
                                <label for="idGasto">Gasto</label>
                                <select id="idGasto" name="idGasto" class="form-control dtic-input"
                                        style="width:100%"></select>
                            </div>
                            <div id="detalleGasto" class="sigma-preview mb-3" style="display:none"></div>

                            <div class="form-group">
                                <label for="descripcion">Descripción</label>
                                <textarea id="descripcion" name="descripcion" rows="3" maxlength="500"
                                          class="form-control dtic-input"></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="idFuente">Fuente</label>
                                    <select id="idFuente" name="idFuente" class="form-control dtic-input"
                                            style="width:100%"></select>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="idOrganismo">Organismo</label>
                                    <select id="idOrganismo" name="idOrganismo" class="form-control dtic-input"
                                            style="width:100%"></select>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="idFuenteUniversitaria">Fuente universitaria</label>
                                    <select id="idFuenteUniversitaria" name="idFuenteUniversitaria"
                                            class="form-control dtic-input" style="width:100%"></select>
                                </div>
                                <div class="col-md-3 form-group">
                                    <label for="idUnidadMedida">Unidad de medida</label>
                                    <select id="idUnidadMedida" name="idUnidadMedida" class="form-control dtic-input"
                                            style="width:100%"></select>
                                </div>
                                <div class="col-md-3 form-group">
                                    <label for="precio">Precio</label>
                                    <input id="precio" name="precio" type="number" min="0.01" step="0.01"
                                           class="form-control dtic-input">
                                </div>
                            </div>

                            <h6 class="mt-3 mb-2">Asignación a operaciones</h6>
                            <div id="accordionOperaciones"></div>
                        </form>
                    </div>
                    <div class="card-footer card-dtic-form-footer">
                        <button id="btnGuardar" type="button" class="btn-guardar">
                            <i class="fa fa-check-circle"></i><span class="btn_text">Guardar</span>
                        </button>
                        <button id="btnCancelar" type="button" class="btn-cancel">
                            <i class="fa fa-times-circle"></i> Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div id="divTabla" class="card-body">
            <div class="card-dtic-style">
                <div class="card-dtic-style-header">
                    <div class="card-dtic-style-title">Ítems del formulario <?= (int)$formulario ?></div>
                    <div id="totalFormularioBox" class="total-formulario">
                        Total: <strong id="totalFormulario">0.00</strong>
                    </div>
                </div>
                <div id="dticTableLoading" class="p-4">
                    <div class="table-loading"></div>
                    <div class="table-loading"></div>
                    <div class="table-loading"></div>
                </div>
                <div id="dticTableContainer" class="p-2" style="display:none">
                    <table id="tablaListaItemsDescatalogados" class="table w-100 dtic-table"></table>
                </div>
            </div>
        </div>
    </div>
</div>
