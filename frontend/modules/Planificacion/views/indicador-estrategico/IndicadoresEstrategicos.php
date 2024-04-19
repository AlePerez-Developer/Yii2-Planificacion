<?php
use yii\web\JqueryAsset;
use yii\helpers\Html;

app\modules\Planificacion\assets\PlanificacionAsset::register($this);

$this->registerJsFile("@web/js/indicador-estrategico/IndicadorEstrategico.js",[
    'depends' => [
        JqueryAsset::className()
    ]
]);
$this->registerJsFile("@web/js/indicador-estrategico/IndicadorEstrategicoGestion.js",[
    'depends' => [
        JqueryAsset::className()
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
                    <div class="icon closed">
                        <div class="circle">
                            <div class="horizontal"></div>
                            <div class="vertical"></div>
                        </div>
                        Agregar Indicador
                    </div>
                </button>
            </div>
            <div class="col-6" style="text-align: right;">
                <?= Html::a('Reporte Ind. Estrategico', ['reporte'], ['class' => 'btn btn-success', 'target' => '_Blank']) ?>
            </div>
        </div>
    </div>
    <div id="IngresoDatos" class="card-body" style="display: none" >
        <div class="row justify-content-center">
            <div class="col-11">
                <div class="card">
                    <div class="card-header bg-gradient-primary">Ingreso Datos</div>
                    <div class="card-body">
                        <input type="text" id="codigoIndicadorEstrategico" name="codigoIndicadorEstrategico" disabled hidden >
                        <form id="formIndicadorEstrategico" action="" method="post">
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="codigoObjEstrategico">Seleccione el objetivo estrategico</label>
                                        <select class="form-control objEstrategico" id="codigoObjEstrategico" name="codigoObjEstrategico" >
                                            <option></option>
                                            <?php foreach ($objsEstrategicos as $objEstrategico){ ?>
                                                <option value="<?= $objEstrategico['CodigoObjEstrategico'] ?>"><?= '('.  $objEstrategico['CodigoObjetivo'] .') - ' . $objEstrategico['Objetivo']  ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-4">
                                    <div class="form-group">
                                        <label for="codigoIndicador">Codigo indicador</label>
                                        <input type="text" class="form-control input-sm num" id="codigoIndicador" name="codigoIndicador" maxlength="3"  placeholder="Codigo indicador" style="width: 150px" >
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group">
                                        <label for="metaIndicador">Meta indicador</label>
                                        <input type="text" class="form-control input-sm num" id="metaIndicador" name="metaIndicador"   placeholder="Meta del indicador" style="width: 150px" >
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
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="tipoResultado">Seleccione el resultado</label>
                                        <select class="form-control" id="tipoResultado" name="tipoResultado" >
                                            <option value="0" selected>Seleccione el resultado</option>
                                            <?php foreach ($Resultados as $Resultado){  ?>
                                                <option value="<?= $Resultado->CodigoTipo ?>"><?=$Resultado->Descripcion?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="tipoIndicador">Seleccione el tipo de indicador</label>
                                        <select class="form-control" id="tipoIndicador" name="tipoIndicador" >
                                            <option value="0" selected>Seleccione el tipo de indicador</option>
                                            <?php foreach ($Tipos as $Tipo){  ?>
                                                <option value="<?= $Tipo->CodigoTipo ?>"><?=$Tipo->Descripcion?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="categoriaIndicador">Seleccione la categoria</label>
                                        <select class="form-control" id="categoriaIndicador" name="categoriaIndicador" >
                                            <option value="0" selected>Seleccione la categoria</option>
                                            <?php foreach ($Categorias as $Categoria){  ?>
                                                <option value="<?= $Categoria->CodigoCategoria ?>"><?=$Categoria->Descripcion?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="tipoUnidad">Seleccione la unidad</label>
                                        <select class="form-control" id="tipoUnidad" name="tipoUnidad" >
                                            <option value="0" selected>Seleccione la unidad</option>
                                            <?php foreach ($Unidades as $Unidad){  ?>
                                                <option value="<?= $Unidad->CodigoTipo ?>"><?=$Unidad->Descripcion?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center">
                        <button id="btnGuardar" name="btnGuardar" class='btn btn-primary bg-gradient-primary'><span class='fa fa-check-circle'></span> Guardar </button>
                        <button id="btnCancelar" name="btnCancelar" class='btn btn-danger'><span class='fa fa-times-circle'></span> Cancelar </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="Divtabla" class="card-body">
        <table class="table-bordered table-striped dt-responsive table-sm tablaListaIndicadoresEstrategicos" style="width: 100%" >
            <thead>
                <th>#</th>
                <th>#</th>
                <th>Codigo</th>
                <th>Meta</th>
                <th>Descripcion</th>
                <th>Resultado</th>
                <th>Tipo</th>
                <th>Categoria</th>
                <th>Unidad</th>
                <th>Obj.</th>
                <th>Estado</th>
                <th>Prog.</th>
                <th>Acciones</th>
            </thead>
        </table>
    </div>
<style>
    .center {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100px;
        height: 100px;
    }
</style>
    <!-- Modal -->
    <div class="modal fade" id="programarIndicadorEstrategico" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5">Programacion de indicadores estrategicos</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="programacionGestion">
                        <div class="card">
                            <div class="card-header border-primary">
                                <div class="container text-center">
                                    <div class="row">
                                        <div class="col-12">
                                            <form class="form-floating">
                                                <input type="text" class="form-control" id="objetivoEstrategico" style="font-size: 12px" placeholder="" value="" disabled>
                                                <label for="objetivoEstrategico" style="font-size: 14px">Objetivo estrategico</label>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-2">
                                            <form class="form-floating">
                                                <input type="text" class="form-control" id="codigoIndicadorModal" style="font-size: 12px" placeholder="" value="" disabled>
                                                <label for="codigoIndicadorModal" style="font-size: 14px">Codigo del indicador</label>
                                            </form>
                                        </div>
                                        <div class="col-2">
                                            <form class="form-floating">
                                                <input type="text" class="form-control" id="metaIndicadorModal" style="font-size: 12px" placeholder="" value="" disabled>
                                                <label for="metaIndicadorModal" style="font-size: 14px">Meta del indicador</label>
                                            </form>
                                        </div>
                                        <div class="col-8">
                                            <form class="form-floating">
                                                <input type="text" class="form-control" id="descripcionIndicador" style="font-size: 12px" placeholder="" value="" disabled>
                                                <label for="descripcionIndicador" style="font-size: 14px">Descripcion del indicador</label>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <table id="osolala" class="table-bordered table-striped dt-responsive table-sm tablaIndicadoresGestion" style="width: 100%" >
                                    <thead>
                                    <th>#</th>
                                    <th>Gestion</th>
                                    <th>Meta</th>
                                    <th>Indicador</th>
                                    <th>Acciones</th>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div id="programacionGestion" style="display: none">

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="cerrarModal" class='btn btn-outline-danger' data-bs-dismiss="modal"><span class='fa fa-times-circle'></span> Cerrar </button>
                </div>
            </div>
        </div>
    </div>

</div>
