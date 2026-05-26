<?php

use yii\web\JqueryAsset;

app\modules\Planificacion\assets\PlanificacionAsset::register($this);

$this->registerJsFile("@planificacionModule/js/indicador-estrategico-programacion/programacion.js",[
    'depends' => [
        JqueryAsset::class
    ]
]);

$this->registerJsFile("@planificacionModule/js/indicador-estrategico-programacion/dt-declaration.js",[
        'depends' => [
                JqueryAsset::class
        ]
]);

if (isset($idObjEstrategico)) {
    $this->registerJsVar('idObjEstrategico', $idObjEstrategico);
}

$this->title = 'Planificación Institucional';

$this->params['subtitle'] = 'Programacion de indicadores estrategicos';

$this->params['icon'] = 'fas fa-clipboard-list';

$this->params['iconColor'] = 'info';

$this->params['actions'] =
    '';


$this->params['breadcrumbs'][] = [
    'label' => '/ Objetivos estrategicos',
    'url' => ['obj-estrategico/index']
];

$this->params['breadcrumbs'][] = [
    'label' => '/ Programacion de indicadores estrategicos',
];
?>
<div id="divTabla" class="card-body">
    <div class="card-dtic-style">

        <div id="dticTableLoading" class="p-4">
            <div class="table-loading"></div>
            <div class="table-loading"></div>
            <div class="table-loading"></div>
        </div>

        <div class="p-2" id="dticTableContainer" style="display:none;">
            <table id="tablaListaIndicadores" class="table w-100 dtic-table"></table>
        </div>

    </div>
</div>



<!-- Modal Fijo en la Vista -->
<div class="modal fade" id="modalProgramacion" data-bs-backdrop="static" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl"> <!-- 'xl' para que la DataTable interna tenga espacio -->
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalLabel">
                    <i class="fa fa-list-alt"></i> Seleccion de llaves presupuestarias
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Información de contexto (opcional) -->
                <div class="alert alert-light border mb-3">
                    <h3>Listado de llaves presupuestarias</h3>
                </div>

                <!-- Inputs ocultos para persistencia -->
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
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar y Actualizar</button>
            </div>
        </div>
    </div>
</div>






<div class="container-fluid mt-3">

    <div id="loaderIndicadores">
        <div class="acc-skeleton"></div>
        <div class="acc-skeleton"></div>
    </div>

    <div id="contenedorAccordion"></div>

</div>

<style>

    .acc-footer {
        display: flex;
        /* justify-content: space-between; <-- ESTA LÍNEA DEBE ELIMINARSE */
        align-items: center; /* <-- CAMBIADO de 'flex-end' a 'center' */
    }


    /* KPI */
    .kpi-circle{
        width:38px;
        height:38px;
        border-radius:12px;
        background:#64748b;
        color:#fff;
        display:flex;
        align-items:center;
        justify-content:center;
        font-weight:800;
        font-size:14px;
        animation:kpiFade .6s ease;
    }

    .meta-box-left {
        display: flex;
        gap: 6px;
        align-items: center; /* Asegura que los textos y círculos estén centrados entre sí */
    }

    .meta-badge{
        background:#2563eb;
        color: #fff;
        border-radius:999px;
        padding:4px 10px;
        font-size:13px;
        font-weight:700;
    }
    .meta-badge-text{
        font-weight: bold;
        color: black;
    }

    /* RESULTADOS */
    /* BLOQUE DE RESULTADOS (Derecha) - MODIFICADO */
    .result-box {
        display: flex;
        flex-direction: column;
        align-items: flex-end; /* Mantiene el texto de los botones alineado a la derecha */
        gap: 4px;
        margin-left: auto; /* <-- ESTA LÍNEA ES CLAVE: empuja este bloque a la derecha */
    }

    .result-top{
        display:flex;
        gap:4px;
    }

    .badge-result {
        background: #ffffff; /* <-- CAMBIADO: Fondo limpio */
        color: #61942e; /* <-- CAMBIADO: El texto toma el color verde para resaltar */
        border: 1.5px solid #8DBE5A; /* <-- CAMBIADO: El verde original pasa a ser el borde */
        border-radius: 20px;
        padding: 6px 16px;
        font-size: 13px;
        font-weight: 600;
        width: 140px;
        text-align: center;
    }

    .dtic-item-sub{
        display:flex;
        align-items:center;
        gap:8px;
        flex-wrap:wrap;
    }

    /* Quita el padding de la celda que contiene el hijo para que el slide sea fluido */
    td.no-padding {
        padding: 0 !important;
    }
    div.slider {
        overflow: hidden;
    }

    /* ===== TABS ===== */

    .tabs-nav{
        display:flex;
        gap:6px;
        margin-bottom:10px;
    }

    .tab-btn{
        flex:1;
        padding:10px;
        border:none;
        border-radius:10px;
        background:#f1f5f9; !important;
        font-weight:700;
    }

    .tab-btn.active{
        background:#2563eb; !important;
        color:#fff; !important;
    }

    .nav-tabs .nav-link.active {
        background:#2563eb; !important;
        color:#fff; !important;
    }

    /* ===== TAB CONTENT ===== */

    .tab-pane{ display:none; }
    .tab-pane.active{ display:block; }

    /* ===== TABLE ===== */

    .table-container{
        border-radius:12px;
        overflow:hidden;
        box-shadow:0 4px 12px rgba(0,0,0,.05);
    }

    .table-scroll{
        max-height:260px;
        overflow:auto;
    }

    .inputMeta,
    .inputMetaNueva{
        text-align:center;
        font-weight:700;
    }

    /* ===== NUEVA FILA ===== */

    .fila-nueva{
        background:#fff7ed;
    }


    /* ========  input  =========*/
    /* Estado normal (parece texto simple) */
    .input-editable-smart {
        transition: all 0.2s ease;
        font-weight: bold;
        outline: none;
        box-shadow: none !important;
    }

    /* Cuando se le quita el readonly (se activa) */
    .input-editable-smart:not([readonly]) {
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
    }

    /* Animación de éxito */
    .input-editable-smart.is-valid {
        background-color: #d4edda !important;
        transition: background-color 0.5s;
    }



</style>
