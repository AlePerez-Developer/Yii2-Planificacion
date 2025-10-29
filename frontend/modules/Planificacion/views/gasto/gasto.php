<?php

use yii\web\JqueryAsset;

app\modules\Planificacion\assets\PlanificacionAsset::register($this);

$this->registerJsFile("@planificacionModule/js/gasto/Gasto.js", [
    'depends' => [
        JqueryAsset::className()
    ]
]);


$this->title = 'Planificacion';
$this->params['breadcrumbs'] = [['label' => '/Gastos']];
?>

<div class="card ">
    <div class="card-header">
        <button id="btnMostrarCrear" class="btn btn-primary bg-gradient-primary">
            <div class="icon closed">
                <div class="circle">
                    <div class="horizontal"></div>
                    <div class="vertical"></div>
                </div>
                Agregar Gasto
            </div>
        </button>
    </div>
    <div id="divDatos" class="card-body" style="display: none">
        <div class="col d-flex justify-content-center">
            <div class="card " style="width: 40rem;">
                <div class="card-header bg-gradient-primary">Ingreso Datos</div>
                <div class="card-body">
                    <input type="text" id="codigoGasto" name="codigoGasto" disabled hidden>
                    <form id="formGasto" action="" method="post">
                        <div class="form-group">
                            <label for="descripcion">Descripcion del gasto</label>
                            <textarea class="form-control input-sm txt" rows="3" id="descripcion" name="descripcion" placeholder="Descripcion"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="entidadTransferencia" class="control-label">Entidad Transferencia</label>
                            <input type="text" id="entidadTransferencia" name="entidadTransferencia" class="form-control" maxlength="5" placeholder="Entidad Transferencia" style="width: 120px">
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
        <table id="tablaListaGastos" name="tablaListaGastos" class="table table-bordered table-striped">
            <thead>
                <th>#</th>
                <th>Descripcion</th>
                <th>Entidad Transferencia</th>
                <th>Estado</th>
                <th>Acciones</th>
            </thead>
        </table>
    </div>
</div>