<?php

use yii\web\JqueryAsset;

app\modules\Planificacion\assets\PlanificacionAsset::register($this);

$this->registerJsFile("@planificacionModule/js/indicador-estrategico-accion/dt-declaration.js", [
        'depends' => [
                JqueryAsset::class
        ]
]);

$this->registerJsFile("@planificacionModule/js/indicador-estrategico-accion/s2-declaration.js", [
        'depends' => [
                JqueryAsset::class
        ]
]);


$this->title = 'Planificación Institucional';

$this->params['subtitle'] = 'Asignacion de acciones estrategicas';

$this->params['icon'] = 'fas fa-clipboard-list';

$this->params['iconColor'] = 'info';

$this->params['actions'] =
    '<button id="btnMostrarCrear"  class="btn-crear closed" >
              <span class="circle">
                <span class="horizontal"></span>
                <span class="vertical"></span>
              </span>
              <span class="btn-text">Nuevo Registro</span>
        </button>

         <button id="btnReportePdf" class="btn-reporte">
            <i class="fas fa-file-pdf"></i>
             <span class="btn-text">Exportar</span>
         </button>';

$this->params['breadcrumbs'][] = [
    'label' => '/ Ind. Estrategicos'
];
?>
<div class="card ">




    <div  class="card-body">
        <div class="card-dtic-style">
            <div class="card-dtic-style-header">
                <div class="card-dtic-style-title">
                    Objetivos Estrategicos
                </div>
                <div class="container">
                    <div class="row">
                        <div class="col">
                            <select id="idObjEstrategico" class="form-control"></select>
                        </div>
                    </div>
                </div>
                <div>

                </div>
            </div>
            <div style="height: 150px"></div>
            <label for="idObjEstrategico"></label>
        </div>
    </div>

    <div  class="card-body">
        <div class="card-dtic-style">
            <div class="card-dtic-style-header">
                <div class="card-dtic-style-title">
                    Indicadores Estratégicos Institucionales
                </div>
            </div>
        </div>
    </div>
</div>
