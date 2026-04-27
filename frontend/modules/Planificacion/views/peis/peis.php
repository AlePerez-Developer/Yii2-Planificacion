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

$this->title = 'Planificación Institucional';

$this->params['subtitle'] = 'Administración de planes estratégicos institucionales';

$this->params['icon'] = 'fas fa-clipboard-list';

$this->params['iconColor'] = 'info';

$this->params['actions'] =
    '<button id="btnMostrarCrear" class="btn btn-primary bg-gradient-primary">
        <span class="icon closed">
            <span class="circle" style="margin-right: 4px">
                <span class="horizontal"></span>
                <span class="vertical"></span>
            </span>
             Nuevo 
        </span>
     </button>

     <a href="#" class="btn btn-outline-success btn-sm">
        <i class="fas fa-file-excel"></i> Exportar
     </a>';

$this->params['breadcrumbs'][] = [
    'label' => '/ PEI'
];
?>

<div class="card ">
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

    <div id="divTabla" class="card-body">
        <div class="card-pei">

            <div class="card-pei-header">
                <div class="card-pei-title">
                    Planes Estratégicos Institucionales
                </div>

            </div>

            <div id="peiLoading" class="p-4">
                <div class="table-loading"></div>
                <div class="table-loading"></div>
                <div class="table-loading"></div>
            </div>

            <div class="p-2" id="peiTableContainer" style="display:none;">
                <table id="tablaListaPeis" class="table w-100"></table>
            </div>

        </div>
    </div>

</div>










    <style>

        /* =====================================================
           DATATABLE ULTRA PRO 2026
        ===================================================== */

        :root{
            --primary:#2563eb;
            --primary-dark:#1d4ed8;
            --success:#16a34a;
            --danger:#dc2626;
            --text:#0f172a;
            --muted:#64748b;
            --line:#e2e8f0;
            --bg:#f8fafc;
            --card:#ffffff;
        }

        /* ================= CARD ================= */

        .card-pei{
            background:var(--card);
            border:0;
            border-radius:22px;
            box-shadow:
                    0 10px 30px rgba(15,23,42,.06),
                    0 2px 8px rgba(15,23,42,.03);
            overflow:hidden;
        }

        .card-pei-header{
            padding:22px 24px;
            border-bottom:1px solid #eef2f7;
            background:
                    linear-gradient(90deg,#ffffff,#f8fbff);
        }

        .card-pei-title{
            margin:0;
            font-size:22px;
            font-weight:800;
            color:var(--text);
        }

        .card-pei-subtitle{
            margin-top:4px;
            color:var(--muted);
            font-size:14px;
        }

        /* ================= TOOLBAR ================= */

        .dataTables_wrapper .row:first-child{
            padding:18px 24px 8px;
        }

        .dataTables_filter label,
        .dataTables_length label{
            font-weight:700;
            color:#334155;
            display:flex;
            align-items:center;
            gap:10px;
            margin:0;
        }

        .dataTables_filter{
            display:flex;
            align-items:center;
            gap:10px;
        }

        .dataTables_filter input{
            border:1px solid var(--line) !important;
            border-radius:16px !important;
            padding:11px 16px !important;
            min-width:320px;
            background:#fff;
            box-shadow:0 3px 10px rgba(0,0,0,.03);
            transition:.18s;
        }

        .dataTables_filter input:focus{
            border-color:var(--primary) !important;
            box-shadow:0 0 0 4px rgba(37,99,235,.12);
            outline:none !important;
        }

        .dataTables_length select{
            border-radius:14px !important;
            border:1px solid var(--line) !important;
            padding:8px 36px 8px 12px !important;
            background:#fff;
            box-shadow:0 3px 10px rgba(0,0,0,.03);
        }

        /* refresh integrado */
        .btn-refresh{
            width:44px;
            height:44px;
            border:0;
            border-radius:14px;
            background:linear-gradient(135deg,var(--primary),var(--primary-dark));
            color:#fff;
            transition:.18s;
            box-shadow:0 8px 20px rgba(37,99,235,.22);
        }

        .btn-refresh:hover{
            transform:translateY(-2px) rotate(20deg);
        }

        /* ================= TABLA ================= */

        #tablaListaPeis{
            border-collapse:separate !important;
            border-spacing:0 12px !important;
            padding:0 20px;
        }

        #tablaListaPeis thead th{
            border:0 !important;
            background:transparent !important;
            color:#64748b !important;
            font-size:12px;
            font-weight:800;
            letter-spacing:.8px;
            text-transform:uppercase;
            padding:12px !important;
        }

        #tablaListaPeis tbody tr{
            background:#fff;
            transition:.18s;
        }

        #tablaListaPeis tbody tr td{
            padding:20px 14px !important;
            border-top:1px solid #eef2f7 !important;
            border-bottom:1px solid #eef2f7 !important;
            vertical-align:middle;
        }

        #tablaListaPeis tbody tr td:first-child{
            border-left:1px solid #eef2f7 !important;
            border-radius:18px 0 0 18px;
        }

        #tablaListaPeis tbody tr td:last-child{
            border-right:1px solid #eef2f7 !important;
            border-radius:0 18px 18px 0;
        }

        #tablaListaPeis tbody tr:hover{
            transform:translateY(-3px);
            box-shadow:0 12px 26px rgba(0,0,0,.06);
        }

        /* ================= CONTENIDO ================= */

        .badge-codigo{
            width:34px;
            height:34px;
            border-radius:12px;
            display:flex;
            align-items:center;
            justify-content:center;
            margin:auto;
            background:linear-gradient(135deg,var(--primary),#3b82f6);
            color:#fff;
            font-size:12px;
            font-weight:800;
        }

        .pei-main{
            font-size:18px;
            font-weight:800;
            color:var(--text);
            margin-bottom:5px;
        }

        .pei-sub{
            font-size:13px;
            color:var(--muted);
            line-height:1.55;
        }

        /* ================= ESTADOS ================= */

        .badge-vigente,
        .badge-caducado{
            padding:10px 18px;
            border-radius:999px;
            font-size:12px;
            font-weight:800;
            letter-spacing:.3px;
        }

        .badge-vigente{
            background:#dcfce7;
            color:#166534;
        }

        .badge-caducado{
            background:#fee2e2;
            color:#991b1b;
        }

        /* ================= BOTONES ================= */

        .btn-action{
            width:42px;
            height:42px;
            border:0;
            border-radius:14px;
            margin:0 3px;
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
            transform:translateY(-2px) scale(1.04);
            box-shadow:0 10px 18px rgba(0,0,0,.08);
        }

        /* ================= FOOTER ================= */

        .dataTables_wrapper .row:last-child{
            padding:10px 24px 24px;
        }

        .dataTables_info{
            color:var(--muted) !important;
            font-weight:600;
            font-size:14px;
        }

        /* ================= PAGINACIÓN ================= */

        .dataTables_paginate .pagination{
            gap:8px;
        }

        .page-link{
            min-width:44px;
            height:44px;
            border-radius:14px !important;
            border:1px solid var(--line) !important;
            display:flex !important;
            align-items:center;
            justify-content:center;
            background:#fff !important;
            color:#334155 !important;
            font-weight:800;
            transition:.18s;
        }

        .page-link:hover{
            background:#eff6ff !important;
            color:var(--primary) !important;
            transform:translateY(-2px);
        }

        .page-item.active .page-link{
            background:linear-gradient(135deg,var(--primary),var(--primary-dark)) !important;
            color:#fff !important;
            border-color:var(--primary) !important;
            box-shadow:0 12px 22px rgba(37,99,235,.25);
        }

        /* ================= SKELETON ================= */

        .table-loading{
            height:60px;
            border-radius:14px;
            background:linear-gradient(90deg,#f1f5f9,#ffffff,#f1f5f9);
            background-size:200% 100%;
            animation:loading 1.3s infinite;
            margin-bottom:12px;
        }

        @keyframes loading{
            0%{background-position:200% 0}
            100%{background-position:-200% 0}
        }

        /* ================= RESPONSIVE ================= */

        @media(max-width:768px){

            .dataTables_filter input{
                min-width:100%;
            }

            .pei-main{
                font-size:15px;
            }

            .pei-sub{
                font-size:12px;
            }

            .dataTables_info,
            .dataTables_paginate{
                text-align:center !important;
                float:none !important;
                width:100%;
                margin-top:12px;
            }
        }

        #btnMostrarCrear{
            font-size: small;
        }


        /* =====================================================
   ESTADO V2 PREMIUM
===================================================== */

        .btn-toggle-estado{
            border:0;
            border-radius:999px;
            min-width:150px;
            height:42px;
            padding:0 16px;
            font-size:12px;
            font-weight:800;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:8px;
            transition:.18s;
        }

        .estado-on{
            background:#dcfce7;
            color:#166534;
        }

        .estado-off{
            background:#fee2e2;
            color:#991b1b;
        }

        .btn-toggle-estado:hover{
            transform:translateY(-2px);
            box-shadow:0 10px 18px rgba(0,0,0,.08);
        }

        .estado-loading{
            opacity:.8;
            pointer-events:none;
        }

    </style>



