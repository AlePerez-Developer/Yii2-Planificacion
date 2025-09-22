<?php
use yii\helpers\Html;
use yii\web\JqueryAsset;

app\modules\Planificacion\assets\PlanificacionAsset::register($this);

$this->registerJsFile("@planificacionModule/js/obj-estrategico/ObjEstrategico.js",[
    'depends' => [
        JqueryAsset::class
    ]
]);

$this->registerJsFile("@planificacionModule/js/obj-estrategico/dt-declaration.js", [
    'depends' => [
        JqueryAsset::class
    ]
]);

$this->title = 'Planificacion';
$this->params['breadcrumbs'] = [['label' => '/Objs Estrategicos']];
?>

<div class="card ">
    <div class="card-header">
        <div class="row">
            <div class="col-6">
                <button id="btnMostrarCrear" class="btn btn-primary bg-gradient-primary">
                    <div class="icon closed">
                        <div class="circle">
                            <div class="horizontal"></div>
                            <div class="vertical"></div>
                        </div>
                        Agregar Obj. Estrategico
                    </div>
                </button>
            </div>
            <div class="col-6" style="text-align: right;">
                <?= Html::a('Reporte Obj Estrategico', ['reporte'], ['class' => 'btn btn-success', 'target' => '_Blank']) ?>
            </div>
        </div>
    </div>
    <div id="divDatos" class="card-body" style="display: none">
        <div class="col d-flex justify-content-center">
            <div class="card " style="width: 40rem;" >
                <div class="card-header bg-gradient-primary">Ingreso Datos</div>
                <div class="card-body">
                    <form id="formObjEstrategico" action="" method="post">
                        <div class="form-group">
                            <label for="codigoObjetivo">Codigo de Objetivo Estrategico (OE)</label>
                            <input type="text" class="form-control input-sm num" id="codigoObjetivo" name="codigoObjetivo" maxlength="3"  placeholder="Codigo" style="width: 100px" >
                        </div>
                        <div class="form-group">
                            <label for="objetivo" class="control-label">Descripcion del objetivo estrategico</label>
                            <textarea class="form-control input-sm txt" id="objetivo" name="objetivo" rows="4" placeholder="Descripcion del objetivo estrategico"></textarea>
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
        <table id="tablaListaObjEstrategicos" name="tablaListaObjEstrategicos" class="table table-bordered table-striped">
            <thead>
            <th>#</th>
            <th>#</th>
            <th>PEI</th>
            <th>Codigo</th>
            <th>Objetivo</th>
            <th>Estado</th>
            <th>Acciones</th>
            </thead>
        </table>
    </div>
</div>