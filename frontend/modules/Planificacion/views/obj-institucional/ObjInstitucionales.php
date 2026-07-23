<?php

use app\modules\Planificacion\assets\PlanificacionAsset;
use yii\web\JqueryAsset;

PlanificacionAsset::register($this);

$this->registerJsFile('@planificacionModule/js/obj-institucional/s2-declaration.js', [
    'depends' => [JqueryAsset::class],
]);
$this->registerJsFile('@planificacionModule/js/obj-institucional/dt-declaration.js', [
    'depends' => [JqueryAsset::class],
]);
$this->registerJsFile('@planificacionModule/js/obj-institucional/ObjInstitucional.js', [
    'depends' => [JqueryAsset::class],
]);
$this->registerCssFile("@planificacionModule/css/obj-institucional/style.css", [
        'depends' => [PlanificacionAsset::class]
]);

$this->title = 'Planificación Institucional';

$this->params['subtitle'] = 'Administración de objetivos institucionales';

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
        'label' => '/ Objetivos institucionales'
];
?>
<div class="card">
    <div id="divDatos" class="card-body" style="display: none">
        <div class="col d-flex justify-content-center">
            <div class="card-dtic-form" style="width: 120rem;">
                <div class="card-header card-dtic-form-header">Ingreso Datos</div>
                <div class="card-body card-dtic-form-body">
                    <form id="formObjInstitucional" method="post" autocomplete="off">
                        <div class="row">
                            <div class="form-group">
                                <label for="idObjEstrategico">Objetivo estratégico</label>
                                <select
                                    id="idObjEstrategico"
                                    name="idObjEstrategico"
                                    class="form-control dtic-input"
                                    style="width:100%;"
                                ></select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="codigo">Código</label>
                                    <input
                                        id="codigo"
                                        name="codigo"
                                        type="text"
                                        class="form-control dtic-input"
                                        maxlength="2"
                                        inputmode="numeric"
                                        placeholder="01"
                                    >
                                    <small class="form-text text-muted">Formato: 01, 02, 03...</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="objetivo">Objetivo institucional</label>
                                    <textarea
                                        id="objetivo"
                                        name="objetivo"
                                        class="form-control dtic-input"
                                        rows="4"
                                        maxlength="200"
                                        placeholder="Descripción del objetivo institucional"
                                    ></textarea>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="producto">Resultado / producto esperado</label>
                                    <textarea
                                        id="producto"
                                        name="producto"
                                        class="form-control dtic-input"
                                        rows="4"
                                        maxlength="200"
                                        placeholder="Resultado o producto esperado"
                                    ></textarea>
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
                    Listado de objetivos institucionales
                </div>
            </div>

            <div id="dticTableLoading" class="p-4">
                <div class="table-loading"></div>
                <div class="table-loading"></div>
                <div class="table-loading"></div>
            </div>

            <div id="dticTableContainer" class="p-2" style="display:none;">
                <table id="tablaListaObjInstitucionales" class="table w-100 dtic-table"></table>
            </div>
        </div>
    </div>
</div>
