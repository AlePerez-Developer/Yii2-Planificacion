<?php

use yii\helpers\Html;
use yii\web\JqueryAsset;

app\modules\Planificacion\assets\PlanificacionAsset::register($this);

$this->registerJsFile("@planificacionModule/js/llave-presupuestaria/LlavePresupuestaria.js", [
    'depends' => [
        JqueryAsset::class,
    ],
]);

$this->title = 'Planificación';
$this->params['breadcrumbs'] = [['label' => '/Llave Presupuestaria']];
?>

<div class="card">
    <div class="card-header">
        <button id="btnMostrarCrear" class="btn btn-primary bg-gradient-primary">
            <div class="icon closed">
                <div class="circle">
                    <div class="horizontal"></div>
                    <div class="vertical"></div>
                </div>
                Agregar Llave Presupuestaria
            </div>
        </button>
    </div>
    <div id="divDatos" class="card-body" style="display: none">
        <div class="col d-flex justify-content-center">
            <div class="card" style="width: 60rem;">
                <div class="card-header bg-gradient-primary">Ingreso Datos</div>
                <div class="card-body">
                    <form id="formLlavePresupuestaria" action="" method="post">
                        <input type="hidden" id="codigoUnidadOriginal" name="codigoUnidadOriginal">
                        <input type="hidden" id="codigoProgramaOriginal" name="codigoProgramaOriginal">
                        <input type="hidden" id="codigoProyectoOriginal" name="codigoProyectoOriginal">
                        <input type="hidden" id="codigoActividadOriginal" name="codigoActividadOriginal">

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="codigoUnidad">Unidad</label>
                                <select class="form-control" id="codigoUnidad" name="codigoUnidad">
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($unidades as $unidad): ?>
                                        <option value="<?= Html::encode($unidad->CodigoUnidad) ?>">
                                            <?= Html::encode('(' . $unidad->Da . '/' . $unidad->Ue . ') - ' . $unidad->Descripcion) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="codigoPrograma">Programa</label>
                                <select class="form-control" id="codigoPrograma" name="codigoPrograma">
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($programas as $programa): ?>
                                        <option value="<?= Html::encode($programa->CodigoPrograma) ?>">
                                            <?= Html::encode('(' . $programa->Codigo . ') - ' . $programa->Descripcion) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="codigoProyecto">Proyecto</label>
                                <select class="form-control" id="codigoProyecto" name="codigoProyecto">
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($proyectos as $proyecto): ?>
                                        <option value="<?= Html::encode($proyecto->CodigoProyecto) ?>" data-programa="<?= Html::encode($proyecto->Programa) ?>">
                                            <?= Html::encode('(' . $proyecto->Codigo . ') - ' . $proyecto->Descripcion) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="codigoActividad">Actividad</label>
                                <select class="form-control" id="codigoActividad" name="codigoActividad">
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($actividades as $actividad): ?>
                                        <option value="<?= Html::encode($actividad->CodigoActividad) ?>" data-programa="<?= Html::encode($actividad->Programa) ?>">
                                            <?= Html::encode('(' . $actividad->Codigo . ') - ' . $actividad->Descripcion) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <textarea class="form-control" rows="3" id="descripcion" name="descripcion" placeholder="Descripción"></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="techoPresupuestario">Techo Presupuestario</label>
                                <input type="number" step="0.01" min="0" class="form-control" id="techoPresupuestario" name="techoPresupuestario" placeholder="0.00">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="fechaInicio">Fecha Inicio</label>
                                <input type="date" class="form-control" id="fechaInicio" name="fechaInicio">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <button id="btnGuardar" name="btnGuardar" class="btn btn-primary bg-gradient-primary"><span class="fa fa-check-circle"></span> Guardar</button>
                    <button id="btnCancelar" name="btnCancelar" class="btn btn-danger"><span class="fa fa-times-circle"></span> Cancelar</button>
                </div>
            </div>
        </div>
    </div>
    <div id="divTabla" class="card-body overflow-auto">
        <table id="tablaLlavesPresupuestarias" class="table table-bordered table-striped dt-responsive" style="width: 100%;">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Unidad</th>
                    <th>Programa</th>
                    <th>Proyecto</th>
                    <th>Actividad</th>
                    <th>Descripción</th>
                    <th>Techo</th>
                    <th>Período</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
