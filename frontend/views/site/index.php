<?php

/** @var yii\web\View $this */
/** @var $modulos */


use yii\helpers\Url;

$this->title = 'Bienvenido a POA Presupuestos';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="row">

    <?php foreach ($modulos as $modulo): ?>

        <div class="col-lg-4 col-md-6 col-sm-12">

            <a
                    href="<?= Url::to([
                        $modulo->DashboardRoute
                    ]) ?>"
                    class="module-card-link"
            >

                <div
                        class="module-card"
                        style="border-top: 4px solid <?= $modulo->Color ?>"
                >

                    <div class="module-icon">
                        <i class="<?= $modulo->Icono ?>"></i>
                        <i class="fa fa-chalkboard"></i>

                    </div>

                    <div class="module-title">

                        <?= $modulo->Nombre ?>

                    </div>

                    <div class="module-description">

                        Acceder al módulo

                    </div>

                </div>

            </a>

        </div>

    <?php endforeach; ?>

</div>

<style>
    .module-card-link {

        text-decoration: none !important;
    }

    .module-card {

        background: #2c3643;

        border-radius: 16px;

        padding: 35px 20px;

        margin-bottom: 25px;

        text-align: center;

        transition: all .25s ease;

        box-shadow: 0 4px 12px rgba(0,0,0,0.18);

        border: 1px solid #3b4655;

        min-height: 220px;

        cursor: pointer;
    }

    .module-card:hover {

        transform: translateY(-6px);

        box-shadow: 0 12px 24px rgba(0,0,0,0.28);

        background: #344150;
    }

    .module-icon {

        font-size: 52px;

        margin-bottom: 20px;
    }

    .module-title {

        color: #fff;

        font-size: 22px;

        font-weight: 600;

        margin-bottom: 10px;
    }

    .module-description {

        color: #c7d0d9;

        font-size: 13px;
    }

</style>
