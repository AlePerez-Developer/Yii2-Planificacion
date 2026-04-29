<?php
use yii\web\JqueryAsset;

app\modules\Planificacion\assets\PlanificacionAsset::register($this);


$this->registerJsFile("@planificacionModule/js/obj-institucional/ObjInstitucional.js",[
    'depends' => [
        JqueryAsset::class
    ]
]);

$this->registerJsFile("@planificacionModule/js/obj-institucional/dt-declaration.js", [
    'depends' => [
        JqueryAsset::class
    ]
]);

$this->registerJsFile("@planificacionModule/js/obj-institucional/s2-declaration.js", [
    'depends' => [
        JqueryAsset::class
    ]
]);

$this->title = 'Planificacion';
$this->params['breadcrumbs'] = [['label' => '/Objs Institucionales']];
?>

<div class="card ">
    <div class="card-header">
        <div class="row">
            <div class="col-6">
                <button id="btnMostrarCrear" class="btn btn-primary bg-gradient-primary">
                    <span class="icon closed">
                        <span class="circle">
                            <span class="horizontal"></span>
                            <span class="vertical"></span>
                        </span>
                        Agregar Obj. institucional
                    </span>
                </button>
            </div>
        </div>
    </div>
    <div id="divDatos" class="card-body" style="display: none">
        <div class="col d-flex justify-content-center">
            <div class="card " style="width: 80rem;" >
                <div class="card-header bg-gradient-primary">Ingreso Datos</div>
                <div class="card-body">
                    <form id="formObjInstitucional" action="" method="post">

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="objsEstrategicos">Seleccione el objetivo estrategico</label>
                                    <select class="form-control objEstrategico codigo_group" id="objsEstrategicos" name="objsEstrategicos" >
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-4">
                                <label for="codigo">Codigo de Objetivo Institucional (OI)</label>
                                <input type="text" class="form-control input-sm num codigo_group" id="codigo" name="codigo" maxlength="3"  placeholder="Codigo" style="width: 100px"  >
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="objetivo" class="control-label">Descripcion del objetivo institucional</label>
                                    <textarea class="form-control input-sm txt" id="objetivo" name="objetivo" rows="3" placeholder="Descripcion del objetivo institucional"></textarea>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="producto" class="control-label">Resultado/Producto esperado</label>
                                    <textarea class="form-control input-sm txt" id="producto" name="producto" rows="3" placeholder="Resultado/Producto esperado"></textarea>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
                <div class="card-footer text-center">
                    <button id="btnGuardar" name="btnGuardar" class='btn btn-primary bg-gradient-primary'><i class='fa fa-check-circle'></i> <span class='btn_text'> Guardar </span> </button>
                    <button id="btnCancelar" name="btnCancelar" class='btn btn-danger'><span class='fa fa-times-circle'></span> Cancelar </button>
                </div>
            </div>
        </div>
    </div>
    <div id="divTabla" class="card-body">
        <table id="tablaListaObjInstitucionales"
               name="tablaListaObjInstitucionales"
               class="table table-bordered table-striped">
            <thead>
            <th>#</th>
            <th>Codigo</th>
            <th>Objetivo</th>
            <th>Producto Esperado</th>
            <th>Estado</th>
            <th>Acciones</th>
            </thead>
        </table>
    </div>
</div>

