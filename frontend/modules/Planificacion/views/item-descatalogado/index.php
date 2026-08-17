<?php

use app\modules\Planificacion\assets\PlanificacionAsset;
use yii\web\JqueryAsset;

PlanificacionAsset::register($this);
$this->registerCssFile('@planificacionModule/css/items-poa/style.css', ['depends' => [PlanificacionAsset::class]]);
$this->registerJsFile('@planificacionModule/js/item-descatalogado/index.js', ['depends' => [JqueryAsset::class]]);

$this->title = 'Planificación Institucional';
$this->params['subtitle'] = "Formulario {$formulario} - Ítems descatalogados";
$this->params['icon'] = 'fas fa-clipboard-list';
$this->params['iconColor'] = 'primary';
$this->params['breadcrumbs'][] = ['label' => "/ Formulario {$formulario}"];
?>

<div id="itemsDescatalogadosPage" data-formulario="<?= (int)$formulario ?>">
    <div class="card">
        <div class="card-body">
            <div class="card-dtic-style">
                <div class="card-dtic-style-header">
                    <div class="card-dtic-style-title">Operaciones disponibles</div>
                </div>
                <div class="p-2 table-responsive">
                    <table id="tablaOperacionesItems" class="table w-100 dtic-table"></table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalItems" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title">Ítems descatalogados</h5>
                        <small id="operacionSeleccionada"></small>
                    </div>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="formItemDescatalogado" autocomplete="off">
                        <input type="hidden" id="idItemDescatalogado">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="idGasto">Gasto</label>
                                <select id="idGasto" class="form-control" style="width:100%"></select>
                            </div>
                            <div class="col-md-3 form-group">
                                <label for="idFuente">Fuente</label>
                                <select id="idFuente" class="form-control" style="width:100%"></select>
                            </div>
                            <div class="col-md-3 form-group">
                                <label for="idOrganismo">Organismo</label>
                                <select id="idOrganismo" class="form-control" style="width:100%"></select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label for="cantidad">Cantidad</label>
                                <input id="cantidad" type="number" min="1" step="1" class="form-control">
                            </div>
                            <div class="col-md-4 form-group">
                                <label for="precio">Precio</label>
                                <input id="precio" type="number" min="0.01" step="0.01" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <textarea id="descripcion" maxlength="500" rows="2"
                                      class="form-control" placeholder="Descripción opcional del ítem"></textarea>
                        </div>
                        <div class="text-right">
                            <button id="btnCancelarItem" type="button" class="btn-cancel btn btn-light">Limpiar</button>
                            <button id="btnGuardarItem" type="submit" class="btn btn-primary">Guardar ítem</button>
                        </div>
                    </form>
                    <hr>
                    <div class="table-responsive">
                        <table id="tablaItems" class="table w-100 dtic-table"></table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
