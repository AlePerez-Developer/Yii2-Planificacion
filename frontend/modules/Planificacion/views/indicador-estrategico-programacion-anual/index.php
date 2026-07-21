<?php

use app\modules\Planificacion\assets\PlanificacionAsset;
use yii\web\JqueryAsset;

PlanificacionAsset::register($this);

$this->registerJsFile("@planificacionModule/js/indicador-estrategico-programacion-anual/programacion.js", [
        'depends' => [JqueryAsset::class]
]);
$this->registerJsFile("@planificacionModule/js/indicador-estrategico-programacion-anual/dt-declaration.js", [
        'depends' => [JqueryAsset::class]
]);
$this->registerJsFile("@planificacionModule/js/indicador-estrategico-programacion-anual/s2-declaration.js", [
        'depends' => [JqueryAsset::class]
]);
$this->registerCssFile('@planificacionModule/css/indicador-estrategico-programacion-anual/style.css', [
        'depends' => [PlanificacionAsset::class]]);

if (isset($idObjEstrategico)) {
    $this->registerJsVar('idObjEstrategico', $idObjEstrategico);
}

$this->title = 'Planificación Institucional';

$this->params['subtitle'] = 'Programacion de indicadores estrategicos';

$this->params['icon'] = 'fas fa-clipboard-list';

$this->params['iconColor'] = 'info';

$this->params['actions'] = '';

$this->params['breadcrumbs'][] = [
        'label' => '/ Objetivos estrategicos',
        'url' => ['obj-estrategico/index']
];

$this->params['breadcrumbs'][] = [
        'label' => '/ Programacion Anual',
];
?>

<div class="card ">
    <div id="divTabla" class="card-body">
        <div class="card-dtic-style">

            <div class="card-dtic-style-header">
                <div class="card-dtic-style-title">Objetivos Estrategicos </div>
                <select id="idObjEstrategico" class="form-control dtic-input"></select>
            </div>

            <div id="dticTableLoading" class="p-4" style="display:none;">
                <div class="table-loading"></div>
                <div class="table-loading"></div>
                <div class="table-loading"></div>
            </div>

            <div id="mensajeInicial" class="programacion-empty-state">
                <i class="fas fa-bullseye"></i>
                <span>Seleccione un objetivo estratégico para mostrar sus indicadores programados.</span>
            </div>

            <div class="p-2" id="dticTableContainer" style="display:none;">
                <table id="tablaListaIndicadores" class="table w-100 dtic-table"></table>
            </div>

        </div>
    </div>
</div>
<label for="idObjEstrategico"></label>

<!-- Modal Fijo en la Vista -->
<div class="modal fade" id="modalLlaves" data-bs-backdrop="static" tabindex="-1" aria-labelledby="modalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content" style="overflow: auto">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalLabel">
                    <i class="fa fa-list-alt"></i> Seleccion de llaves presupuestarias
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <div class="alert alert-light border mb-3">
                    <h3>Listado de llaves presupuestarias</h3>
                </div>

                <input type="hidden" id="modal_Indicador">
                <input type="hidden" id="modal_Gestion">
                <input type="hidden" id="modal_tableIdOriginal">

                <!-- Tabla Detalle -->
                <div class="table-responsive">
                    <table id="tblModalDetalle" class="table table-striped table-bordered w-100">
                        <thead>
                        <tr>
                            <th>Llave</th>
                            <th>Descripcion</th>
                            <th>Acciones</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-guardar" data-bs-dismiss="modal">Cerrar y Actualizar</button>
            </div>
        </div>
    </div>
</div>


<style>


</style>