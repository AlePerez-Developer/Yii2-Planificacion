<?php
use yii\helpers\Url;
use yii\web\JqueryAsset;
use app\modules\Planificacion\assets\PlanificacionAsset;

PlanificacionAsset::register($this);

$this->registerJsFile("@planificacionModule/js/obj-estrategico/s2-declaration.js", [
        'depends' => [
                JqueryAsset::class
        ]
]);

$this->registerJsFile("@planificacionModule/js/obj-estrategico/dt-declaration.js", [
    'depends' => [
        JqueryAsset::class
    ]
]);

$this->registerJsFile("@planificacionModule/js/obj-estrategico/ObjEstrategico.js",[
        'depends' => [
                JqueryAsset::class
        ]
]);

$this->registerCssFile("@planificacionModule/css/obj-estrategico/style.css", [
        'depends' => [
                PlanificacionAsset::class
        ]
]);

$this->registerJs("
    urlProgramar = '" . Url::to(['indicador-estrategico-programacion-anual/index']) . "';
");

$this->title = 'Planificación Institucional';

$this->params['subtitle'] = 'Administración de objetivos estratégicos institucionales';

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
    'label' => '/ Obj. Estrategicos institucionales',
];
?>
<div class="card ">

    <div id="divDatos" class="card-body" style="display: none">
        <div class="col d-flex justify-content-center">
            <div class="card-dtic-form" style="width: 120rem;">
                <div class="card-header card-dtic-form-header">Ingreso Datos</div>
                <div class="card-body card-dtic-form-body">
                    <form id="formObjEstrategico" action="" method="post">
                        <div class="row mb-3" style="display: flex; align-items: flex-start !important;" >
                            <div class="col-4">
                                <label for="areasEstrategicas" class="lblTitulo">Seleccione una Área Estrategica</label>
                                <select id="areasEstrategicas" name="areasEstrategicas" class="form-control codigo_group dtic-input">
                                </select>
                            </div>
                            <div class="col-4">
                                <label for="politicasEstrategicas" class="lblTitulo">Seleccione una Politica Estrategica</label>
                                <select id="politicasEstrategicas" name="politicasEstrategicas" class="form-control codigo_group dtic-input">
                                </select>
                            </div>
                            <div class="col-4">
                                <label for="codigo">Codigo de Objetivo Estrategico (OE)</label>
                                <input type="text" class="form-control input-sm num codigo_group dtic-input" id="codigo" name="codigo" maxlength="1"  placeholder="Codigo">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="objetivo" class="control-label">Descripcion del objetivo estrategico</label>
                                    <textarea class="form-control input-sm dtic-input txt" id="objetivo" name="objetivo" rows="3" placeholder="Descripcion del objetivo estrategico"></textarea>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="producto" class="control-label">Resultado/Producto esperado</label>
                                    <textarea class="form-control input-sm dtic-input txt" id="producto" name="producto" rows="3" placeholder="Resultado/Producto esperado"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="contenedor">
                            <div class="ltc">Datos del indicador del objetivo</div>
                            <div class="square">
                                <div class="row">
                                    <div class="col-6">
                                        <label for="descripcion" class="control-label">Descripcion</label>
                                        <textarea class="form-control input-sm dtic-input txt" id="descripcion" name="descripcion" rows="3" placeholder="Descripcion del indicador"></textarea>
                                    </div>
                                    <div class="col-6">
                                        <label for="formula" class="control-label">Formula</label>
                                        <textarea class="form-control input-sm dtic-input txt" id="formula" name="formula" rows="3" placeholder="Formula del indicador"></textarea>
                                    </div>
                                </div>
                            </div>
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
                    Objetivos Estratégicos Institucionales
                </div>
            </div>
            <div id="dticTableLoading" class="p-4">
                <div class="table-loading"></div>
                <div class="table-loading"></div>
                <div class="table-loading"></div>
            </div>
            <div class="p-2" id="dticTableContainer" style="display:none;">
                <table id="tablaListaObjEstrategicos" class="table w-100 dtic-table"></table>
            </div>
        </div>
    </div>

</div>