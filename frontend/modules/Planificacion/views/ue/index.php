<?php
use yii\web\JqueryAsset;

app\modules\Planificacion\assets\PlanificacionAsset::register($this);

$this->registerJsFile("@planificacionModule/js/ue/Ue.js", [
    'depends' => [
        JqueryAsset::class
    ]
]);

$this->registerJsFile("@planificacionModule/js/ue/dt-declaration.js", [
    'depends' => [
        JqueryAsset::class
    ]
]);

$this->title = 'Planificación Institucional';

$this->params['subtitle'] = 'Administración de unidades ejecutoras';

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
                    <form id="formUe" action="" method="post">
                        <div class="form-group">
                            <label for="ue" class="control-label">Unidad Ejecutora</label>
                            <input type="text" class="form-control input-sm" id="ue" name="ue" pattern="\d{3}" maxlength="3">
                        </div>
                        <div class="form-group">
                            <label for="descripcion" class="control-label">Descripcion</label>
                            <textarea class="form-control input-sm txt" id="descripcion"
                                      name="descripcion" rows="3" placeholder="descripcion del pei"></textarea>
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
                    Estructura Unidad Ejecutora
                </div>

            </div>

            <div id="dticTableLoading" class="p-4">
                <div class="table-loading"></div>
                <div class="table-loading"></div>
                <div class="table-loading"></div>
            </div>

            <div class="p-2" id="dticTableContainer" style="display:none;">
                <table id="tablaListaUes" class="table w-100 dtic-table"></table>
            </div>

        </div>
    </div>
</div>
