<?php
use yii\web\JqueryAsset;
use yii\bootstrap5\Modal;
use yii\helpers\Url;

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
        <div class="row justify-content-center">
            <div class="col-11">
                <div class="card">
                    <div class="card-header bg-gradient-primary">Ingreso Datos</div>
                    <div class="card-body">
                        <input type="text" id="codigo" name="codigo" disabled hidden >
                        <form id="formIndicadores" action="" method="post">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="CodigoObjInstitucional">Seleccione el objetivo institucional</label>
                                        <select class="form-control objinstitucional" id="CodigoObjInstitucional" name="CodigoObjInstitucional" >
                                            <option></option>
                                            <?php foreach ($objInsitucionales as $objinstitucional){ ?>
                                                <option value="<?= $objinstitucional['CodigoObjInstitucional'] ?>"><?= '('.  $objinstitucional['Codigo'] .') - ' . $objinstitucional['Objetivo']  ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="CodigoObjEspecifico">Seleccione el objetivo especifico</label>
                                        <select class="form-control objespecifico" id="CodigoObjEspecifico" name="CodigoObjEspecifico" disabled >
                                            <option></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="CodigoPrograma">Seleccione el programa</label>
                                        <select class="form-control programa" id="CodigoPrograma" name="CodigoPrograma" >
                                            <option></option>
                                            <?php foreach ($programas as $programa){ ?>
                                                <option value="<?= $programa->CodigoPrograma ?>"><?= '('.  $programa->Codigo .') - ' . $programa->Descripcion  ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="CodigoActividad">Seleccione la actividad</label>
                                        <select class="form-control actividad" id="CodigoActividad" name="CodigoActividad" disabled >
                                            <option></option>
                                        </select>
                                    </div>

                                </div>
                            </div>
                            <div class="row">
                                <div class="col-4">
                                    <div class="form-group">
                                        <label for="CodigoPei">Codigo indicador PEI</label>
                                        <input type="text" class="form-control input-sm num" id="CodigoPei" name="CodigoPei" maxlength="3"  placeholder="Codigo Pei" style="width: 120px" >
                                    </div>
                                </div>

                                <div class="col-4">
                                    <div class="form-group">
                                        <label for="CodigoPoa">Codigo indicador POA</label>
                                        <input type="text" class="form-control input-sm num" id="CodigoPoa" name="CodigoPoa" maxlength="3"  placeholder="Codigo Poa" style="width: 120px" >
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group">
                                    <label for="Descripcion" class="control-label">Descripcion del indicador</label>
                                    <textarea class="form-control input-sm txt" id="Descripcion" name="Descripcion" rows="4" placeholder="Descripcion del indicador"></textarea>
                                </div>
                            </div>
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
                        </form>
                    </div>
                    <div class="card-footer text-center">
                        <button class='btn btn-primary bg-gradient-primary btnGuardar'><i class='fa fa-check-circle-o'>Guardar</i></button>
                        <button class='btn btn-danger btn- btnCancel'><i class='fa fa-warning'>Cancelar</i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="Divtabla" class="card-body">
        <table class="table table-bordered table-striped dt-responsive tablaListaIndicadores" style="width: 100%" >
            <thead>
                <th>#</th>
                <th>#</th>
                <th>Codigo Pei</th>
                <th>Codigo Poa</th>
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


    <?php
    Modal::begin([
        'id' => 'indicadoresUnidades',
        'title' => '<h4>Registro de unidades</h4>',
        'footer' => '
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal" > Cerrar </button>
            </div>
        ',
        'clientOptions' => ['backdrop' => 'static', 'keyboard' => false],
        'size' => 'modal-xl',
        'class' => 'modal-dialog-centered',

    ]);?>

        <div class="card mt-3">
            <?= $this->render('IndicadoresUnidades',[]) ?>
        </div>


    <?php
    Modal::end();
    ?>



</div>
