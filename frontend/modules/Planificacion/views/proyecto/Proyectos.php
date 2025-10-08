<?php

use yii\web\JqueryAsset;

app\modules\Planificacion\assets\PlanificacionAsset::register($this);

$this->registerJsFile("@planificacionModule/js/Proyecto/proyecto.js", [
    'depends' => [
        JqueryAsset::className()
    ]
]);
$this->title = 'Planificacion';
$this->params['breadcrumbs'] = [['label' => '/Proyectos']];
?>

<div class="card ">
    <div class="card-header">
        <button id="btnMostrarCrear" class="btn btn-primary bg-gradient-primary">
            <div class="icon closed">
                <div class="circle">
                    <div class="horizontal"></div>
                    <div class="vertical"></div>
                </div>
                Agregar Proyecto
            </div>
        </button>
    </div>
    <div id="ingresoDatos" class="card-body">
        <div class="col d-flex justify-content-center">
            <div class="card " style="width: 50rem;">
                <div class="card-header bg-gradient-primary">Ingreso Datos</div>
                <div class="card-body">
                    <input type="text" id="codigoProyecto" name="codigoProyecto" disabled hidden>
                    <form id="formProyectos" action="" method="post">

                        <div class="form-group">
                            <label for="codigoPrograma">Seleccione un programa</label>
                            <select class="form-control programa" id="codigoPrograma" name="codigoPrograma" >
                                <option></option>
                                <?php foreach ($programas as $programa){ ?>
                                    <option value="<?= $programa->CodigoPrograma ?>"><?= '('.  $programa->Codigo .') - ' . $programa->Descripcion  ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="Codigo" class="control-label">Codigo proyecto</label>
                            <input id="Codigo" name="codigo" placeholder="codigo" style="width: 240px" class="form-control input-lg num">
                        </div>

                        <div class="form-group">
                            <label for="Descripcion">Descripcion</label>
                            <textarea class="form-control input-sm txt" rows="4" id="Descripcion" name="descripcion" placeholder="descripcion"></textarea>
                        </div>

                    </form>
                </div>
                <div class="card-footer text-center">
                    <button id="btnGuardar" name="btnGuardar" class='btn btn-primary bg-gradient-primary'><i class='fa fa-check-circle-o'>Guardar</i>
                    </button>
                    <button id="btnCancelar" name="btnCancelar" class='btn btn-danger'><i class='fa fa-warning'>Cancelar</i></button>
                </div>
            </div>
        </div>
    </div>
    <div id="divTabla" name="divTabla" class="card-body">
        <table id="tablaListaProyectos" name="tablaListaProyectos" class="table table-bordered table-striped dt-responsive">
            <thead>
            <th >#</th>
            <th >Programa</th>
            <th >Codigo</th>
            <th >Descripcion</th>
            <th >Estado</th>
            <th >Acciones</th>
            </thead>
        </table>
    </div>

</div>


