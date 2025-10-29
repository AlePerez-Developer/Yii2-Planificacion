<?php

use yii\helpers\Html;
use yii\helpers\Url;

?>
<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="<?=\yii\helpers\Url::home()?>" class="nav-link">Home</a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="#" class="nav-link">Contact</a>
        </li>
        <li class="nav-item dropdown d-none d-sm-inline-block" id="contexto-poa-dropdown" data-contexto-poa-url="<?= Url::to(['/Planificacion/estado-poa/listar-todo']) ?>">
            <a href="#" class="nav-link d-flex align-items-center" id="contextoPoaToggle" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                <i class="fas fa-cog me-2"></i>
                <span>Contexto POA</span>
            </a>
            <div class="dropdown-menu dropdown-menu-end p-3" aria-labelledby="contextoPoaToggle" id="contextoPoaMenu" style="min-width: 16rem; max-width: 18rem;">
                <div class="d-flex align-items-center mb-2">
                    <span class="text-muted text-uppercase fw-semibold small">Contexto POA</span>
                    <button type="button" class="btn btn-link btn-sm ms-auto text-secondary" id="contextoPoaRefresh" title="Actualizar contexto">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    <button type="button" class="btn btn-link btn-sm text-secondary" id="contextoPoaClose" title="Cerrar panel">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="text-muted small d-none" id="contextoPoaFeedback"></div>
                <ul class="list-unstyled mb-0" id="contextoPoaList"></ul>
            </div>
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