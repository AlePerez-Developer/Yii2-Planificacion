<?php

use yii\web\JqueryAsset;

app\modules\Planificacion\assets\PlanificacionAsset::register($this);

$this->registerJsFile("@planificacionModule/js/programa/Programa.js", [
    'depends' => [
        JqueryAsset::class
    ]
]);
$this->title = 'Planificacion';
$this->params['breadcrumbs'] = [['label' => '/Programas']];
?>

<div class="card ">
    <div class="card-header">
        <button id="btnMostrarCrear" class="btn btn-primary bg-gradient-primary">
            <div class="icon closed">
                <div class="circle">
                    <div class="horizontal"></div>
                    <div class="vertical"></div>
                </div>
                Agregar Programa
            </div>
        </button>
    </div>
    <div id="divDatos" class="card-body" style="display: none">
        <div class="col d-flex justify-content-center">
            <div class="card " style="width: 40rem;">
                <div class="card-header bg-gradient-primary">Ingreso Datos</div>
                <div class="card-body">
                    <input type="text" id="codigoPrograma" name="codigoPrograma" disabled hidden>
                    <form id="formPrograma" action="" method="post">

                        <div class="form-group">
                            <label for="codigo" class="control-label">Codigo de programa</label>
                            <input type="text" id="codigo" name="codigo" class="form-control num" maxlength="3"  placeholder="Codigo" style="width: 120px"4>
                        </div>

                        <div class="form-group">
                            <label for="descripcion">Descripcion del programa</label>
                            <textarea class="form-control input-sm txt" rows="3" id="descripcion" name="descripcion" placeholder="Descripcion"></textarea>
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
    <div id="divTabla" name="divTabla" class="card-body overflow-auto">
        <table id="tablaListaProgramas" name="tablaListaActividades" class="table table-bordered table-striped">
            <thead>
            <th>#</th>
            <th>Codigo</th>
            <th>Descripcion</th>
            <th>Estado</th>
            <th>Acciones</th>
            </thead>
        </table>
    </div>
</div>




