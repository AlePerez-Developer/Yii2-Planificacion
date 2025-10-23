<?php
use yii\web\JqueryAsset;

app\modules\Planificacion\assets\PlanificacionAsset::register($this);

$this->registerJsFile("@planificacionModule/js/PoliticaEstrategica/politicaEstrategica.js", [
    'depends' => [
        JqueryAsset::class
    ]
]);

$this->registerJsFile("@planificacionModule/js/PoliticaEstrategica/dt-declaration.js", [
    'depends' => [
        JqueryAsset::class
    ]
]);

$this->registerJsFile("@planificacionModule/js/PoliticaEstrategica/s2-declaration.js", [
    'depends' => [
        JqueryAsset::class
    ]
]);

$this->title = 'Planificacion';
$this->params['breadcrumbs'] = [['label' => '/Politicas Estrategicas']];
?>

<div class="card ">
    <div class="card-header">
        <button id="btnMostrarCrear" class="btn btn-primary bg-gradient-primary">
            <span class="icon closed">
                <span class="circle">
                    <span class="horizontal"></span>
                    <span class="vertical"></span>
                </span>
                Agregar Política Estratégica
            </span>
        </button>
    </div>
    <div id="divDatos" class="card-body" style="display: none">
        <div class="col d-flex justify-content-center">
            <div class="card " style="width: 50rem;">
                <div class="card-header bg-gradient-primary">Ingreso Datos</div>
                <div class="card-body">
                    <form id="formPoliticaEstrategica" action="" method="post">

                        <div class="form-group">
                            <label for="areasEstrategicas" class="lblTitulo">Seleccione una Area Estrategica</label>
                            <select id="areasEstrategicas" name="areasEstrategicas" class="form-control codigo_group">
                                <option></option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="codigo" class="control-label">Código</label>
                            <input type="text" id="codigo" name="codigo"  placeholder="Codigo" style="width: 120px" class="form-control input-lg num codigo_group" maxlength="1">
                        </div>

                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <textarea class="form-control input-sm txt" rows="4" id="descripcion" name="descripcion" placeholder="Descripcion"></textarea>
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
        <table id="tablaListaPoliticas" name="tablaListaPoliticas" class="table table-bordered table-striped dt-responsive"
               style="width: 100%">
            <thead>
            <th style="text-align: center; vertical-align: middle;">#</th>
            <th style="text-align: center; vertical-align: middle;">Área Estratégica</th>
            <th style="text-align: center; vertical-align: middle;">Area</th>
            <th style="text-align: center; vertical-align: middle;">Código</th>
            <th style="text-align: center; vertical-align: middle;">Descripción</th>
            <th style="text-align: center; vertical-align: middle;">Acciones</th>
            </thead>
        </table>
    </div>
</div>