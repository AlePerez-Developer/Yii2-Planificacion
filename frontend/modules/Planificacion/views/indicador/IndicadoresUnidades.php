<?php
use yii\web\JqueryAsset;

app\modules\Planificacion\assets\PlanificacionAsset::register($this);

$this->registerJsFile("@web/js/indicador/Indicador.js",[
    'depends' => [
        JqueryAsset::className()
    ]
]);
?>

<div class="card ">
    <div class="card-header">
        Registro de Unidades
    </div>
    <div id="Divtabla" class="card-body">
        <table class="table table-bordered table-striped dt-responsive tablaListaIndicadores" style="width: 100%" >
            <thead>
            <th>#</th>
            <th>#</th>
            <th>Codigo Pei</th>
            <th>Codigo Poa</th>
            <th>Descripcion</th>
            <th>Articulacion</th>
            <th>Resultado</th>
            <th>Tipo</th>
            <th>Categoria</th>
            <th>Unidad</th>
            <th>Estado</th>
            <th>Acciones</th>
            </thead>
        </table>
    </div>

</div>

