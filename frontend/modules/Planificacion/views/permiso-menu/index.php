<?php

use app\modules\Planificacion\assets\PlanificacionAsset;
use yii\web\JqueryAsset;

PlanificacionAsset::register($this);
$this->registerCssFile('@planificacionModule/css/catalogo/style.css', ['depends' => [PlanificacionAsset::class]]);
$this->registerJsFile('@planificacionModule/js/permiso-menu/index.js', ['depends' => [JqueryAsset::class]]);

$this->title = 'Planificación Institucional';
$this->params['subtitle'] = 'Permisos de menú por unidad';
$this->params['icon'] = 'fas fa-user-lock';
$this->params['iconColor'] = 'warning';
$this->params['breadcrumbs'][] = ['label' => '/ Permisos'];
?>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <label>Usuario</label>
                <select id="idUsuario" class="form-control"></select>
            </div>
            <div class="col-md-3">
                <label>Unidad</label>
                <select id="idUnidadEjecutora" class="form-control"></select>
            </div>
            <div class="col-md-3">
                <label>Gestión</label>
                <select id="idGestion" class="form-control"></select>
            </div>
            <div class="col-md-3">
                <label>Estado POA</label>
                <select id="idEstadoPoa" class="form-control"></select>
            </div>
        </div>
        <div class="mt-3">
            <button id="btnCargar" type="button" class="btn btn-primary">Cargar menús</button>
            <button id="btnGuardarPermisos" type="button" class="btn btn-success">Guardar permisos</button>
            <button id="btnMarcarVer" type="button" class="btn btn-outline-secondary">Marcar todos Ver</button>
        </div>
        <table class="table table-sm table-striped mt-3">
            <thead>
            <tr>
                <th>Menú</th>
                <th>Ruta</th>
                <th class="text-center">Ver</th>
                <th class="text-center">Crear</th>
                <th class="text-center">Editar</th>
                <th class="text-center">Eliminar</th>
            </tr>
            </thead>
            <tbody id="tablaPermisos"></tbody>
        </table>
    </div>
</div>
