<?php

use yii\web\JqueryAsset;
use yii\web\View;

app\modules\Planificacion\assets\PlanificacionAsset::register($this);

if (isset($id)) {
    $this->registerJs("
        window.appConfig = {
            idObj: " . json_encode($id) . ",
        };
    ", View::POS_HEAD);
}

$this->registerJsFile("@planificacionModule/js/programar-indicador/ProgIndicador.js",[
    'depends' => [
        JqueryAsset::class
    ]
]);



$this->title = 'Planificación Institucional';

$this->params['subtitle'] = 'Programacion de indicadores estrategicos';

$this->params['icon'] = 'fa fa-code-branch';

$this->params['iconColor'] = 'info';

$this->params['actions'] =
    '';

$this->params['breadcrumbs'][] = [
    'label' => '/ Obj Estrategico',
    'url' => ['obj-estrategico/index'],
];

$this->params['breadcrumbs'][] = [
    'label' => '/ Programacion'
];

?>
<div class="container-fluid mt-4">

    <div class="card shadow-sm border-0">

        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Planificación por Código</h5>
        </div>

        <div class="card-body">

            <!-- Spinner general -->
            <div id="loaderGeneral" class="text-center py-5">
                <div class="spinner-border text-primary"></div>
                <div class="mt-2">Cargando información...</div>
            </div>

            <!-- Contenido principal -->
            <div id="contenedorPrincipal" style="display:none;"></div>

        </div>

    </div>

</div>


<!-- MODAL -->
<div class="modal fade" id="modalDetalle" tabindex="-1">

    <div class="modal-dialog modal-xl modal-dialog-scrollable">

        <div class="modal-content">

            <div class="modal-header bg-primary text-white">

                <h5 class="modal-title">Detalle Gestión</h5>

                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body" id="contenidoModal">

                <div class="text-center py-5">
                    <div class="spinner-border text-primary"></div>
                    <div class="mt-2">Cargando...</div>
                </div>

            </div>

        </div>

    </div>

</div>


<style>

    .custom-accordion-header{
        background: linear-gradient(135deg,#ffffff,#f8f9fa);
        border: none !important;
        box-shadow: none !important;
        transition: all .25s ease;
    }

    .custom-accordion-header:not(.collapsed){
        background: linear-gradient(135deg,#e8f2ff,#f5faff);
    }

    .custom-accordion-header:hover{
        background: linear-gradient(135deg,#f8fbff,#eef6ff);
    }

    .custom-accordion-header::after{
        margin-left: 15px;
    }

    .accordion-button:focus{
        box-shadow:none;
    }

    .accordion-item{
        border-left: 5px solid #0d6efd;
    }

    .badge{
        font-weight:500;
        letter-spacing:.3px;
    }

</style>
