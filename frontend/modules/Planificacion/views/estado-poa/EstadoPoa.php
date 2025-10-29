<?php

use yii\web\JqueryAsset;

app\modules\Planificacion\assets\PlanificacionAsset::register($this);

$this->registerJsFile("@planificacionModule/js/estado-poa/estadoPoa.js", [
    'depends' => [
        JqueryAsset::className(),
    ],
]);

$this->title = 'Planificacion';
$this->params['breadcrumbs'] = [['label' => '/Estados POA']];
?>

<div class="card">
    <div class="card-header">
        <button id="btnMostrarCrear" class="btn btn-primary bg-gradient-primary">
            <div class="icon closed">
                <div class="circle">
                    <div class="horizontal"></div>
                    <div class="vertical"></div>
                </div>
                Agregar Estado POA
            </div>
        </button>
    </div>
    <div id="divDatos" class="card-body" style="display: none">
        <div class="col d-flex justify-content-center">
            <div class="card" style="width: 40rem;">
                <div class="card-header bg-gradient-primary">Ingreso Datos</div>
                <div class="card-body">
                    <input type="text" id="codigoEstadoPoa" name="codigoEstadoPoa" disabled hidden>
                    <form id="formEstadoPoa" method="post">
                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <textarea class="form-control input-sm txt" rows="3" id="descripcion" name="descripcion" placeholder="Descripción"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="abreviacion">Abreviación</label>
                            <input type="text" id="abreviacion" name="abreviacion" class="form-control" maxlength="3" placeholder="Abreviación">
                        </div>
                        <div class="form-group">
                            <label for="etapaActual">Etapa actual</label>
                            <input type="number" id="etapaActual" name="etapaActual" class="form-control" min="0" placeholder="Etapa actual">
                        </div>
                        <div class="form-group">
                            <label for="etapaPredeterminada">Etapa predeterminada</label>
                            <input type="number" id="etapaPredeterminada" name="etapaPredeterminada" class="form-control" min="0" placeholder="Etapa predeterminada">
                        </div>
                        <div class="form-group">
                            <label for="orden">Orden</label>
                            <input type="number" id="orden" name="orden" class="form-control" min="0" placeholder="Orden">
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <button id="btnGuardar" name="btnGuardar" class="btn btn-primary bg-gradient-primary"><span class="fa fa-check-circle"></span> Guardar </button>
                    <button id="btnCancelar" name="btnCancelar" class="btn btn-danger"><span class="fa fa-times-circle"></span> Cancelar </button>
                </div>
            </div>
        </div>
    </div>
    <div id="divTabla" class="card-body overflow-auto">
        <table id="tablaEstadosPoa" class="table table-bordered table-striped">
            <thead>
                <th>#</th>
                <th>Descripción</th>
                <th>Abreviación</th>
                <th>Etapa actual</th>
                <th>Etapa predeterminada</th>
                <th>Orden</th>
                <th>Estado</th>
                <th>Acciones</th>
            </thead>
        </table>
    </div>
</div>
