<?php

use yii\web\JqueryAsset;
use yii\helpers\Html;

app\modules\Planificacion\assets\PlanificacionAsset::register($this);


$this->registerJsFile("@planificacionModule/js/indicador-estrategico/dt-declaration.js", [
        'depends' => [
                JqueryAsset::class
        ]
]);

$this->registerJsFile("@planificacionModule/js/indicador-estrategico/s2-declaration.js", [
        'depends' => [
                JqueryAsset::class
        ]
]);

$this->registerJsFile("@planificacionModule/js/indicador-estrategico/IndicadorEstrategico.js", [
        'depends' => [
                JqueryAsset::class
        ]
]);


$this->title = 'Planificación Institucional';

$this->params['subtitle'] = 'Administración de Indicadores estratégicos institucionales';

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
        'label' => '/ Ind. Estrategicos'
];
?>

<div class="card ">
    <div id="divDatos" class="card-body" style="display: none">
        <div class="col d-flex justify-content-center">
            <div class="card card-dtic-form" style="width: 120rem;">
                <div class="card-header card-dtic-form-header">Ingreso Datos</div>
                <div class="card-body card-dtic-form-body">
                    <form id="formIndicadorEstrategico" action="" method="post">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="idObjEstrategico">Seleccione el objetivo estrategico</label>
                                    <select class="form-control objEstrategico codigo_group dtic-input"
                                            id="idObjEstrategico" name="idObjEstrategico">
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="codigo">Codigo indicador</label>
                                    <input type="text" class="form-control input-sm num codigo_group dtic-input"
                                           id="codigo" name="codigo" maxlength="3"
                                           placeholder="Codigo indicador" >
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="meta">Meta indicador</label>
                                    <input type="text" class="form-control input-sm num dtic-input" id="meta" name="meta"
                                           placeholder="Meta del indicador" >
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="lineaBase">Linea base</label>
                                    <input type="text" class="form-control input-sm num dtic-input" id="lineaBase"
                                           name="lineaBase" placeholder="Meta del indicador" >
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group">
                                <label for="descripcion" class="control-label">Descripcion del indicador</label>
                                <textarea class="form-control input-sm txt dtic-input" id="descripcion" name="descripcion"
                                          rows="4" placeholder="Descripcion del indicador"></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="idTipoResultado">Seleccione el resultado</label>
                                    <select class="form-control dtic-input" id="idTipoResultado" name="idTipoResultado">
                                    </select>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="idCategoriaIndicador">Seleccione el tipo de indicador</label>
                                    <select class="form-control dtic-input" id="idCategoriaIndicador"
                                            name="idCategoriaIndicador">
                                    </select>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="idUnidadIndicador">Seleccione la unidad</label>
                                    <select class="form-control dtic-input" id="idUnidadIndicador"
                                            name="idUnidadIndicador">
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row" style="display: flex; align-items: center">
                            <div class="col-8">
                                <div class="form-group">
                                    <label for="idAccionEstrategica">Seleccione la Accion estrategica</label>
                                    <select class="form-control dtic-input" id="idAccionEstrategica" name="idAccionEstrategica">
                                    </select>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="accionDescripcion">Descripcion</label>
                                    <input type="text" class="form-control input-sm txt dtic-input" id="accionDescripcion"
                                           name="accionDescripcion" placeholder="Descripcion de la accion" >
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
                    Indicadores Estratégicos Institucionales
                </div>
            </div>

            <div id="dticTableLoading" class="p-4">
                <div class="table-loading"></div>
                <div class="table-loading"></div>
                <div class="table-loading"></div>
            </div>

            <div class="p-2" id="dticTableContainer" style="display:none;">
                <table id="tablaListaIndicadoresEstrategicos" class="table w-100 dtic-table"></table>
            </div>

        </div>
    </div>
</div>

    <style>

        .kpi-circle {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: #64748b;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 14px;
            animation: kpiFade .6s ease;
        }

        .badge-result {
            background: #ffffff; /* <-- CAMBIADO: Fondo limpio */
            color: #61942e; /* <-- CAMBIADO: El texto toma el color verde para resaltar */
            border: 1.5px solid #8DBE5A; /* <-- CAMBIADO: El verde original pasa a ser el borde */
            border-radius: 20px;
            padding: 6px 16px;
            font-size: 13px;
            font-weight: 600;
            width: 140px;
            text-align: center;
        }


    </style>


<?php include_once "modalProgramarGestion.php"; ?>