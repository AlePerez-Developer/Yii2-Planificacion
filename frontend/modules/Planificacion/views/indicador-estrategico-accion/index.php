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
                <div class="container">
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

<?php
$cssCode = <<<CSS
    .select2-container .select2-selection--single {
        height: auto !important; /* Permite que crezca según el contenido */
        padding: 4px 8px !important;
    }
    
    .select2-selection__rendered{
        display: flex !important;
        flex-direction: row-reverse;
        align-items: center !important;
        gap: 10px !important;
    }
    
    .select2-selection__clear{
        color: darkred !important ;
        font-weight: bold;
        font-size: 40px !important;
        height: auto !important;
        width: auto !important;
        padding: 5px !important;
        background-color: #FFFFFF!important;
        border: 2px solid red!important;
    }

    /* Centra la flecha lateral del Select2 para que no quede arriba */
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 100% !important;
        top: 0 !important;
        display: flex;
        align-items: center;
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
CSS;

$this->registerCss($cssCode);
?>

<style>

    .kpi-circle {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        background: #64748b;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 14px;
        animation: kpiFade .6s ease;
    }

    .badge-result {
        background: #ffffff; /* <-- CAMBIADO: Fondo limpio */
        color: #61942e; /* <-- CAMBIADO: El texto toma el color verde para resaltar */
        border: 2px solid #8DBE5A; /* <-- CAMBIADO: El verde original pasa a ser el borde */
        border-radius: 20px;
        padding: 6px 16px;
        font-size: 13px;
        font-weight: 600;
        width: 140px;
        text-align: center;
    }


</style>

