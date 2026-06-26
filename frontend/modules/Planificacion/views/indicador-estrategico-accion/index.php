<?php

use yii\web\JqueryAsset;

app\modules\Planificacion\assets\PlanificacionAsset::register($this);

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


$this->title = 'Planificación Institucional';

$this->params['subtitle'] = 'Asignacion de acciones estrategicas';

$this->params['icon'] = 'fas fa-clipboard-list';

$this->params['iconColor'] = 'info';

$this->params['actions'] =
    '<button id="btnMostrarCrear"  class="btn-crear closed" >
              <span class="circle">
                <span class="horizontal"></span>
                <span class="vertical"></span>
              </span>
              <span class="btn-text">Nuevo Registro</span>
        </button>

         <button id="btnReportePdf" class="btn-reporte">
            <i class="fas fa-file-pdf"></i>
             <span class="btn-text">Exportar</span>
         </button>';

$this->params['breadcrumbs'][] = [
    'label' => '/ Ind. Estrategicos'
];
?>
<div class="card ">

    <div  class="card-body">
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

    <div  class="card-body">
        <div class="card-dtic-style">
            <div class="card-dtic-style-header">
                <div class="card-dtic-style-title">
                    Indicadores Estratégicos Institucionales
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Modal Fijo en la Vista -->
<div class="modal fade" id="modalProgramacion" data-bs-backdrop="static" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content" style="overflow: auto">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalLabel">
                    <i class="fa fa-list-alt"></i> Seleccion de llaves presupuestarias
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <div class="alert alert-light border mb-3">
                    <h3>Listado de llaves presupuestarias</h3>
                </div>

                <input type="hidden" id="modal_idIndicador">
                <input type="hidden" id="modal_idGestion">
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
                <button type="button" class="btn btn-outline-info" data-bs-dismiss="modal">Cerrar y Actualizar</button>
            </div>
        </div>
    </div>
</div>

<?php
$cssCode = <<<CSS

table.dataTable thead {
    display: none;
}


/*******************Select2 css***********************/
span .select2-selection.select2-selection--single{
    height: auto !important;
    border-radius: 8px !important;
    padding: 4px 8px !important;
}

.select2-selection__rendered{
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
    height: auto !important;
}

.mi-render-select2{
    display: flex;
    flex-direction: column;
    line-height: 1.2;
    padding: 2px 0;
    gap: 5px;
    flex: 1;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: normal !important;
    padding-left: 0 !important;
}

.select2-results__option{
    border-bottom: 1px dotted darkgrey;
}

.select2-results__options {
    max-height: 390px !important; /* Aumenta o disminuye este valor */
}

/****************select2 datos css************************/

.titulo-producto{
    font-weight: bold;
    color: #333;
}

.subtitulo-producto{
    font-weight: normal;
    color: #333;
}

/************************Tabla css***********************/
.dtic-item-main{
    font-size: 14px !important;
}

.badge-result {
    background: #ffffff; /* <-- CAMBIADO: Fondo limpio */
    color: #61942e; /* <-- CAMBIADO: El texto toma el color verde para resaltar */
    border: 2px solid #8DBE5A; /* <-- CAMBIADO: El verde original pasa a ser el borde */
    border-radius: 20px;
    padding: 6px 16px;
    font-size: 10px;
    font-weight: 550;
    width: 110px;
    text-align: center;
}
CSS;

$this->registerCss($cssCode);
?>

