<?php

use yii\web\JqueryAsset;

app\modules\Planificacion\assets\PlanificacionAsset::register($this);

$this->registerJsFile("@web/js/peis/Peis.js", [
    'depends' => [
        JqueryAsset::className()
    ]
]);
$this->title = 'Planificacion';
$this->params['breadcrumbs'] = [['label' => 'Peis']];
?>

<div class="card ">
    <div class="card-header">
        <button id="btnMostrarCrear" class="btn btn-primary bg-gradient-primary">
            <div class="icon closed">
                <div class="circle">
                    <div class="horizontal"></div>
                    <div class="vertical"></div>
                </div>
                Agregar Pei
            </div>
        </button>
    </div>
    <div id="ingresoDatos" class="card-body">
        <div class="col d-flex justify-content-center">
            <div class="card " style="width: 40rem;">
                <div class="card-header bg-gradient-primary">Ingreso Datos</div>
                <div class="card-body">
                    <input type="text" id="codigoPei" name="codigo" disabled hidden>
                    <form id="formPei" action="" method="post">
                        <div class="form-group">
                            <label for="descripcionPei" class="control-label">Descripcion del pei</label>
                            <input type="text" class="form-control input-sm txt" id="descripcionPei"
                                   name="descripcionPei" placeholder="descripcion del pei">
                        </div>
                        <div class="form-group">
                            <label for="fechaAprobacion" class="control-label">Fecha de aprobacion</label>
                            <input type="date" class="form-control input-sm" id="fechaAprobacion" name="fechaAprobacion"
                                   pattern="\d{4}-\d{2}-\d{2}">
                        </div>
                        <div class="form-group">
                            <label for="gestionInicio">Gestion de inicio</label>
                            <input type="text" class="form-control input-sm num" id="gestionInicio" name="gestionInicio"
                                   placeholder="Gestion de inicio">
                        </div>
                        <div class="form-group">
                            <label for="gestionFin">Gestion Final</label>
                            <input type="text" class="form-control input-sm num" id="gestionFin" name="gestionFin"
                                   placeholder="Gestion final">
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
        <table id="tablaListaPeis" name="tablaListaPeis" class="table table-bordered table-striped dt-responsive">
            <thead>
            <th>#</th>
            <th>Descripcion</th>
            <th>Fecha de Aprobacion</th>
            <th>Gestion Inicio</th>
            <th>Gestion Fin</th>
            <th>Estado</th>
            <th>Acciones</th>
            </thead>
        </table>
    </div>
</div>
