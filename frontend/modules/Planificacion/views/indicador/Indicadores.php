<?php
use yii\web\JqueryAsset;

app\modules\Planificacion\assets\PlanificacionAsset::register($this);

$this->registerJsFile("@web/js/indicador/Indicador.js",[
    'depends' => [
        JqueryAsset::className()
    ]
]);
$this->title = 'Planificacion';
$this->params['breadcrumbs'] = [['label' => 'Indicadores']];
?>

<div class="card ">
    <div class="card-header">
        <button id="btnMostrarCrearIndicador" class="btn btn-primary bg-gradient-primary" >
            <div class="icon closed">
                <div class="circle">
                    <div class="horizontal"></div>
                    <div class="vertical"></div>
                </div>
                Agregar Indicador
            </div>
        </button>
    </div>
    <div id="IngresoDatos" class="card-body" >
        <div class="col d-flex justify-content-center">
            <div class="card " style="width: 40rem;" >
                <div class="card-header bg-gradient-primary">Ingreso Datos</div>
                <div class="card-body">
                    <input type="text" id="codigo" name="codigo" disabled hidden >
                    <form id="formindicador" action="" method="post">
                        <div class="form-group">
                            <label for="Codigo">Codigo indicador</label>
                            <input type="text" class="form-control input-sm num" id="Codigo" name="Codigo" maxlength="4"  placeholder="Codigo" style="width: 120px" >
                        </div>
                        <div class="form-group">
                            <label for="Descripcion" class="control-label">Descripcion del indicador</label>
                            <textarea class="form-control input-sm txt" id="Descripcion" name="Descripcion" rows="4" placeholder="Descripcion del indicador"></textarea>
                        </div>

                        <div class="container">
                            <div class="row">
                                <div class="col-4">
                                    <div class="form-group">
                                        <label for="Articulacion">Seleccione la Articulacion</label>
                                        <select class="form-control" id="Articulacion" name="Articulacion" >
                                            <option value="0" selected>Seleccione la articulacion</option>
                                            <?php foreach ($Articulaciones as $Articulacion){  ?>
                                                <option value="<?= $Articulacion->CodigoTipo ?>"><?=$Articulacion->Descripcion?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group">
                                        <label for="Resultado">Seleccione el resultado</label>
                                        <select class="form-control" id="Resultado" name="Resultado" >
                                            <option value="0" selected>Seleccione el resultado</option>
                                            <?php foreach ($Resultados as $Resultado){  ?>
                                                <option value="<?= $Resultado->CodigoTipo ?>"><?=$Resultado->Descripcion?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group">
                                        <label for="Tipo">Seleccione el tipo de indicador</label>
                                        <select class="form-control" id="Tipo" name="Tipo" >
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
                                        <label for="Categoria">Seleccione la categoria</label>
                                        <select class="form-control" id="Categoria" name="Categoria" >
                                            <option value="0" selected>Seleccione la categoria</option>
                                            <?php foreach ($Categorias as $Categoria){  ?>
                                                <option value="<?= $Categoria->CodigoCategoria ?>"><?=$Categoria->Descripcion?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="Unidad">Seleccione la unidad</label>
                                        <select class="form-control" id="Unidad" name="Unidad" >
                                            <option value="0" selected>Seleccione la unidad</option>
                                            <?php foreach ($Unidades as $Unidad){  ?>
                                                <option value="<?= $Unidad->CodigoTipo ?>"><?=$Unidad->Descripcion?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <button class='btn btn-primary bg-gradient-primary btnGuardar'><i class='fa fa-check-circle-o'>Guardar</i></button>
                    <button class='btn btn-danger btn- btnCancel'><i class='fa fa-warning'>Cancelar</i></button>
                </div>
            </div>
        </div>
    </div>
    <div id="Divtabla" class="card-body">
        <table class="table table-bordered table-striped dt-responsive tablaListaIndicadores" style="width: 100%" >
            <thead>
                <th>#</th>
                <th>Codigo</th>
                <th>Descripcion</th>
                <th>Articulacion</th>
                <th>Resultado</th>
                <th>Tipo</th>
                <th>Categoria</th>
                <th>Unidad</th>
                <th>Estado</th>
                <th>Acciones</th>
            </thead>
        </table>
    </div>
</div>
