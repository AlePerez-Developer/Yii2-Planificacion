<?php
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
             Nuevo registro
        </span>
     </button>
     
<button class="btn-custom closed" id="toggleBtn">
  <span class="circle">
    <span class="horizontal"></span>
    <span class="vertical"></span>
  </span>
  <span class="btn-text">Nuevo Registro</span>
</button>

     <a href="" id="btnReportePdf" class="btn btn-outline-danger btn-sm">
        <i class="fas fa-file-pdf"></i> Exportar
     </a>';



$this->params['breadcrumbs'][] = [
    'label' => '/ Peis'
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
        <div class="card-dtic-style">

            <div class="card-dtic-style-header">
                <div class="card-dtic-style-title">
                    Planes Estratégicos Institucionales
                </div>
            </div>

            <div id="dticTableLoading" class="p-4">
                <div class="table-loading"></div>
                <div class="table-loading"></div>
                <div class="table-loading"></div>
            </div>

            <div class="p-2" id="dticTableContainer" style="display:none;">
                <table id="tablaListaPeis" class="table w-100 dtic-table"></table>
            </div>

        </div>
    </div>
</div>

<div id="tabs_container_${id}" style=" min-height: 300px; height: auto">
    <ul class="nav nav-pills nav-fill" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#pills-home" type="button" role="tab" aria-controls="pills-home" aria-selected="true">Home</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pills-profile-tab" data-bs-toggle="pill" data-bs-target="#pills-profile" type="button" role="tab" aria-controls="pills-profile" aria-selected="false">Profile</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pills-contact-tab" data-bs-toggle="pill" data-bs-target="#pills-contact" type="button" role="tab" aria-controls="pills-contact" aria-selected="false">Contact</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pills-disabled-tab" data-bs-toggle="pill" data-bs-target="#pills-disabled" type="button" role="tab" aria-controls="pills-disabled" aria-selected="false" disabled>Disabled</button>
        </li>
    </ul>
    <div class="tab-content" id="pills-tabContent">
        <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab" tabindex="0">
            <div style="background-color: red; height: 300px"></div>
        </div>
        <div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab" tabindex="0">
            <div style="background-color: green; height: 300px"></div>
        </div>
        <div class="tab-pane fade" id="pills-contact" role="tabpanel" aria-labelledby="pills-contact-tab" tabindex="0">
            <div style="background-color: blue; height: 300px"></div>
        </div>
        <div class="tab-pane fade" id="pills-disabled" role="tabpanel" aria-labelledby="pills-disabled-tab" tabindex="0">
            <div style="background-color: purple; height: 300px"></div>
        </div>
    </div>
</div>



<style>


.nav-pills{
    gap: 5px;
}
.nav-item {
    border:0;
    border-radius:22px;
    box-shadow:
            0 10px 30px rgba(15,23,42,.06),
            0 2px 8px rgba(15,23,42,.03);
}


.nav-item:hover{
    background-color: #e6e6e6;
}

/* --- ESTILO BASE DEL BOTÓN AZUL --- */
.btn-custom {
    display: inline-flex;
    align-items: center;
    padding: 8px 15px;
    font-family: 'Segoe UI', sans-serif;
    font-weight: 600;
    font-size: 13px;
    gap: 12px;

    border-radius: 15px !important;;
    background-color: #0d6efd; /* Color primario de Bootstrap */
    color: #ffffff;
    border: none;
    cursor: pointer;
    transition: background-color 0.25s ease-in-out, box-shadow 0.25s ease-in-out, transform 0.25s ease-in-out;

}

/* Hover con la sombra azulada que querías */
.btn-custom:hover {
    box-shadow: 0 8px 20px rgb(47 89 197);
    transform: translateY(-1px);
}

.btn-custom:active {
    transform: translateY(1px);
}

/* --- EL CÍRCULO DEL ICONO --- */
.circle {
    position: relative;
    width: 24px;
    height: 24px;
    background-color: rgba(255, 255, 255, 0.2); /* Fondo sutil para el círculo */
    border-radius: 100%;
    display: inline-block;
    vertical-align: middle;
    transition: background-color 0.3s ease;
}

.btn-custom:hover .circle {
    background-color: rgba(255, 255, 255, 0.3); /* Resalta el círculo en hover */
}

/* --- LAS LÍNEAS (Estructura limpia) --- */
.horizontal, .vertical {
    position: absolute;
    background-color: whitesmoke;
    border-radius: 2px;
    top: 50%;
    left: 50%;
    transform-origin: center; /* Se aseguran de girar exactamente sobre su propio centro */
}

/* Medidas de la Cruz */
.horizontal {
    width: 12px;
    height: 2px;
    margin-top: -1px;  /* Centrado perfecto exacto (mitad de la altura) */
    margin-left: -6px; /* Centrado perfecto exacto (mitad del ancho) */
}

.vertical {
    width: 2px;
    height: 12px;
    margin-top: -6px;
    margin-left: -1px;
}


/* ========================================================
   ANIMACIÓN FLUIDA (Tu lógica de giro + Mi control de opacidad)
   ======================================================== */

/* ========================================================
   ANIMACIÓN CORREGIDA (El guion termina en horizontal)
   ======================================================== */

/* --- ESTADO: CLOSED (Muestra la Cruz) --- */
.closed .vertical {
    transition: all 0.5s ease-in-out;
    transform: rotate(0deg); /* Posición vertical original */
    opacity: 1;
}
.closed .horizontal {
    transition: all 0.5s ease-in-out;
    transform: rotate(0deg); /* Posición horizontal original */
    opacity: 1;
}

/* --- ESTADO: OPENED (La vertical gira y se oculta, la horizontal mantiene su forma) --- */
.opened .vertical {
    transition: all 0.5s ease-in-out;
    transform: rotate(90deg); /* Gira 90° para acostarse sobre la horizontal */
    opacity: 0;               /* Se desvanece suavemente en el proceso */
}
.opened .horizontal {
    transition: all 0.5s ease-in-out;
    transform: rotate(180deg); /* Gira media vuelta completa (180°). Hace el efecto de giro pero vuelve a quedar horizontal */
    opacity: 1;                /* Se mantiene visible como el guion final */
}


</style>