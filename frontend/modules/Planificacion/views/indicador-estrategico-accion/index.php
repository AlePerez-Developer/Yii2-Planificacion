<?php

use yii\web\JqueryAsset;
use app\modules\Planificacion\assets\PlanificacionAsset;

PlanificacionAsset::register($this);

$this->registerJsFile("@planificacionModule/js/indicador-estrategico-accion/dt-declaration.js", [
        'depends' => [
                JqueryAsset::class
        ]
]);

$this->registerJsFile("@planificacionModule/js/indicador-estrategico-accion/s2-declaration.js", [
        'depends' => [
                JqueryAsset::class
        ]
]);

$this->registerJsFile("@planificacionModule/js/indicador-estrategico-accion/acciones.js", [
        'depends' => [
                JqueryAsset::class
        ]
]);

$this->registerCssFile("@planificacionModule/css/indicador-estrategico-accion/style.css", [
        'depends' => [
                PlanificacionAsset::class
        ]
]);


$this->title = 'Planificación Institucional';

$this->params['subtitle'] = 'Asignacion de acciones estrategicas';

$this->params['icon'] = 'fas fa-clipboard-list';

$this->params['iconColor'] = 'info';

$this->params['actions'] = '';

$this->params['breadcrumbs'][] = [
        'label' => '/ Ind. Estrategicos'
];
?>
<div class="card ">

    <div class="card-body">
        <div class="card-dtic-style">
            <div class="card-dtic-style-header">
                <div class="card-dtic-style-title">
                    Objetivos Estrategicos
                </div>
                <div class="">
                    <div class="row">
                        <div class="col">
                            <select id="idObjEstrategico" class="form-control dtic-input"></select>
                        </div>
                    </div>
                </div>

            </div>
            <div id="dticTableLoading" class="p-4">
                <div class="table-loading"></div>
                <div class="table-loading"></div>
                <div class="table-loading"></div>
            </div>

            <div class="p-2" id="dticTableContainer" style="display:none;">
                <table id="tablaListaIndicadoresEstrategicosAccion" class="table w-100 dtic-table"></table>
            </div>
            <label for="idObjEstrategico"></label>
        </div>
    </div>

</div>


<!-- Modal Fijo en la Vista -->
<div class="modal fade" id="modalAsignacion" data-bs-backdrop="static" tabindex="-1" aria-labelledby="modalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="overflow: hidden">
            <div class="modal-header bg-primary text-white">
                <div class="modal-header-title">
                    <div class="page-icon bg-info specific">
                        <i class="fa fa-list-alt"></i>
                    </div>
                    <h5 class="modal-title" id="modalLabel">
                        Asignacion de accion estrategica
                    </h5>
                </div>

                <button type="button" class="btn-remove" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>

            </div>
            <div class="modal-body">

                <div class="card ">

                    <div id="divTabla" class="card-body">
                        <div class="card-dtic-style specific">

                            <div class="card-dtic-style-header">
                                <div class="card-dtic-style-title">
                                    Listado de acciones estrategicas
                                </div>
                            </div>

                            <div class="p-2" id="dticTableContainer">
                                <form id="formAsignacionAccion" action="" method="post">
                                    <div class="row" style="display: flex; align-items: center">
                                        <div class="col-8">
                                            <div class="form-group">
                                                <label for="idAccionEstrategica">Seleccione la Accion estrategica</label>
                                                <select class="form-control dtic-input" id="idAccionEstrategica"
                                                        name="idAccionEstrategica">
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="form-group">
                                                <label for="accionDescripcion">Descripcion</label>
                                                <input type="text" class="form-control input-sm txt dtic-input"
                                                       id="accionDescripcion"
                                                       name="accionDescripcion" placeholder="Descripcion de la accion">
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-guardar  guardar" >Cerrar y
                    Actualizar
                </button>
            </div>
        </div>
    </div>
</div>
