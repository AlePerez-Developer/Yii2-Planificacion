<?php
use yii\helpers\Html;
use yii\web\JqueryAsset;

app\modules\Planificacion\assets\PlanificacionAsset::register($this);

$this->registerJsFile("@planificacionModule/js/peis/Peis.js", [
    'depends' => [
        JqueryAsset::class
    ]
]);

$this->registerJsFile("@planificacionModule/js/peis/dt-declaration.js", [
    'depends' => [
        JqueryAsset::class
    ]
]);

$this->title = 'Planificacion';
$this->params['breadcrumbs'] = [['label' => '/Peis']];
?>

<div class="card ">
    <div class="card-header">
        <div class="row">
            <div class="col-6">
                <button id="btnMostrarCrear" class="btn btn-primary bg-gradient-primary">
                    <span class="icon closed">
                        <span class="circle">
                            <span class="horizontal"></span>
                            <span class="vertical"></span>
                        </span>
                        Agregar Pei
                    </span>
                </button>
            </div>
            <div class="col-6" style="text-align: right;">
                <?= Html::a('Report Pei', ['reporte'], ['class' => 'btn btn-success', 'target' => '_Blank']) ?>
            </div>
        </div>
    </div>
    <div id="divDatos" class="card-body" style="display: none">
        <div class="col d-flex justify-content-center">
            <div class="card " style="width: 40rem;">
                <div class="card-header bg-gradient-primary">Ingreso Datos</div>
                <div class="card-body">
                    <form id="formPei" action="" method="post">
                        <div class="form-group">
                            <label for="descripcion" class="control-label">Descripcion del pei</label>
                            <textarea class="form-control input-sm txt" id="descripcion"
                                      name="descripcion" rows="3" placeholder="descripcion del pei"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="fechaAprobacion" class="control-label">Fecha de aprobacion</label>
                            <input type="date" class="form-control input-sm" id="fechaAprobacion" name="fechaAprobacion" onfocus="this.showPicker()"
                                   pattern="\d{4}-\d{2}-\d{2}">
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="gestionInicio">Gestion de inicio</label>
                                    <input type="text" class="form-control input-sm num" id="gestionInicio" name="gestionInicio"
                                           placeholder="Gestion Inicio" style="width: 130px">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="gestionFin">Gestion Final</label>
                                    <input type="text" class="form-control input-sm num" id="gestionFin" name="gestionFin"
                                           placeholder="Gestion final" style="width: 130px">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <button id="btnGuardar" name="btnGuardar" class='btn btn-primary bg-gradient-primary'><i class='fa fa-check-circle'></i> <span class='btn_text'> Guardar </span> </button>
                    <button id="btnCancelar" name="btnCancelar" class='btn btn-danger'><span class='fa fa-times-circle'></span> Cancelar </button>
                </div>
            </div>
        </div>
    </div>



    <div class="card-pei">
        <div class="card-pei-header">
            <h4 class="card-pei-title">Planes Estratégicos Institucionales</h4>
        </div>

        <div class="p-3">
            <table id="tablaListaPeis" class="table w-100"></table>
        </div>
    </div>
</div>

<style>

    /* ===== CONTENEDOR GENERAL ===== */
    .card-pei{
        border:0;
        border-radius:18px;
        box-shadow:0 8px 30px rgba(0,0,0,.06);
        overflow:hidden;
        background:#fff;
    }

    /* ===== CABECERA ===== */
    .card-pei-header{
        padding:18px 22px;
        border-bottom:1px solid #eef2f7;
        background:linear-gradient(90deg,#ffffff,#f8fafc);
    }

    .card-pei-title{
        font-size:20px;
        font-weight:700;
        color:#0f172a;
        margin:0;
    }

    /* ===== TABLA ===== */
    #tablaListaPeis{
        border-collapse:separate !important;
        border-spacing:0 10px !important;
        margin-top:0 !important;
    }

    #tablaListaPeis thead th{
        border:0 !important;
        background:#fff !important;
        color:#64748b !important;
        font-size:13px;
        font-weight:700;
        text-transform:uppercase;
        letter-spacing:.5px;
        padding:14px 12px !important;
    }

    #tablaListaPeis tbody tr{
        background:#fff;
        box-shadow:0 3px 10px rgba(0,0,0,.04);
        transition:all .18s ease;
    }

    #tablaListaPeis tbody tr:hover{
        transform:translateY(-2px);
        box-shadow:0 8px 22px rgba(0,0,0,.08);
    }

    #tablaListaPeis tbody td{
        padding:18px 14px !important;
        vertical-align:middle;
        border-top:1px solid #eef2f7 !important;
        border-bottom:1px solid #eef2f7 !important;
    }

    #tablaListaPeis tbody td:first-child{
        border-left:1px solid #eef2f7 !important;
        border-radius:14px 0 0 14px;
    }

    #tablaListaPeis tbody td:last-child{
        border-right:1px solid #eef2f7 !important;
        border-radius:0 14px 14px 0;
    }

    /* ===== DESCRIPCIÓN ===== */
    .pei-main{
        font-size:17px;
        font-weight:700;
        color:#0f172a;
        margin-bottom:4px;
    }

    .pei-sub{
        font-size:13px;
        color:#64748b;
        line-height:1.5;
    }

    /* ===== BADGES ===== */
    .badge-codigo{
        background:#2563eb;
        color:#fff;
        border-radius:10px;
        padding:7px 10px;
        font-size:12px;
        font-weight:700;
    }

    .badge-vigente{
        background:#dcfce7;
        color:#166534;
        padding:8px 16px;
        border-radius:50px;
        font-weight:700;
        font-size:12px;
    }

    .badge-caducado{
        background:#fee2e2;
        color:#991b1b;
        padding:8px 16px;
        border-radius:50px;
        font-weight:700;
        font-size:12px;
    }

    /* ===== BOTONES ===== */
    .btn-action{
        width:38px;
        height:38px;
        border-radius:10px;
        border:0;
        margin:0 2px;
        transition:.18s;
    }

    .btn-edit{
        background:#fff7ed;
        color:#ea580c;
    }

    .btn-delete{
        background:#fef2f2;
        color:#dc2626;
    }

    .btn-action:hover{
        transform:translateY(-2px);
        box-shadow:0 6px 12px rgba(0,0,0,.08);
    }

    /* ===== BUSCADOR ===== */
    .dataTables_filter input{
        border-radius:12px !important;
        border:1px solid #dbe2ea !important;
        padding:8px 14px !important;
        min-width:250px;
    }

    /* ===== PAGINACIÓN ===== */
    .page-link{
        border-radius:10px !important;
        margin:0 2px;
        border:0 !important;
        color:#334155 !important;
    }

    .page-item.active .page-link{
        background:#2563eb !important;
        color:#fff !important;
    }

    /* ===== RESPONSIVE ===== */
    @media(max-width:768px){

        .pei-main{
            font-size:15px;
        }

        .pei-sub{
            font-size:12px;
        }

        #tablaListaPeis tbody td{
            padding:14px 10px !important;
        }
    }

    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_length{
        margin-bottom:18px;
    }

    /* BUSCADOR */
    .dataTables_filter label{
        font-weight:600;
        color:#334155;
        display:flex;
        align-items:center;
        gap:10px;
    }

    .dataTables_filter input{
        border:1px solid #dbe2ea !important;
        border-radius:14px !important;
        padding:10px 14px !important;
        min-width:280px;
        background:#fff;
        box-shadow:0 2px 8px rgba(0,0,0,.03);
        transition:.18s;
    }

    .dataTables_filter input:focus{
        border-color:#2563eb !important;
        box-shadow:0 0 0 4px rgba(37,99,235,.10);
        outline:none !important;
    }

    /* SELECT REGISTROS */
    .dataTables_length label{
        font-weight:600;
        color:#334155;
        display:flex;
        align-items:center;
        gap:10px;
    }

    .dataTables_length select{
        border-radius:12px !important;
        border:1px solid #dbe2ea !important;
        padding:6px 30px 6px 10px !important;
        background:#fff;
        box-shadow:0 2px 8px rgba(0,0,0,.03);
    }

    /* ===============================
       FOOTER INFO
    =================================*/

    .dataTables_info{
        color:#64748b !important;
        font-size:14px;
        font-weight:500;
        padding-top:18px !important;
    }

    /* ===============================
       PAGINACIÓN PRO
    =================================*/

    .dataTables_paginate{
        padding-top:10px !important;
    }

    .dataTables_paginate .pagination{
        gap:8px;
    }

    .page-link{
        min-width:42px;
        height:42px;
        border-radius:14px !important;
        border:1px solid #e2e8f0 !important;
        display:flex !important;
        align-items:center;
        justify-content:center;
        font-weight:700;
        color:#334155 !important;
        background:#fff !important;
        box-shadow:0 2px 8px rgba(0,0,0,.03);
        transition:.18s;
    }

    .page-link:hover{
        background:#eff6ff !important;
        color:#2563eb !important;
        transform:translateY(-2px);
    }

    .page-item.active .page-link{
        background:linear-gradient(135deg,#2563eb,#1d4ed8) !important;
        color:#fff !important;
        border-color:#2563eb !important;
        box-shadow:0 10px 20px rgba(37,99,235,.25);
    }

    .page-item.disabled .page-link{
        opacity:.45;
        background:#f8fafc !important;
    }

    /* ===============================
       RESPONSIVE
    =================================*/

    @media(max-width:768px){

        .dataTables_filter input{
            min-width:100%;
        }

        .dataTables_wrapper .row{
            gap:12px;
        }

        .dataTables_info,
        .dataTables_paginate{
            text-align:center !important;
            float:none !important;
        }
    }

</style>