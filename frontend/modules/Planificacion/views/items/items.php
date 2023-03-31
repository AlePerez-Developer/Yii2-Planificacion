<?php
use yii\web\JqueryAsset;

app\modules\Planificacion\assets\PlanificacionAsset::register($this);

$this->registerJsFile("@web/js/items/items.js",[
    'depends' => [
        JqueryAsset::className()
    ]
]);
$this->title = 'Planificacion';
$this->params['breadcrumbs'] = [['label' => 'Items']];
?>

<div class="card ">
    <div class="card-header">
        <button id="btnMostrarCrearItem" class="btn btn-primary bg-gradient-primary" >
            <div class="icon closed">
                <div class="circle">
                    <div class="horizontal"></div>
                    <div class="vertical"></div>
                </div>
                Agregar Item
            </div>
        </button>
    </div>
    <div id="IngresoDatos" class="card-body" >
        <div class="col d-flex justify-content-center">
            <div class="card " style="width: 80rem;" >
                <div class="card-header bg-gradient-primary">Ingreso Datos</div>
                <div class="card-body">
                    <input type="text" id="codigo" name="codigo" disabled hidden >
                    <form id="formitem" action="" method="post">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="form-group">
                                            <div class="searchArea">
                                                <button type="button" id="search" class="btn btn-primary btn-sm">Search</button>
                                                <div class="inputDiv">
                                                    <input id="search-term" class="form-control input-sm" placeholder="Buscar" autofocus>
                                                </div>
                                            </div>
                                            <label for="tree1">Seleccione unidad</label>
                                            <div id="unidad"> </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="card">
                                    <div class="card-body">

                                        <div class="form-group">
                                            <label for="sectorTrabajo">Sector de Trabajo</label>
                                            <select id="sectorTrabajo" name="sectorTrabajo" required
                                                    class="form-control input-lg">
                                                <option value="">Seleccionar Sector</option>
                                                <?php
                                                foreach ($sectoresTrabajo as $sectorTrabajo) {
                                                    echo "<option value='" . $sectorTrabajo->CodigoSectorTrabajo . "'>" . $sectorTrabajo->NombreSectorTrabajo . "</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="codigoCargo">Cargo</label>
                                            <select id="codigoCargo" name="codigoCargo" required
                                                    class="form-control input-lg" style="width: 100%;">
                                                <option value="">Seleccionar Cargo</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="codigoCargoDependencia">Dependencia</label>
                                            <select id="codigoCargoDependencia" name="codigoCargoDependencia" required
                                                    class="form-control input-lg" style="width: 100%;">
                                                <option value="">Seleccionar Cargo</option>
                                            </select>
                                        </div>
                                        <div class="row">
                                            <div class="form-group col-md-6 ml-auto">
                                                <label for="itemPlanilla" class="control-label">Item Planillas</label>
                                                <input id="itemPlanilla" name="itemPlanilla" type="text" maxlength="150"
                                                       placeholder="Ingresar el Item de Planillas" required class="form-control input-lg">
                                            </div>
                                            <div class="form-group col-md-6 ml-auto">
                                                <label for="itemRrhh" class="control-label">Item RRHH</label>
                                                <input id="itemRrhh" name="itemRrhh" type="text" maxlength="150"
                                                       placeholder="Ingresar el Item de RRHH" required class="form-control input-lg">
                                            </div>
                                        </div>
                                    </div>
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
    <div id="Divtabla" class="card-body">
        <table class="table table-bordered table-striped dt-responsive tablaListaItems" style="width: 100%" >
            <thead>
            <th style="text-align: center; font-weight: bold;">#</th>
            <th style="text-align: center; font-weight: bold;">Numero</th>
            <th style="text-align: center; font-weight: bold;">Item RRHH</th>
            <th style="text-align: center; font-weight: bold;">Item Planillas</th>
            <th style="text-align: center; font-weight: bold;">Sector</th>
            <th style="text-align: center; font-weight: bold;">Unidad</th>
            <th style="text-align: center; font-weight: bold;">Cargo</th>
            <th style="text-align: center; font-weight: bold;">Dependencia</th>
            <th style="text-align: center; font-weight: bold;">Estado</th>
            <th style="text-align: center; font-weight: bold;">Acciones</th>
            </thead>
        </table>
    </div>
</div>