<?php
use yii\helpers\Url;
use yii\web\JqueryAsset;

app\modules\Planificacion\assets\PlanificacionAsset::register($this);
/*
$this->registerJs("
     urlProgramar = '" . \yii\helpers\Url::to(['programar-indicadores/index']) . "';
", \yii\web\View::POS_HEAD);*/

$this->registerJsFile("@planificacionModule/js/obj-estrategico/ObjEstrategico.js",[
    'depends' => [
        JqueryAsset::class
    ]
]);

$this->registerJs("
    urlProgramar = '" . Url::to(['programar-indicador/index']) . "';
");

$this->registerJsFile("@planificacionModule/js/obj-estrategico/dt-declaration.js", [
    'depends' => [
        JqueryAsset::class
    ]
]);

$this->registerJsFile("@planificacionModule/js/obj-estrategico/s2-declaration.js", [
    'depends' => [
        JqueryAsset::class
    ]
]);

$this->title = 'Planificación Institucional';

$this->params['subtitle'] = 'Administración de objetivos estratégicos institucionales';

$this->params['icon'] = 'fas fa-clipboard-list';

$this->params['iconColor'] = 'info';

$this->params['actions'] =
    '<button id="btnMostrarCrear" class="btn btn-primary bg-gradient-primary">
        <span class="icon closed">
            <span class="circle" style="margin-right: 4px">
                <span class="horizontal"></span>
                <span class="vertical"></span>
            </span>
             Nuevo objetivo
        </span>
     </button>

     <a href="" id="btnReportePdf" class="btn btn-outline-danger btn-sm">
        <i class="fas fa-file-pdf"></i> Exportar
     </a>';

$this->params['breadcrumbs'][] = [
    'label' => '/ Objetivos estrategicos institucionales',
];
?>
<div class="card ">
    <div id="divDatos" class="card-body" style="display: none">
        <div class="col d-flex justify-content-center">
            <div class="card " style="width: 80rem;">
                <div class="card-header bg-gradient-primary">Ingreso Datos</div>
                <div class="card-body">
                    <form id="formObjEstrategico" action="" method="post">

                        <div class="row mb-3">
                            <div class="col-4">
                                <label for="areasEstrategicas" class="lblTitulo">Seleccione una Area Estrategica</label>
                                <select id="areasEstrategicas" name="areasEstrategicas" class="form-control codigo_group">
                                </select>
                            </div>
                            <div class="col-4">
                                <label for="politicasEstrategicas" class="lblTitulo">Seleccione una Politica Estrategica</label>
                                <select id="politicasEstrategicas" name="politicasEstrategicas" class="form-control codigo_group">
                                </select>
                            </div>
                            <div class="col-4">
                                <label for="codigo">Codigo de Objetivo Estrategico (OE)</label>
                                <input type="text" class="form-control input-sm num codigo_group" id="codigo" name="codigo" maxlength="1"  placeholder="Codigo" style="width: 100px"  >
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="objetivo" class="control-label">Descripcion del objetivo estrategico</label>
                                    <textarea class="form-control input-sm txt" id="objetivo" name="objetivo" rows="3" placeholder="Descripcion del objetivo estrategico"></textarea>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="producto" class="control-label">Resultado/Producto esperado</label>
                                    <textarea class="form-control input-sm txt" id="producto" name="producto" rows="3" placeholder="Resultado/Producto esperado"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="contenedor">
                            <div class="ltc">Datos del indicador del objetivo</div>
                            <div class="square">
                                <div class="row">
                                    <div class="col-6">
                                        <label for="descripcion" class="control-label">Descripcion</label>
                                        <textarea class="form-control input-sm txt" id="descripcion" name="descripcion" rows="3" placeholder="Descripcion del indicador"></textarea>
                                    </div>
                                    <div class="col-6">
                                        <label for="formula" class="control-label">Formula</label>
                                        <textarea class="form-control input-sm txt" id="formula" name="formula" rows="3" placeholder="Formula del indicador"></textarea>
                                    </div>
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