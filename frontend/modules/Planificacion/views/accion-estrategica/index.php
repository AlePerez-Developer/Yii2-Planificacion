<?php
use yii\web\JqueryAsset;

app\modules\Planificacion\assets\PlanificacionAsset::register($this);

$this->registerJsFile("@planificacionModule/js/accion-estrategica/accionEstrategica.js", [
    'depends' => [
        JqueryAsset::class
    ]
]);

$this->registerJsFile("@planificacionModule/js/accion-estrategica/dt-declaration.js", [
    'depends' => [
        JqueryAsset::class
    ]
]);

$this->title = 'Planificación Institucional';

$this->params['subtitle'] = 'Administración de acciones estratégicas institucionales';

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
    'label' => '/ Peis'
];
?>

<div class="card ">
    <div id="divDatos" class="card-body" style="display: none">
        <div class="col d-flex justify-content-center">
            <div class="card card-dtic-form" style="width: 40rem;">
                <div class="card-header card-dtic-form-header">Ingreso Datos</div>
                <div class="card-body card-dtic-form-body">
                    <form id="formAccionEstrategica" action="" method="post">
                        <div class="form-group">
                            <label for="descripcion" class="control-label">Descripcion de la accion estrategica</label>
                            <textarea class="form-control input-sm dtic-input txt" id="descripcion"
                                      name="descripcion" rows="3" placeholder="descripcion de la accion estrategica"></textarea>
                        </div>
                    </form>
                </div>
                <div class="card-footer card-dtic-form-footer">
                    <button id="btnGuardar" name="btnGuardar" class='btn-guardar'><i class='fa fa-check-circle'></i> <span class='btn_text'> Guardar </span> </button>
                    <button id="btnCancelar" name="btnCancelar" class='btn-cancel'><span class='fa fa-times-circle'></span> Cancelar </button>
                </div>
            </div>
        </div>
    </div>

    <div id="divTabla" class="card-body">
        <div class="card-dtic-style">

            <div class="card-dtic-style-header">
                <div class="card-dtic-style-title">
                    Acciones Estratégicas Institucionales
                </div>
            </div>

            <div id="dticTableLoading" class="p-4">
                <div class="table-loading"></div>
                <div class="table-loading"></div>
                <div class="table-loading"></div>
            </div>

            <div class="p-2" id="dticTableContainer" style="display:none;">
                <table id="tablaListaAccionesEstrategicas" class="table w-100 dtic-table"></table>
            </div>

        </div>
    </div>
</div>
