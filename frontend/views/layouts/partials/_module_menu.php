<?php

$modulo = Yii::$app->userContext->moduloActivo();

if (!$modulo) {
    return;
}

$menus = $modulo->menus;

?>

<nav class="module-navbar">

    <?php foreach ($menus as $menu): ?>

        <a href="<?= \yii\helpers\Url::to([$menu->Ruta]) ?>">

            <?= $menu->Nombre ?>

        </a>

    <?php endforeach; ?>

</nav>

<style>
    .module-navbar {

        background: #ffffff;

        border-bottom: 1px solid #dee2e6;

        padding: 10px 20px;

        display: flex;

        gap: 20px;
    }

    .module-navbar a {

        color: #495057;

        text-decoration: none;

        font-weight: 500;
    }

    .module-navbar a:hover {

        color: #007bff;
    }
</style>
