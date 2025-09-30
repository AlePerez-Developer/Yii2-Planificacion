<?php

use yii\web\JqueryAsset;

app\modules\Planificacion\assets\PlanificacionAsset::register($this);

$this->registerJsFile("@web/js/proyecto/Proyecto.js", [
    'depends' => [
        JqueryAsset::className()
    ]
]);
$this->title = 'Planificacion';
$this->params['breadcrumbs'] = [['label' => 'Proyectos']];
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
    <div id="divDatos" class="card-body">
        <div class="col d-flex justify-content-center">
            <div class="card " style="width: 50rem;">
                <div class="card-header bg-gradient-primary">Ingreso Datos</div>
                <div class="card-body">
                    <input type="text" id="codigo" name="codigo" disabled hidden>
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
                            <input id="Codigo" name="Codigo" placeholder="Codigo" style="width: 240px" class="form-control input-lg num">
                        </div>

                        <div class="form-group">
                            <label for="Descripcion">Descripcion</label>
                            <textarea class="form-control input-sm txt" rows="4" id="Descripcion" name="Descripcion" placeholder="Descripcion"></textarea>
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
        <table id="tablaListaProyectos" name="tablaListaProyectos" class="table table-bordered table-striped dt-responsive"
               style="width: 100%">
            <thead>
            <th style="text-align: center; vertical-align: middle;">#</th>
            <th style="text-align: center; vertical-align: middle;">Programa</th>
            <th style="text-align: center; vertical-align: middle;">Codigo</th>
            <th style="text-align: center; vertical-align: middle;">Descripcion</th>
            <th style="text-align: center; vertical-align: middle;">Estado</th>
            <th style="text-align: center; vertical-align: middle;">Acciones</th>
            </thead>
        </table>
    </div>

</div>


