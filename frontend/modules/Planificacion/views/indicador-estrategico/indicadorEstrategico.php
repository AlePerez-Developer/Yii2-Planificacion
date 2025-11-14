<?php
use yii\web\JqueryAsset;
use yii\helpers\Html;

app\modules\Planificacion\assets\PlanificacionAsset::register($this);


$this->registerJsFile("@planificacionModule/js/indicador-estrategico/dt-declaration.js",[
    'depends' => [
        JqueryAsset::class
    ]
]);

$this->title = 'Planificacion';
$this->params['breadcrumbs'] = [['label' => '/Ind. Estrategicos']];
?>

<div class="card ">
    <div class="card-header">
        <div class="row">
            <div class="col-6">
                <button id="btnMostrarCrear" name="btnMostrarCrear" class="btn btn-primary bg-gradient-primary">
                    <span class="icon">
                        <span class="circle">
                            <span class="horizontal"></span>
                            <span class="vertical"></span>
                        </span>
                        Agregar Indicador
                    </span>
                </button>
            </div>
            <div class="col-6" style="text-align: right;">
                <?= Html::a('Reporte Ind. Estrategico', ['reporte'], ['class' => 'btn btn-success', 'target' => '_Blank']) ?>
            </div>
        </div>
    </div>
    <div id="divDatos" class="card-body" style="display: none" >
        <div class="row justify-content-center">
            <div class="col-11">
                <div class="card">
                    <div class="card-header bg-gradient-primary">Ingreso Datos</div>
                    <div class="card-body">
                        <form id="formIndicadorEstrategico" action="" method="post">
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="idObjEstrategico">Seleccione el objetivo estrategico</label>
                                        <select class="form-control objEstrategico" id="idObjEstrategico" name="idObjEstrategico" >
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-4">
                                    <div class="form-group">
                                        <label for="codigo">Codigo indicador</label>
                                        <input type="text" class="form-control input-sm num" id="codigo" name="codigo" maxlength="3"  placeholder="Codigo indicador" style="width: 150px" >
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group">
                                        <label for="meta">Meta indicador</label>
                                        <input type="text" class="form-control input-sm num" id="meta" name="meta"   placeholder="Meta del indicador" style="width: 150px" >
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group">
                                        <label for="lineaBase">Linea base</label>
                                        <input type="text" class="form-control input-sm num" id="lineaBase" name="lineaBase"   placeholder="Meta del indicador" style="width: 150px" >
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group">
                                    <label for="descripcion" class="control-label">Descripcion del indicador</label>
                                    <textarea class="form-control input-sm txt" id="descripcion" name="descripcion" rows="4" placeholder="Descripcion del indicador"></textarea>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-4">
                                    <div class="form-group">
                                        <label for="idTipoResultado">Seleccione el resultado</label>
                                        <select class="form-control" id="idTipoResultado" name="idTipoResultado" >
                                        </select>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group">
                                        <label for="idCategoriaIndicador">Seleccione el tipo de indicador</label>
                                        <select class="form-control" id="idCategoriaIndicador" name="idCategoriaIndicador" >
                                        </select>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group">
                                        <label for="idUnidadIndicador">Seleccione la unidad</label>
                                        <select class="form-control" id="idUnidadIndicador" name="idUnidadIndicador" >
                                        </select>
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
    </div>
    <div id="divTabla" class="card-body">
        <table id="tablaListaIndicadoresEstrategicos" name="tablaListaIndicadoresEstrategicos" class="table table-bordered table-striped">
            <thead>
            <th>#</th>
            <th>Codigo.</th>
            <th>Meta</th>
            <th>Descripcion</th>
            <th>LineaBase</th>
            <th>Resultado</th>
            <th>Categoria</th>
            <th>Unidad</th>
            <th>Estado</th>
            <th>Acciones</th>
            </thead>
        </table>
    </div>
</div>

<style>
    .childtitulosmall {
        font-size: 11px;
        font-weight: normal; !important;
    }
    td[colspan] {
        padding: 0 5px .75rem 5px;
    }

    .subDetail {
        border-collapse: collapse;
        border-color:#ccc;
        border-spacing:0;
        font-size: 10px;
        width: 100%;
    }

    .tg  {border-collapse:collapse;border-color:#ccc;border-spacing:0;}
    .tg td{background-color:#fff;border-color:#ccc;border-style:solid;border-width:1px;color:#333;
        font-family:Arial, sans-serif;font-size:14px;overflow:hidden;padding:10px 5px;word-break:normal;}
    .tg th{background-color:#f0f0f0;border-color:#ccc;border-style:solid;border-width:1px;color:#333;
        font-family:Arial, sans-serif;font-size:14px;font-weight:normal;overflow:hidden;padding:10px 5px;word-break:normal;}
    .tg .tg-b91a{background-color:#f9f9f9;font-size:10px;text-align:left;vertical-align:top}
    .tg .tg-l64y{font-size:10px;font-weight:bold;text-align:left;vertical-align:top}
    .tg .tg-3j8g{font-size:10px;font-weight:bold;text-align:center;vertical-align:top}
    .tg .tg-fqa1{background-color:#f9f9f9;font-size:10px; !important; text-align:right;vertical-align:top}
</style>




<?php include_once "modalProgramarGestion.php"; ?>