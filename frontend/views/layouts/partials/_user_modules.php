<?php

use yii\helpers\Url;

$moduloActivo = Yii::$app->userContext->moduloActivo();

$modulos = Yii::$app->user->identity->getModulosPermitidos();




?>

<div class="sidebar-modules">

    <div class="sidebar-section-title">
        MODULOS
    </div>

    <?php foreach ($modulos as $modulo): ?>

        <?php
        $activo =
                $moduloActivo &&
                $moduloActivo->IdModulo === $modulo->IdModulo;
        ?>

        <a
            href="<?= Url::to([
                    '/site/seleccionar-modulo',
                    'id' => $modulo->IdModulo
            ]) ?>"
            class="sidebar-module-item <?= $activo ? 'active' : '' ?>"
            data-toggle="tooltip"
            title="<?= $modulo->Nombre ?>"
            style="<?= $activo
                    ? 'border-left:4px solid ' . $modulo->Color
                    : '' ?>"
        >

            <i class="<?= $modulo->Icono ?>"></i>

            <span class="module-text">
                <?= $modulo->Nombre ?>
            </span>

        </a>

    <?php endforeach; ?>

</div>
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>

<style>
    .module-text {

        margin-left: 8px;

        transition: all .3s ease;
    }

    .sidebar-collapse .module-text {

        /*display: none;*/

        opacity: 0;

        width: 0;

        overflow: hidden;
    }

    .sidebar-collapse .main-sidebar:hover .module-text {

        opacity: 1;

        width: auto;
    }

    .sidebar-modules{
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }

    .sidebar-collapse .sidebar-module-item {

        display: flex;

        justify-content: center;

        align-items: center;
    }

    .sidebar-collapse .sidebar-section-title {

        display: none;
    }

    .sidebar-section-title {

        color: #bfc9d4;

        font-size: 11px;

        letter-spacing: 1px;

        margin: 20px 15px 10px;

        font-weight: bold;
    }

    .sidebar-module-item {

        display: block;

        color: #ffffff;

        padding: 10px 15px;

        text-decoration: none;

        transition: .2s;
    }

    .sidebar-module-item:hover {

        background: rgba(255, 255, 255, .08);

        color: #ffffff;
    }

    .sidebar-module-item.active {

        background: rgba(255, 255, 255, .08);

        font-weight: 600;
    }
</style>
