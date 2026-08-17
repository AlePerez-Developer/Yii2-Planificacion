<?php

use app\modules\Planificacion\assets\PlanificacionAsset;
use yii\web\JqueryAsset;

PlanificacionAsset::register($this);
$this->registerJsFile('@planificacionModule/js/operacion-dtic/s2-declaration.js', ['depends' => [JqueryAsset::class]]);
$this->registerJsFile('@planificacionModule/js/operacion-dtic/dt-declaration.js', ['depends' => [JqueryAsset::class]]);
$this->registerJsFile('@planificacionModule/js/operacion-dtic/index.js', ['depends' => [JqueryAsset::class]]);
$this->registerJsFile('@planificacionModule/js/operacion-dtic/items-asignacion.js', ['depends' => [JqueryAsset::class]]);
$this->registerCssFile('@planificacionModule/css/operacion-dtic/style.css', ['depends' => [PlanificacionAsset::class]]);

$this->title = 'Planificación Institucional';
$this->params['subtitle'] = 'Administración de operaciones';
$this->params['icon'] = 'fas fa-tasks';
$this->params['iconColor'] = 'info';
$this->params['actions'] = '
    <button id="btnMostrarCrear" class="btn-crear closed">
        <span class="circle">
            <span class="horizontal"></span>
            <span class="vertical"></span>
        </span>
        <span class="btn-text">Nuevo Registro</span>
    </button>';
$this->params['breadcrumbs'][] = ['label' => '/ Operaciones'];
?>

<div class="card">
    <div id="divDatos" class="card-body" style="display:none">
        <div class="col d-flex justify-content-center">
            <div class="card-dtic-form w-100">
                <div class="card-header card-dtic-form-header">Ingreso de datos</div>
                <div class="card-body card-dtic-form-body">
                    <form id="formOperacionDtic" autocomplete="off">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="idObjEspecifico">Objetivo específico</label>
                                    <select id="idObjEspecifico" name="idObjEspecifico"
                                            class="form-control dtic-input"></select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="idIndicadorEstrategico">Indicador estratégico (opcional)</label>
                                    <select id="idIndicadorEstrategico" name="idIndicadorEstrategico"
                                            class="form-control dtic-input"></select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="idIndicadorPoa">Indicador POA (opcional)</label>
                                    <select id="idIndicadorPoa" name="idIndicadorPoa"
                                            class="form-control dtic-input"></select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="codigo">Código</label>
                                    <input id="codigo" name="codigo" type="text" maxlength="2"
                                           inputmode="numeric" class="form-control dtic-input"
                                           placeholder="01">
                                </div>
                            </div>
                            <div class="col-md-10">
                                <div class="form-group">
                                    <label for="descripcion">Descripción</label>
                                    <textarea id="descripcion" name="descripcion" rows="3" maxlength="500"
                                              class="form-control dtic-input"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <?php foreach ([
                                'primerTrimestre' => 'Primer trimestre',
                                'segundoTrimestre' => 'Segundo trimestre',
                                'tercerTrimestre' => 'Tercer trimestre',
                                'cuartoTrimestre' => 'Cuarto trimestre',
                            ] as $campo => $etiqueta): ?>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="<?= $campo ?>"><?= $etiqueta ?></label>
                                        <input id="<?= $campo ?>" name="<?= $campo ?>" type="number" min="0"
                                               value="0" class="form-control dtic-input">
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </form>
                </div>

                <div class="card-footer card-dtic-form-footer">
                    <button id="btnGuardar" class="btn-guardar">
                        <i class="fa fa-check-circle"></i>
                        <span class="btn_text">Guardar</span>
                    </button>
                    <button id="btnCancelar" class="btn-cancel">
                        <span class="fa fa-times-circle"></span> Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="divTabla" class="card-body">
        <div class="card-dtic-style">
            <div class="card-dtic-style-header">
                <div class="card-dtic-style-title">Listado de operaciones</div>
            </div>
            <div id="dticTableLoading" class="p-4">
                <div class="table-loading"></div>
                <div class="table-loading"></div>
                <div class="table-loading"></div>
            </div>
            <div id="dticTableContainer" class="p-2" style="display:none">
                <table id="tablaOperacionesDtic" class="table w-100 dtic-table"></table>
            </div>
        </div>
    </div>
</div>

<div id="modalAsignarItems" class="modal fade" tabindex="-1" role="dialog"
     aria-labelledby="tituloModalAsignarItems" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 id="tituloModalAsignarItems" class="modal-title">Ítems de la operación</h5>
                    <small id="descripcionOperacionItems" class="text-muted"></small>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="itemsTableLoading" class="p-4">
                    <div class="table-loading"></div>
                    <div class="table-loading"></div>
                </div>
                <div id="itemsTableContainer" style="display:none">
                    <table id="tablaItemsOperacion" class="table w-100 dtic-table"></table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
