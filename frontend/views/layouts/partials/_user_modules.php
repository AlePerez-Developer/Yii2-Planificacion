<?php

use yii\helpers\Html;
use yii\helpers\Url;

$moduloActivo = Yii::$app->userContext->moduloActivo();
$contexto = Yii::$app->userContext->contexto();
$modulos = Yii::$app->user->identity->getModulosPermitidos();

$colorModulo = $moduloActivo?->Color ?? '#6c757d';
?>

<div class="sidebar-modules">

    <div class="sidebar-section-title">
        MÓDULOS
    </div>

    <?php foreach ($modulos as $modulo): ?>

        <?php
        $activo = $moduloActivo && strtoupper($moduloActivo->IdModulo) === strtoupper($modulo->IdModulo);
        ?>

        <a
                href="<?= Url::to(['/site/seleccionar-modulo', 'id' => $modulo->IdModulo]) ?>"
                class="sidebar-module-item <?= $activo ? 'active' : '' ?>"
                title="<?= Html::encode($modulo->Nombre) ?>"
                data-toggle="tooltip"
                style="<?= $activo ? 'border-left-color:' . Html::encode($modulo->Color) : '' ?>"
        >
            <i class="<?= Html::encode($modulo->Icono) ?>"></i>

            <span class="module-text">
                <?= Html::encode($modulo->Nombre) ?>
            </span>
        </a>

    <?php endforeach; ?>

    <?php if ($contexto && $contexto->IdGestion): ?>

        <div
                class="sidebar-context"
                style="border-left-color: <?= Html::encode($colorModulo) ?>"
        >

            <div class="sidebar-context-title">
                Contexto activo
            </div>

            <div class="context-row">
                <span class="context-label">Gestión</span>
                <span class="context-value">
                    <?= Html::encode($contexto->gestion?->Gestion ?? '-') ?>
                </span>
            </div>

            <div class="context-row">
                <span class="context-label">Estado POA</span>
                <span class="context-value">
                    <?= Html::encode($contexto->estadoPoa?->Codigo ?? '-') ?>
                </span>
            </div>

            <div class="context-row">
                <span class="context-label">Llave</span>
                <span class="context-value context-value-llave">
                    <?= Html::encode($contexto->llavePresupuestaria?->Llave ?? '-') ?>
                </span>
            </div>

        </div>

    <?php endif; ?>

</div>