<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

$contexto = Yii::$app->userContext->contexto();
$colorModulo = Yii::$app->userContext->colorModulo();
$moduloActivo = Yii::$app->userContext->moduloActivo();
$usuario = Yii::$app->user->identity;

$dashboardUrl = ['site/index'];

$gestiones = [];
$estadosPoa = [];
$llaves = [];

if ($moduloActivo) {
    $dashboardUrl = [$moduloActivo->DashboardRoute];

    $gestiones = ArrayHelper::map(
            Yii::$app->user->identity->getGestionesPermitidas(),
            'IdGestion',
            'Gestion'
    );

    if ($contexto?->IdGestion) {
        $estadosPoa = ArrayHelper::map(
                $usuario->getEstadosPoaPermitidos($contexto->IdGestion),
                'IdEstadoPoa',
                'Codigo'
        );
    }

    if ($contexto?->IdGestion && $contexto?->IdEstadoPoa) {
        $llaves = ArrayHelper::map(
                $usuario->getLlavesPermitidas($contexto->IdGestion, $contexto->IdEstadoPoa),
                'IdLlavePresupuestaria',
                'Llave'
        );
    }
}
?>
<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light" style="background-color: <?= $colorModulo ?> !important;">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item">
            <?= Html::a(
                    'Dashboard',
                    $dashboardUrl,
                    ['class' => 'nav-link']
            ) ?>
        </li>

        <li class="navbar-context">


            <?php if ($moduloActivo): ?>

                <div class="navbar-context">

                    <div class="context-field">
                        <label>Gestión</label>
                        <?= Html::dropDownList(
                                'IdGestion',
                                Yii::$app->userContext->contexto()?->IdGestion,
                                $gestiones,
                                [
                                        'class' => 'form-control form-control-sm context-select',
                                        'prompt' => 'Seleccione',
                                        'id' => 'select-gestion',
                                        'onchange' => "if(this.value){ window.location.href='" . Url::to(['/site/cambiar-gestion']) . "&id=' + this.value; }",
                                ]
                        ) ?>
                    </div>

                    <div class="context-field">
                        <label>Estado POA</label>
                        <?= Html::dropDownList(
                                'IdEstadoPoa',
                                $contexto?->IdEstadoPoa,
                                $estadosPoa,
                                [
                                        'class' => 'form-control form-control-sm context-select',
                                        'prompt' => 'Seleccione',
                                        'id' => 'select-estado-poa',
                                        'disabled' => empty($contexto?->IdGestion),
                                        'onchange' => "if(this.value){ window.location.href='" . Url::to(['/site/cambiar-estado-poa']) . "&id=' + this.value; }",
                                ]
                        ) ?>
                    </div>

                    <div class="context-field context-field-lg">
                        <label>Llave Presupuestaria</label>
                        <?= Html::dropDownList(
                                'IdLlavePresupuestaria',
                                $contexto?->IdLlavePresupuestaria,
                                $llaves,
                                [
                                        'class' => 'form-control form-control-sm context-select',
                                        'prompt' => 'Seleccione',
                                        'id' => 'select-llave',
                                        'disabled' => empty($contexto?->IdGestion) || empty($contexto?->IdEstadoPoa),
                                        'onchange' => "if(this.value){ window.location.href='" . Url::to(['/site/cambiar-llave']) . "&id=' + this.value; }",
                                ]
                        ) ?>
                    </div>

                </div>

            <?php else: ?>

                <div class="navbar-no-module">
                    <i class="fas fa-info-circle"></i>
                    Seleccione un módulo para comenzar
                </div>

            <?php endif; ?>

        </li>
    </ul>


    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <!-- Navbar Search
        <li class="nav-item">
            <a class="nav-link" data-widget="navbar-search" href="#" role="button">
                <i class="fas fa-search"></i>
            </a>
            <div class="navbar-search-block">
                <form class="form-inline">
                    <div class="input-group input-group-sm">
                        <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
                        <div class="input-group-append">
                            <button class="btn btn-navbar" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                            <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </li>-->

        <!-- Messages Dropdown Menu -->
        <li class="nav-item dropdown">
            <!--<a class="nav-link" data-toggle="dropdown" href="#">
                <i class="far fa-comments"></i>
                <span class="badge badge-danger navbar-badge">3</span>
            </a>-->
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <a href="#" class="dropdown-item">
                    <!-- Message Start
                    <div class="media">
                        <img src="<?=$assetDir?>/img/user1-128x128.jpg" alt="User Avatar" class="img-size-50 mr-3 img-circle">
                        <div class="media-body">
                            <h3 class="dropdown-item-title">
                                Brad Diesel
                                <span class="float-right text-sm text-danger"><i class="fas fa-star"></i></span>
                            </h3>
                            <p class="text-sm">Call me whenever you can...</p>
                            <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
                        </div>
                    </div>-->
                    <!-- Message End -->
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                    <!-- Message Start -->
                    <div class="media">
                        <img src="<?=$assetDir?>/img/user8-128x128.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">
                        <div class="media-body">
                            <h3 class="dropdown-item-title">
                                John Pierce
                                <span class="float-right text-sm text-muted"><i class="fas fa-star"></i></span>
                            </h3>
                            <p class="text-sm">I got your message bro</p>
                            <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
                        </div>
                    </div>
                    <!-- Message End -->
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                    <!-- Message Start -->
                    <div class="media">
                        <img src="<?=$assetDir?>/img/user3-128x128.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">
                        <div class="media-body">
                            <h3 class="dropdown-item-title">
                                Nora Silvester
                                <span class="float-right text-sm text-warning"><i class="fas fa-star"></i></span>
                            </h3>
                            <p class="text-sm">The subject goes here</p>
                            <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
                        </div>
                    </div>
                    <!-- Message End -->
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item dropdown-footer">See All Messages</a>
            </div>
        </li>
        <!-- Notifications Dropdown Menu
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <i class="far fa-bell"></i>
                <span class="badge badge-warning navbar-badge">15</span>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <span class="dropdown-header">15 Notifications</span>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                    <i class="fas fa-envelope mr-2"></i> 4 new messages
                    <span class="float-right text-muted text-sm">3 mins</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                    <i class="fas fa-users mr-2"></i> 8 friend requests
                    <span class="float-right text-muted text-sm">12 hours</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                    <i class="fas fa-file mr-2"></i> 3 new reports
                    <span class="float-right text-muted text-sm">2 days</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
            </div>
        </li>-->
        <!--<li class="nav-item">
            <?= Html::a('<i class="fas fa-sign-out-alt"></i>', ['/site/logout'], ['data-method' => 'post', 'class' => 'nav-link']) ?>
        </li>-->
        <li class="nav-item">
            <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                <i class="fas fa-expand-arrows-alt"></i>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button">
                <i class="fas fa-th-large"></i>
            </a>
        </li>
    </ul>
</nav>
<!-- /.navbar -->

<style>
    .navbar-context {

        display: flex;

        gap: 10px;

        margin-left: 20px;
    }

    .navbar-context select {

        min-width: 180px;
    }
</style>


<?php
$this->registerJsFile(
    '@web/js/contexto-poa/ContextoPoaNavbar.js',
    [
        'depends' => [
            '\yii\web\JqueryAsset',
            '\yii\bootstrap5\BootstrapAsset',
        ],
    ]
);
$this->registerCss('
    #contextoPoaMenu {
        border-radius: 0.5rem;
    }
    #contextoPoaMenu .contexto-poa-item {
        background-color: #f8f9fa;
        border-radius: 0.5rem;
        padding: 0.75rem;
        cursor: pointer;
        outline: none;
        transition: background-color 0.2s ease, box-shadow 0.2s ease;
    }
    #contextoPoaMenu .contexto-poa-item + .contexto-poa-item {
        margin-top: 0.5rem;
    }
    #contextoPoaMenu .contexto-poa-item:hover {
        background-color: #eef3fb;
    }
    #contextoPoaMenu .contexto-poa-item:focus {
        box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, 0.2);
    }
    #contextoPoaMenu .contexto-poa-selected {
        border-left: 0.35rem solid #0d6efd;
        background-color: #d6e4ff;
    }
    #contextoPoaMenu .contexto-poa-selected:hover {
        background-color: #ccdcff;
    }
    #contextoPoaMenu .contexto-poa-abreviacion {
        border: 1px solid #ced4da;
        color: #495057;
        background-color: #ffffff;
    }
    #contextoPoaMenu .btn-link {
        color: #6c757d;
    }
    #contextoPoaMenu .btn-link:hover {
        color: #0d6efd;
        text-decoration: none;
    }
');
?>

<style>
    .navbar-no-module {



        margin-left: 20px;

        font-size: 14px;

        font-weight: 500;

        display: flex;

        align-items: center;

        gap: 8px;
    }

    .navbar-context {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-left: 20px;
    }

    .context-field {
        display: flex;
        flex-direction: column;
        min-width: 180px;
    }

    .context-field-lg {
        min-width: 310px;
    }

    .context-field label {
        color: rgba(255,255,255,.9);
        font-size: 11px;
        font-weight: 600;
        margin-bottom: 2px;
        line-height: 1;
    }

    .context-select {
        height: 30px;
        border-radius: 7px;
        border: 1px solid rgba(255,255,255,.35);
        font-size: 13px;
    }

    .navbar-no-module {
        color: #ffffff;
        margin-left: 20px;
        font-size: 14px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 8px;
    }
</style>
