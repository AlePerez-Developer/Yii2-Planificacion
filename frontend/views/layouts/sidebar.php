<?php

use hail812\adminlte\widgets\Menu;

?>
<aside class="main-sidebar sidebar-light-primary elevation-4">
    <!-- Brand Logo -->
    <a class="brand-link">
        <img src="img/logo.jpg" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
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
            echo Menu::widget([
                    'items' => [
                            [
                                    'label' => 'Planificacion',
                                    'icon' => 'th',
                                    'badge' => '<span class="right badge badge-info"></span>',
                                    'items' => [
                                            ['label' => 'Pei', 'url' => ['/Planificacion/peis/index'], 'iconStyle' => 'far'],
                                            ['label' => 'Area Estrategica', 'url' => ['/Planificacion/area-estrategica/index'], 'iconStyle' => 'far'],
                                            ['label' => 'Politica Estrategica', 'url' => ['/Planificacion/politica-estrategica/index'], 'iconStyle' => 'far'],
                                            ['label' => 'Objetivo Estrategico', 'url' => ['/Planificacion/obj-estrategico/index'], 'iconStyle' => 'far'],
                                            ['label' => 'Objetivo Institucional', 'url' => ['/Planificacion/obj-institucional/index'], 'iconStyle' => 'far'],
                                            ['label' => 'Indicador Estrategico', 'url' => ['/Planificacion/indicador-estrategico/index'], 'iconStyle' => 'far'],
                                    ]
                            ],
                            [
                                    'label' => 'Estructura',
                                    'icon' => 'th',
                                    'badge' => '<span class="right badge badge-info"></span>',
                                    'items' => [
                                            ['label' => 'Da', 'url' => ['/Planificacion/da/index'], 'iconStyle' => 'far'],
                                            ['label' => 'Ue', 'url' => ['/Planificacion/ue/index'], 'iconStyle' => 'far'],
                                            ['label' => 'Programas', 'url' => ['/Planificacion/programa/index'], 'iconStyle' => 'far'],
                                            ['label' => 'Proyectos', 'url' => ['/Planificacion/proyecto/index'], 'iconStyle' => 'far'],
                                            ['label' => 'Actividades', 'url' => ['/Planificacion/actividad/index'], 'iconStyle' => 'far'],
                                            ['label' => 'Llave Presupuestaria', 'url' => ['/Planificacion/llave-presupuestaria/index'], 'iconStyle' => 'far'],

                                    ]
                            ],
                            [
                                    'label' => 'catalogos',
                                    'icon' => 'th',
                                    'badge' => '<span class="right badge badge-info"></span>',
                                    'items' => [
                                            ['label' => 'Estados POA', 'url' => ['/Planificacion/estado-poa/index'], 'iconStyle' => 'far'],
                                            ['label' => 'Gastos', 'url' => ['/Planificacion/gasto/index'], 'iconStyle' => 'far'],
                                            ['label' => 'Accion Estrategica', 'url' => ['/Planificacion/accion-estrategica/index'], 'iconStyle' => 'far'],
                                    ]
                            ],
                            [
                                    'label' => 'Planificacion CH',
                                    'icon' => 'th',
                                    'badge' => '<span class="right badge badge-info"></span>',
                                    'visible' => false,
                                    'items' => [
                                            ['label' => 'Planificar', 'url' => ['/PlanificacionCH/planificar-carga-horaria/index'], 'iconStyle' => 'far'],
                                            ['label' => 'Planificar Matriciales', 'url' => ['/PlanificacionCH/planificar-carga-horaria-matricial/index'], 'iconStyle' => 'far'],
                                    ]
                            ]
                    ],
            ]);
            ?>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>