<?php

use yii\web\JqueryAsset;

app\modules\Planificacion\assets\PlanificacionAsset::register($this);

$this->registerJsFile("@planificacionModule/js/area-estrategica/dt-declaration.js", [
    'depends' => [
        JqueryAsset::class
    ]
]);

$this->registerJsFile("@planificacionModule/js/area-estrategica/areaEstrategica.js", [
    'depends' => [
        JqueryAsset::class
    ]
]);
$this->title = 'Planificacion';
$this->params['breadcrumbs'] = [['label' => '/Areas Estrategicas']];
?>

<div class="card ">
    <div class="card-header">
        <button id="btnMostrarCrear" class="btn btn-primary bg-gradient-primary">
            <span class="icon closed">
                <span class="circle">
                    <span class="horizontal"></span>
                    <span class="vertical"></span>
                </span>
                Agregar Área Estratégica
            </span>
        </button>
    </div>
    <div id="divDatos" class="card-body" style="display: none">
        <div class="col d-flex justify-content-center">
            <div class="card " style="width: 50rem;">
                <div class="card-header bg-gradient-primary">Ingreso Datos</div>
                <div class="card-body">
                    <form id="formAreaEstrategica" action="" method="post" data-id-area-estrategica="0">
                        <div class="form-group">
                            <label for="codigo" class="control-label">Código de Área</label>
                            <input type="text" id="codigo" name="codigo"  placeholder="Codigo" style="width: 120px" class="form-control input-lg num" maxlength="1">
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
        <table id="tablaListaAreas" name="tablaListaAreas" class="table table-bordered table-striped dt-responsive"
               style="width: 100%">
            <thead>
            <th style="text-align: center; vertical-align: middle;">#</th>
            <th style="text-align: center; vertical-align: middle;">PEI</th>
            <th style="text-align: center; vertical-align: middle;">Codigo</th>
            <th style="text-align: center; vertical-align: middle;">Descripcion</th>
            <th style="text-align: center; vertical-align: middle;">Acciones</th>
            </thead>
        </table>
    </div>
</div>