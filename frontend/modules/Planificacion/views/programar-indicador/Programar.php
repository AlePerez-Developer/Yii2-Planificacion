<?php

use yii\web\JqueryAsset;

app\modules\Planificacion\assets\PlanificacionAsset::register($this);

$this->title = 'Programar Indicadores';

$id = Yii::$app->request->get('id');

/* PASAR VARIABLE GLOBAL */
$this->registerJs("
    window.appConfig = {
        idObj: " . json_encode($id) . "
    };
", \yii\web\View::POS_HEAD);

/* REGISTRAR JS */
$this->registerJsFile("@planificacionModule/js/programar-indicador/ProgIndicador.js", [
    'depends' => [JqueryAsset::class]
]);
?>

<div class="container-fluid mt-3">
    <div id="contenedor"></div>
</div>

<!-- MODAL -->
<div class="modal fade" id="modalLlaves">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5>Seleccionar Llave Presupuestaria</h5>
            </div>

            <div class="modal-body">
                <table id="tblLlaves" class="table table-sm table-hover"></table>
            </div>

        </div>
    </div>
</div>

<style>

    /* ===============================
       TABS
    =============================== */
    .tabs-pei{
        border-bottom:2px solid #dee2e6;
    }

    .tabs-pei .nav-link{
        border:none;
        border-radius:10px 10px 0 0;
        background:#f8f9fa;
        margin-right:5px;
    }

    .tabs-pei .nav-link.active{
        background:#0d6efd;
        color:white;
    }

    /* ===============================
       TAB CONTENT
    =============================== */
    .tab-box{
        border:1px solid #dee2e6;
        border-top:0;
        padding:15px;
        border-radius:0 0 10px 10px;
    }

    /* ===============================
       TABLE STYLE
    =============================== */
    .dataTables_wrapper{
        background:#fff;
        border-radius:12px;
        padding:10px;
        box-shadow:0 4px 12px rgba(0,0,0,.05);
    }

    .table thead{
        background:linear-gradient(135deg,#0d6efd,#0b5ed7);
        color:white;
    }

    /* ===============================
       INPUT META
    =============================== */
    .inputMeta{
        text-align:center;
        border-radius:8px;
    }

    .inputMeta:focus{
        border-color:#0d6efd;
        box-shadow:0 0 0 .1rem rgba(13,110,253,.25);
    }

    /* ===============================
       LOADER TABLA
    =============================== */
    .table-loader{
        display:flex;
        align-items:center;
        justify-content:center;
        padding:30px;
        color:#0d6efd;
    }

</style>