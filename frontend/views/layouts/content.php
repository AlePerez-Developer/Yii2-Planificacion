<?php
/* @var $content string */

use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\widgets\Breadcrumbs;

/* ==========================================
   CONFIGURACIÓN DINÁMICA DESDE VISTAS
========================================== */

$title = !is_null($this->title)
    ? $this->title
    : Inflector::camelize($this->context->id);

$subtitle = $this->params['subtitle']
    ?? 'Panel administrativo del sistema';

$icon = $this->params['icon']
    ?? 'fas fa-chart-line';

$iconColor = $this->params['iconColor']
    ?? 'primary';

$actions = $this->params['actions']
    ?? '';
?>

<div class="content-wrapper content-wrapper-pro">

    <!-- HEADER -->
    <section class="content-header pt-3 pb-2">

        <div class="container-fluid">

            <div class="card header-card border-0 shadow-sm mb-3">

                <div class="card-body px-4 py-3">

                    <div class="row align-items-center">

                        <!-- IZQUIERDA -->
                        <div class="col-lg-7 col-md-6">

                            <div class="d-flex align-items-center flex-wrap">

                                <div class="page-icon bg-<?= $iconColor ?> mr-3">
                                    <i class="<?= $icon ?>"></i>
                                </div>

                                <div>

                                    <h1 class="m-0 page-title">
                                        <?= Html::encode($title) ?>
                                    </h1>

                                    <div class="page-subtitle">
                                        <?= Html::encode($subtitle) ?>
                                    </div>

                                </div>

                            </div>

                        </div>

                        <!-- DERECHA -->
                        <div class="col-lg-5 col-md-6 text-md-right mt-3 mt-md-0">


                            <?php
                            try {
                                echo Breadcrumbs::widget([
                                    'encodeLabels' => false,
                                    'homeLink' => [
                                        'label' => '<i class="fas fa-home text-primary">Home</i>',
                                        'url' => Yii::$app->homeUrl
                                    ],
                                    'links' => $this->params['breadcrumbs'] ?? [],
                                    'options' => [
                                        'class' =>
                                            'breadcrumb breadcrumb-pro justify-content-md-end justify-content-start mb-0'
                                    ]
                                ]);
                            } catch (Throwable $e) {
                            }
                            ?>

                            <?php if ($actions): ?>
                                <div class="mb-2">
                                    <?= $actions ?>
                                </div>
                            <?php endif; ?>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>

    <!-- CONTENIDO -->
    <section class="content pb-4">

        <div class="container-fluid">

            <?= $content ?>

        </div>

    </section>

</div>