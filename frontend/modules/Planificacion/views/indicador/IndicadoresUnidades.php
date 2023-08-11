<?php
use yii\web\JqueryAsset;

app\modules\Planificacion\assets\PlanificacionAsset::register($this);

$this->registerJsFile("@web/js/indicador/IndicadorApertura.js",[
    'depends' => [
        JqueryAsset::className()
    ]
]);

?>

<button id="oso" class="btn btn-danger">este botoncito</button>