<?php
use yii\web\JqueryAsset;

app\modules\Planificacion\assets\PlanificacionAsset::register($this);

$this->registerJsFile("@planificacionModule/js/operacion/Operacion.js",[
    'depends' => [
        JqueryAsset::className()
    ]
]);
$this->title = 'Planificacion';
$this->params['breadcrumbs'] = [['label' => 'Operaciones']];
?>

<div class="card ">
    <div class="card-header">
        <button id="btnMostrarCrearOpe" class="btn btn-primary bg-gradient-primary" >
            <div class="icon closed">
                <div class="circle">
                    <div class="horizontal"></div>
                    <div class="vertical"></div>
                </div>
                Agregar Operacion
            </div>
        </button>
    </div>
    <div id="IngresoDatos" class="card-body" >
        <div class="col d-flex justify-content-center">
            <div class="card " style="width: 100%;" >
                <div class="card-header bg-gradient-primary">Ingreso Datos</div>
                <div class="card-body">
                    <input type="text" id="codigo" name="codigo" disabled hidden >
                    <form id="formoperacion" action="" method="post">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="codigoObjEstrategico">Seleccione el objetivo estrategico</label>
                                    <select class="form-control" id="codigoObjEstrategico" name="codigoObjEstrategico" >
                                        <option></option>
                                        <?php foreach ($objsEstrategicos as $objEstrategico){  ?>
                                            <option value="<?= $objEstrategico->CodigoObjEstrategico ?>"><?= '('.  $objEstrategico->CodigoObjetivo .') - ' . $objEstrategico->Objetivo  ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="codigoObjInstitucional">Seleccione el objetivo institucional</label>
                                    <select class="form-control" id="codigoObjInstitucional" name="codigoObjInstitucional" disabled >
                                        <option></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="codigoObjEspecifico">Seleccione el objetivo especifico</label>
                                    <select class="form-control" id="codigoObjEspecifico" name="codigoObjEspecifico" disabled >
                                        <option></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="codigoPrograma">Seleccione el Programa</label>
                                    <select class="form-control" id="codigoPrograma" name="codigoPrograma" >
                                        <option></option>
                                        <?php foreach ($programas as $programa){  ?>
                                            <option value="<?= $programa->CodigoPrograma ?>"><?= '('.  $programa->Codigo .') - ' . $programa->Descripcion  ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="codigoProyecto">Seleccione un proyecto</label>
                                    <select class="form-control" id="codigoProyecto" name="codigoProyecto" disabled >
                                        <option></option>
                                    </select>
                                </div> 
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="codigoActividad">Seleccione una actividad</label>
                                    <select class="form-control" id="codigoActividad" name="codigoActividad" disabled >
                                        <option></option>
                                    </select>
                                </div>
                            </div>
                        </div>






                        <div class="form-group">
                            <label for="CodigoCOGE">Codigo de Objetivo de Gestion Especifico (OGE)</label>
                            <input type="text" class="form-control input-sm num" id="CodigoCOGE" name="CodigoCOGE" maxlength="2"  placeholder="Codigo" style="width: 80px" >
                        </div>
                        <div class="form-group">
                            <label for="Objetivo" class="control-label">Descripcion del objetivo especifico</label>
                            <textarea class="form-control input-sm txt" id="Objetivo" name="Objetivo" rows="4" placeholder="Descripcion del objetivo especifico"></textarea>
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
        <table class="table table-bordered table-striped dt-responsive tablaListaObjEspecificos" style="width: 100%" >
            <thead>
            <th style="text-align: center; vertical-align: middle;">#</th>
            <th style="text-align: center; vertical-align: middle;">#</th>
            <th style="text-align: center; vertical-align: middle;">PEI</th>
            <th style="text-align: center; vertical-align: middle;">Codigo</th>
            <th style="text-align: center; vertical-align: middle;">Objetivo</th>
            <th style="text-align: center; vertical-align: middle;">Estado</th>
            <th style="text-align: center; vertical-align: middle;">Acciones</th>
            </thead>
        </table>
    </div>
</div>

