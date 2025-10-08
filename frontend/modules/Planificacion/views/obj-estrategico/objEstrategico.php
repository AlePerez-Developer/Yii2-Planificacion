<?php
use yii\helpers\Html;
use yii\web\JqueryAsset;

app\modules\Planificacion\assets\PlanificacionAsset::register($this);

$this->registerJsFile("@planificacionModule/js/obj-estrategico/ObjEstrategico.js",[
    'depends' => [
        JqueryAsset::class
    ]
]);

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

$this->title = 'Planificacion';
$this->params['breadcrumbs'] = [['label' => '/Objs Estrategicos']];
?>

<div class="card ">
    <div class="card-header">
        <div class="row">
            <div class="col-6">
                <button id="btnMostrarCrear" class="btn btn-primary bg-gradient-primary">
                    <div class="icon closed">
                        <div class="circle">
                            <div class="horizontal"></div>
                            <div class="vertical"></div>
                        </div>
                        Agregar Obj. Estrategico
                    </div>
                </button>
            </div>
            <div class="col-6" style="text-align: right;">
                <?= Html::a('Reporte Obj Estrategico', ['reporte'], ['class' => 'btn btn-success', 'target' => '_Blank']) ?>
            </div>
        </div>
    </div>
    <div id="divDatos" class="card-body" style="display: none">
        <div class="col d-flex justify-content-center">
            <div class="card " style="width: 80rem;" >
                <div class="card-header bg-gradient-primary">Ingreso Datos</div>
                <div class="card-body">
                    <form id="formObjEstrategico" action="" method="post">

                        <div class="row mb-3">
                            <div class="col-4">
                                <label for="areasEstrategicas" class="lblTitulo">Seleccione una Area Estrategica</label>
                                <select id="areasEstrategicas" name="areasEstrategicas" class="form-control">
                                </select>
                            </div>
                            <div class="col-4">
                                <label for="politicasEstrategicas" class="lblTitulo">Seleccione una Politica Estrategica</label>
                                <select id="politicasEstrategicas" name="politicasEstrategicas" class="form-control">
                                </select>
                            </div>
                            <div class="col-4">
                                <label for="codigoObjetivo">Codigo de Objetivo Estrategico (OE)</label>
                                <input type="text" class="form-control input-sm num" id="codigoObjetivo" name="codigoObjetivo" maxlength="1"  placeholder="Codigo" style="width: 100px"  >
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
        <table id="tablaListaObjEstrategicos" name="tablaListaObjEstrategicos" class="table table-bordered table-striped">
            <thead>
            <th>#</th>
            <th>#</th>
            <th>PEI</th>
            <th>Codigo</th>
            <th>Objetivo</th>
            <th>Producto Esperado</th>
            <th>Ind. Descripcion</th>
            <th>Ind. Formula</th>
            <th>Estado</th>
            <th>Acciones</th>
            </thead>
        </table>
    </div>
</div>


<style>
    :root {
        --border-color: #007BFF; /* azul */
        --border-width: 1px;
        --padding: 14px;
    }

    .contenedor {
        width: 100%; !important;
        position: relative;
        display: inline-block;
    }

    .square {
        width: auto;
        height: auto;
        border: var(--border-width) solid var(--border-color);
        border-radius: 8px;
        padding: var(--padding);
        box-sizing: border-box;
        gap: 10px;
    }

    .ltc {
        position: absolute;
        top: -0.8em;       /* sube el texto para que corte la línea */
        left: 16px;        /* margen interno desde la izquierda */
        background: white; /* fondo blanco para "romper" el borde */
        padding: 0 8px;    /* espacio horizontal */
        color: var(--border-color);
        font-weight: 600;
        font-size: 1rem;
        border-radius: 4px;
    }

    .ltc {
        margin-bottom: 6px;
    }

    .mio {
        color: green;
    }

    .tuyo {
        color: red;
    }
</style>