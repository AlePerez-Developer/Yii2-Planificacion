<?php

use app\modules\Planificacion\assets\PlanificacionAsset;
use yii\helpers\Html;
use yii\helpers\Url;

PlanificacionAsset::register($this);

$this->title = 'Planificación Institucional';
$this->params['subtitle'] = 'Enviar POA';
$this->params['icon'] = 'fas fa-paper-plane';
$this->params['iconColor'] = 'success';
$this->params['breadcrumbs'][] = ['label' => '/ Enviar POA'];

$puedeEnviar = (bool)($edicion['puedeEnviar'] ?? false);
$hayRestricciones = $restricciones !== [];
?>

<div class="card">
    <div class="card-body">
        <h5 class="mb-3">
            Unidad: <?= Html::encode($unidad?->Ue ? $unidad->Ue . ' - ' . $unidad->Descripcion : 'Sin unidad activa') ?>
        </h5>

        <?php if ($enviado || ($edicion['enviado'] ?? false)): ?>
            <div class="alert alert-success">
                El POA de la unidad fue enviado. Las opciones de generar, editar y eliminar ítems quedan bloqueadas.
            </div>
        <?php elseif ($hayRestricciones): ?>
            <div class="alert alert-danger">
                <strong>No se puede enviar el POA.</strong> Deben cumplirse las siguientes restricciones:
            </div>
            <ul class="list-group mb-4">
                <?php foreach ($restricciones as $restriccion): ?>
                    <li class="list-group-item list-group-item-warning"><?= Html::encode($restriccion) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <div class="alert alert-info">
                La unidad cumple las validaciones de presupuesto, programación trimestral e indicadores.
                Al enviar, no podrá modificar ítems ni operaciones aunque el cronograma siga vigente.
            </div>
        <?php endif; ?>

        <?php if ($puedeEnviar && !$enviado): ?>
            <?= Html::beginForm(Url::to(['enviar-poa/enviar']), 'post') ?>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-paper-plane"></i> Enviar POA
                </button>
            <?= Html::endForm() ?>
        <?php elseif (!$enviado && !($edicion['enCronograma'] ?? false)): ?>
            <p class="text-muted mb-0">No hay un cronograma vigente para registrar el envío.</p>
        <?php endif; ?>
    </div>
</div>
