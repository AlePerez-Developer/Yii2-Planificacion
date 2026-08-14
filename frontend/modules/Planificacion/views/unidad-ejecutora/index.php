<?php
use yii\web\JqueryAsset;

app\modules\Planificacion\assets\PlanificacionAsset::register($this);

$this->registerJsFile("@planificacionModule/js/unidad-ejecutora/Index.js", ['depends' => [JqueryAsset::class]]);
$this->registerJsFile("@planificacionModule/js/unidad-ejecutora/dt-declaration.js", ['depends' => [JqueryAsset::class]]);
$this->registerJsFile("@planificacionModule/js/unidad-ejecutora/s2-declaration.js", ['depends' => [JqueryAsset::class]]);

$this->title = 'Planificación Institucional';

$this->params['subtitle'] = 'Administración de unidades ejecutoras';

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
        'label' => '/ Unidad Ejecutora'
];
?>

<div class="card ">

    <div id="divDatos" class="card-body" style="display: none">
        <div class="col d-flex justify-content-center">
            <div class="card-dtic-form" style="width: 120rem;">
                <div class="card-header card-dtic-form-header">Ingreso Datos</div>
                <div class="card-body card-dtic-form-body">
                    <form id="formUe" action="" method="post">
                        <div class="form-group">
                            <label for="idDa" class="control-label">Direccion Administrativa</label>
                            <select class="form-control codigo_group dtic-input"
                                    id="idDa" name="idDa">
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="ue" class="control-label">Unidad Ejecutora</label>
                            <input type="text" class="form-control input-sm codigo_group" id="ue" name="ue" pattern="\d{3}" maxlength="3">
                        </div>
                        <div class="form-group">
                            <label for="descripcion" class="control-label">Descripcion</label>
                            <textarea class="form-control input-sm txt" id="descripcion"
                                      name="descripcion" rows="3" placeholder="descripcion unidad ejecutora"></textarea>
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
                    Unidades Ejecutoras
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
