<aside class="main-sidebar sidebar-light-primary elevation-4">
    <!-- Brand Logo -->
    <a  class="brand-link">
        <img src="img/logo.jpg" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">USFX - Dtic</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="http://201.131.45.4/declaracionjurada/archivos/fotografias/F_A_<?= trim(Yii::$app->user->identity->persona->IdPersona)?>.jpg" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block"><?=  Yii::$app->user->identity->persona->nombreCompleto      ?></a>
            </div>
        </div>

        <!-- SidebarSearch Form -->
        <!-- href be escaped -->
        <!-- <div class="form-inline">
            <div class="input-group" data-widget="sidebar-search">
                <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
                <div class="input-group-append">
                    <button class="btn btn-sidebar">
                        <i class="fas fa-search fa-fw"></i>
                    </button>
                </div>
            </div>
        </div> -->

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <?php
            echo \hail812\adminlte\widgets\Menu::widget([
                'items' => [
                    /*[
                        'label' => 'Planificacion',
                        'icon' => 'th',
                        'badge' => '<span class="right badge badge-info"></span>',
                        'items' => [
                            ['label' => 'Pei', 'url' => ['/Planificacion/peis/index'], 'iconStyle' => 'far'],
                            ['label' => 'Objetivo Estrategico', 'url' => ['/Planificacion/obj-estrategico/index'], 'iconStyle' => 'far'],
                            ['label' => 'Indicador Estrategico', 'url' => ['/Planificacion/indicador-estrategico/index'], 'iconStyle' => 'far'],
                            ['label' => 'Objetivo Institucional', 'url' => ['/Planificacion/obj-institucional/index'], 'iconStyle' => 'far'],
                            ['label' => 'Objetivo Especifico', 'url' => ['/Planificacion/obj-especifico/index'], 'iconStyle' => 'far'],
                            ['label' => 'indicadores', 'url' => ['/Planificacion/indicador/index'], 'iconStyle' => 'far'],
                            ['label' => 'Unidades', 'url' => ['/Planificacion/unidad/index'], 'iconStyle' => 'far'],
                            ['label' => 'Programas', 'url' => ['/Planificacion/programa/index'], 'iconStyle' => 'far'],
                            ['label' => 'Proyectos', 'url' => ['/Planificacion/proyecto/index'], 'iconStyle' => 'far'],
                            ['label' => 'Actividades', 'url' => ['/Planificacion/actividad/index'], 'iconStyle' => 'far'],
                            ['label' => 'Aperturas Programaticas', 'url' => ['/Planificacion/apertura-programatica/index'], 'iconStyle' => 'far'],
                        ]
                    ],*/
                    [
                        'label' => 'Planificacion CH',
                        'icon' => 'th',
                        'badge' => '<span class="right badge badge-info"></span>',
                        'items' => [
                            ['label' => 'Planificar', 'url' => ['/PlanificacionCH/planificar-carga-horaria/index'], 'iconStyle' => 'far'],
                            ['label' => 'Planificar Matriciales', 'url' => ['/PlanificacionCH/planificar-carga-horaria-matricial/index'], 'iconStyle' => 'far'],
                        ]
                    ],
                   /* [
                        'label' => 'Soa',
                        'icon' => 'th',
                        'badge' => '<span class="right badge badge-info"></span>',
                        'items' => [
                            ['label' => 'Unidades Soa', 'url' => ['/Planificacion/unidades-soa/index'], 'iconStyle' => 'far'],
                            ['label' => 'Cargos', 'url' => ['/Planificacion/cargos/index'], 'iconStyle' => 'far'],
                            ['label' => 'Unidades - Cargos', 'url' => ['/Planificacion/unidades-cargos/index'], 'iconStyle' => 'far'],
                        ]
                    ]*/
                ],
            ]);
            ?>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>