<?php
use yii\web\JqueryAsset;

app\modules\Planificacion\assets\PlanificacionAsset::register($this);

$this->registerJsFile("@web/js/obj-especifico/ObjEspecifico.js",[
    'depends' => [
        JqueryAsset::className()
    ]
]);
$this->title = 'Planificacion';
$this->params['breadcrumbs'] = [['label' => 'Objs Especificos']];
?>

<div class="card ">
    <div class="card-header">
        <button id="btnMostrarCrearObj" class="btn btn-primary bg-gradient-primary" >
            <div class="icon closed">
                <div class="circle">
                    <div class="horizontal"></div>
                    <div class="vertical"></div>
                </div>
                Agregar Obj. Especifico
            </div>
        </button>
    </div>
    <div id="IngresoDatos" class="card-body" >
        <div class="col d-flex justify-content-center">
            <div class="card " style="width: 40rem;" >
                <div class="card-header bg-gradient-primary">Ingreso Datos</div>
                <div class="card-body">
                    <input type="text" id="codigo" name="codigo" disabled hidden >
                    <form id="formobjespecifico" action="" method="post">

                        <div class="form-group">
                            <label for="CodigoObjEstrategico">Seleccione el objetivo estrategico</label>
                            <select class="form-control objestrategicos" id="CodigoObjEstrategico" name="CodigoObjEstrategico" >
                                <option></option>
                                <?php foreach ($objsEstrategicos as $objEstrategico){  ?>
                                    <option value="<?= $objEstrategico->CodigoObjEstrategico ?>"><?= '('.  $objEstrategico->CodigoCOGE .') - ' . $objEstrategico->Objetivo  ?></option>
                                <?php } ?>
                            </select>
                        </div>


                        <div class="form-group">
                            <label for="CodigoObjInstitucional">Seleccione el objetivo institucional</label>
                            <select class="form-control objinstitucional" id="CodigoObjInstitucional" name="CodigoObjInstitucional" disabled >
                                <option></option>
                            </select>
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

