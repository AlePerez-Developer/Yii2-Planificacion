<?php
use yii\web\JqueryAsset;

app\modules\Planificacion\assets\PlanificacionAsset::register($this);

$this->registerJsFile("@web/js/cargos/cargos.js",[
    'depends' => [
        JqueryAsset::className()
    ]
]);
$this->title = 'Planificacion';
$this->params['breadcrumbs'] = [['label' => 'Cargos']];
?>

<div class="card ">
    <div class="card-header">
        <button id="btnMostrarCrearCargo" class="btn btn-primary bg-gradient-primary" >
            <div class="icon closed">
                <div class="circle">
                    <div class="horizontal"></div>
                    <div class="vertical"></div>
                </div>
                Agregar Cargo
            </div>
        </button>
    </div>
    <div id="IngresoDatos" class="card-body" >
        <div class="col d-flex justify-content-center">
            <div class="card " style="width: 40rem;" >
                <div class="card-header bg-gradient-primary">Ingreso Datos</div>
                <div class="card-body">
                    <input type="text" id="codigo" name="codigo" disabled hidden >
                    <form id="formcargo" action="" method="post">

                        <div class="form-group">
                            <label for="sectorTrabajo">Sector de Trabajo</label>
                            <select id="sectorTrabajo" name="sectorTrabajo"  class="form-control input-lg">
                                <option value="0" disabled selected>Selecionar sector trabajo</option>
                                <?php
                                foreach ($sectoresTrabajo as $sectorTrabajo) {
                                    echo "<option value='" . $sectorTrabajo->CodigoSectorTrabajo . "'>" . $sectorTrabajo->NombreSectorTrabajo . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="nombreCargo" class="control-label">Nombre</label>
                            <input id="nombreCargo" name="nombreCargo" placeholder="Ingresar nombre del cargo" class="form-control input-lg txt">
                        </div>
                        <div class="form-group">
                            <label for="descripcionCargo" class="control-label">Descripción</label>
                            <textarea id="descripcionCargo" name="descripcionCargo" rows="3" placeholder="Ingresar descripción del cargo" class="form-control input-lg txt"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="requisitosPrincipales" class="control-label">Requisitos Principales</label>
                            <textarea id="requisitosPrincipales" name="requisitosPrincipales" rows="3"
                                      placeholder="Ingresar requisitos principales del cargo"
                                      class="form-control input-lg txt"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="requisitosOpcionales" class="control-label">Requisitos Opcionales</label>
                            <textarea id="requisitosOpcionales" name="requisitosOpcionales" rows="3"
                                      placeholder="Ingresar requisitos opcionales del cargo"
                                      class="form-control input-lg txt"></textarea>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <button class='btn btn-primary bg-gradient-primary btnGuardar'><i class='fas fas-check-circle-o'>Guardar</i></button>
                    <button class='btn btn-danger btn- btnCancel'><i class='fas fas-warning'>Cancelar</i></button>
                </div>
            </div>
        </div>
    </div>
    <div id="Divtabla" class="card-body">
        <table class="table table-bordered table-striped dt-responsive tablaListaCargos" style="width: 100%" >
            <thead>
            <th style="text-align: center; font-weight: bold;">#</th>
            <th style="text-align: center; font-weight: bold;">Codigo</th>
            <th style="text-align: center; font-weight: bold;">Nombre</th>
            <th style="text-align: center; font-weight: bold;">Descripción</th>
            <th style="text-align: center; font-weight: bold;">Sector</th>
            <th style="text-align: center; font-weight: bold;">Estado</th>
            <th style="text-align: center; font-weight: bold;">Acciones</th>
            </thead>
        </table>
    </div>
</div>