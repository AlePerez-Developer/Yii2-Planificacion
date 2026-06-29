<?php

use hail812\adminlte\widgets\Menu;

?>
<aside class="main-sidebar sidebar-light-primary elevation-4">
    <!-- Brand Logo -->
    <a class="brand-link">
        <img src="img/logo.jpg"
             alt="AdminLTE Logo"
             class="brand-image img-circle elevation-3"
             style="opacity: .8">
        <span class="brand-text font-weight-light">USFX - Dtic</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <?= $this->render('partials/_user_panel') ?>
        </div>


        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <?= $this->render('partials/_user_modules') ?>
            </div>
            <?php
            try {
                echo Menu::widget([
                        'items' => [

                        ],
                ]);
            } catch (Throwable $e) {

            }
            ?>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>