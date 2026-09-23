<?php

use app\modules\Planificacion\common\helpers\MenuPermisoHelper;
use yii\helpers\Url;

$modulo = Yii::$app->userContext->moduloActivo();

if (!$modulo) {
    return;
}

$contexto = Yii::$app->userContext->contexto();
$hayGestion = (string)($contexto?->IdGestion ?? '') !== '';
$hayUnidad = (string)($contexto?->IdUnidadEjecutora ?? '') !== '';

if (!$hayGestion && !$hayUnidad) {
    return;
}

$visibles = MenuPermisoHelper::rutasVisibles();
$ver = static fn(string $ruta): bool => MenuPermisoHelper::puedeVer($ruta, $visibles);
$unidad = $contexto?->unidadEjecutora;
$esPadre = (bool)$unidad?->esUnidadPadre();
$usaIngresos = (bool)$unidad?->usaIngresosGlobales();

?>

<nav class="module-navbar">



    <div class="form-navigation">

        <?php if ($hayGestion): ?>
        <!-- Primera fila -->
        <div class="menu-row<?= $hayUnidad ? '' : ' menu-row-only' ?>">

            <?php if ($ver('Planificacion/peis/index') || $ver('Planificacion/obj-estrategico/index') || $ver('Planificacion/area-estrategica/index') || $ver('Planificacion/politica-estrategica/index') || $ver('Planificacion/accion-estrategica/index') || $ver('Planificacion/indicador-estrategico-accion/index')): ?>
            <div class="dropdown">
                <button class="dropbtn">Pei ▾</button>
                <div class="dropdown-content">
                    <?php if ($ver('Planificacion/peis/index')): ?><a href="<?= Url::to(['/Planificacion/peis/index']) ?>">Manejo Pei</a><?php endif; ?>
                    <div class="dropdown-divider"></div>
                    <?php if ($ver('Planificacion/obj-estrategico/index')): ?><a href="<?= Url::to(['/Planificacion/obj-estrategico/index']) ?>">Objetivos Estrategicos</a><?php endif; ?>
                    <div class="dropdown-divider"></div>
                    <?php if ($ver('Planificacion/area-estrategica/index')): ?><a href="<?= Url::to(['/Planificacion/area-estrategica/index']) ?>">Areas Estrategicas</a><?php endif; ?>
                    <?php if ($ver('Planificacion/politica-estrategica/index')): ?><a href="<?= Url::to(['/Planificacion/politica-estrategica/index']) ?>">Politicas Estrategicas</a><?php endif; ?>
                    <?php if ($ver('Planificacion/accion-estrategica/index')): ?><a href="<?= Url::to(['/Planificacion/accion-estrategica/index']) ?>">Acciones Estrategicas</a><?php endif; ?>
                    <div class="dropdown-divider"></div>
                    <?php if ($ver('Planificacion/indicador-estrategico-accion/index')): ?><a href="<?= Url::to(['/Planificacion/indicador-estrategico-accion/index']) ?>">Asignar Acciones Estrategicas</a><?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($ver('Planificacion/da/index') || $ver('Planificacion/unidad-ejecutora/index') || $ver('Planificacion/programa/index') || $ver('Planificacion/proyecto/index') || $ver('Planificacion/actividad/index') || $ver('Planificacion/llave-presupuestaria/index')): ?>
            <div class="dropdown">
                <button class="dropbtn">Estructura Organizacional ▾</button>
                <div class="dropdown-content">
                    <?php if ($ver('Planificacion/da/index')): ?><a href="<?= Url::to(['/Planificacion/da/index']) ?>">Direcciones Administrativas</a><?php endif; ?>
                    <?php if ($ver('Planificacion/unidad-ejecutora/index')): ?><a href="<?= Url::to(['/Planificacion/unidad-ejecutora/index']) ?>">Unidades Ejecutorias</a><?php endif; ?>
                    <?php if ($ver('Planificacion/programa/index')): ?><a href="<?= Url::to(['/Planificacion/programa/index']) ?>">Programas</a><?php endif; ?>
                    <?php if ($ver('Planificacion/proyecto/index')): ?><a href="<?= Url::to(['/Planificacion/proyecto/index']) ?>">Proyectos</a><?php endif; ?>
                    <?php if ($ver('Planificacion/actividad/index')): ?><a href="<?= Url::to(['/Planificacion/actividad/index']) ?>">Actividades</a><?php endif; ?>
                    <div class="dropdown-divider"></div>
                    <?php if ($ver('Planificacion/llave-presupuestaria/index')): ?><a href="<?= Url::to(['/Planificacion/llave-presupuestaria/index']) ?>">Llaves Presupuestarias</a><?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($ver('Planificacion/indicador-estrategico/index') || $ver('Planificacion/indicador-estrategico-programacion-anual/index') || $ver('Planificacion/indicador-estrategico-programacion-trimestral/index')): ?>
            <div class="dropdown">
                <button class="dropbtn">Indicadores Estrategicos ▾</button>
                <div class="dropdown-content">
                    <?php if ($ver('Planificacion/indicador-estrategico/index')): ?><a href="<?= Url::to(['/Planificacion/indicador-estrategico/index']) ?>">Manejar Indicadores</a><?php endif; ?>
                    <div class="dropdown-divider"></div>
                    <?php if ($ver('Planificacion/indicador-estrategico-programacion-anual/index')): ?><a href="<?= Url::to(['/Planificacion/indicador-estrategico-programacion-anual/index']) ?>">Programacion Anual</a><?php endif; ?>
                    <?php if ($ver('Planificacion/indicador-estrategico-programacion-trimestral/index')): ?><a href="<?= Url::to(['/Planificacion/indicador-estrategico-programacion-trimestral/index']) ?>">Programacion Trimestral</a><?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($ver('Planificacion/indicador-poa/index') || $ver('Planificacion/indicador-poa-programacion-anual/index') || $ver('Planificacion/indicador-poa-programacion-trimestral/index')): ?>
            <div class="dropdown">
                <button class="dropbtn">Indicadores POA ▾</button>
                <div class="dropdown-content">
                    <?php if ($ver('Planificacion/indicador-poa/index')): ?><a href="<?= Url::to(['/Planificacion/indicador-poa/index']) ?>">Manejar Indicadores</a><?php endif; ?>
                    <div class="dropdown-divider"></div>
                    <?php if ($ver('Planificacion/indicador-poa-programacion-anual/index')): ?><a href="<?= Url::to(['/Planificacion/indicador-poa-programacion-anual/index']) ?>">Programacion Anual</a><?php endif; ?>
                    <?php if ($ver('Planificacion/indicador-poa-programacion-trimestral/index')): ?><a href="<?= Url::to(['/Planificacion/indicador-poa-programacion-trimestral/index']) ?>">Programacion Trimestral</a><?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($ver('Planificacion/estado-poa/index') || $ver('Planificacion/gasto/index') || $ver('Planificacion/fuente/index') || $ver('Planificacion/organismo/index') || $ver('Planificacion/partida/index') || $ver('Planificacion/cronograma-poa/index') || $ver('Planificacion/control-envio-poa/index') || $ver('Planificacion/usuario-seguridad/index') || $ver('Planificacion/permiso-menu/index')): ?>
            <div class="dropdown">
                <button class="dropbtn">Catalogos ▾</button>
                <div class="dropdown-content">
                    <?php if ($ver('Planificacion/estado-poa/index')): ?><a href="<?= Url::to(['/Planificacion/estado-poa/index']) ?>">Estados Poa</a><?php endif; ?>
                    <?php if ($ver('Planificacion/gasto/index')): ?><a href="<?= Url::to(['/Planificacion/gasto/index']) ?>">Gastos</a><?php endif; ?>
                    <?php if ($ver('Planificacion/fuente/index')): ?><a href="<?= Url::to(['/Planificacion/fuente/index']) ?>">Fuentes</a><?php endif; ?>
                    <?php if ($ver('Planificacion/organismo/index')): ?><a href="<?= Url::to(['/Planificacion/organismo/index']) ?>">Organismos</a><?php endif; ?>
                    <?php if ($ver('Planificacion/partida/index')): ?><a href="<?= Url::to(['/Planificacion/partida/index']) ?>">Partidas</a><?php endif; ?>
                    <?php if ($ver('Planificacion/cronograma-poa/index')): ?><a href="<?= Url::to(['/Planificacion/cronograma-poa/index']) ?>">Cronograma POA</a><?php endif; ?>
                    <?php if ($ver('Planificacion/control-envio-poa/index')): ?><a href="<?= Url::to(['/Planificacion/control-envio-poa/index']) ?>">Envíos POA</a><?php endif; ?>
                    <?php if ($ver('Planificacion/usuario-seguridad/index')): ?><a href="<?= Url::to(['/Planificacion/usuario-seguridad/index']) ?>">Usuarios</a><?php endif; ?>
                    <?php if ($ver('Planificacion/permiso-menu/index')): ?><a href="<?= Url::to(['/Planificacion/permiso-menu/index']) ?>">Permisos de menú</a><?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>
        <?php endif; ?>

        <?php if ($hayUnidad): ?>
        <!-- Segunda fila -->
        <div class="forms-row">

            <?php if ($ver('Planificacion/obj-estrategico/index')): ?><a href="<?= Url::to(['/Planificacion/obj-estrategico/index']) ?>">Form 1</a><?php endif; ?>
            <?php if ($ver('Planificacion/foda-institucion/index') || $ver('Planificacion/foda-unidad/index')): ?>
            <div class="dropdown form-dropdown">
                <button type="button" class="form-dropbtn">Form 2 ▾</button>
                <div class="dropdown-content">
                    <?php if ($ver('Planificacion/foda-institucion/index')): ?><a href="<?= Url::to(['/Planificacion/foda-institucion/index']) ?>">FODA</a><?php endif; ?>
                    <?php if ($ver('Planificacion/foda-unidad/index')): ?><a href="<?= Url::to(['/Planificacion/foda-unidad/index']) ?>">Foda Unidad</a><?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            <?php if ($ver('Planificacion/obj-institucional/index')): ?><a href="<?= Url::to(['/Planificacion/obj-institucional/index']) ?>">Form 3</a><?php endif; ?>
            <?php if ($ver('Planificacion/obj-especifico/index')): ?><a href="<?= Url::to(['/Planificacion/obj-especifico/index']) ?>">Form 4</a><?php endif; ?>
            <?php if ($ver('Planificacion/operacion/index')): ?><a href="<?= Url::to(['/Planificacion/operacion/index']) ?>">Form 5</a><?php endif; ?>
            <?php if ($ver('Planificacion/items-catalogados/index?formulario=7')): ?><a href="<?= Url::to(['/Planificacion/items-catalogados/index', 'formulario' => 7]) ?>">Form 7</a><?php endif; ?>
            <?php if ($ver('Planificacion/items-catalogados/index?formulario=8')): ?><a href="<?= Url::to(['/Planificacion/items-catalogados/index', 'formulario' => 8]) ?>">Form 8</a><?php endif; ?>
            <?php if ($ver('Planificacion/items-catalogados/index?formulario=9')): ?><a href="<?= Url::to(['/Planificacion/items-catalogados/index', 'formulario' => 9]) ?>">Form 9</a><?php endif; ?>
            <?php if ($ver('Planificacion/items-descatalogados/index?formulario=10')): ?><a href="<?= Url::to(['/Planificacion/items-descatalogados/index', 'formulario' => 10]) ?>">Form 10</a><?php endif; ?>
            <?php if ($ver('Planificacion/items-descatalogados/index?formulario=11')): ?><a href="<?= Url::to(['/Planificacion/items-descatalogados/index', 'formulario' => 11]) ?>">Form 11</a><?php endif; ?>
            <?php if ($ver('Planificacion/items-descatalogados/index?formulario=12')): ?><a href="<?= Url::to(['/Planificacion/items-descatalogados/index', 'formulario' => 12]) ?>">Form 12</a><?php endif; ?>
            <?php if ($ver('Planificacion/items-descatalogados/index?formulario=13')): ?><a href="<?= Url::to(['/Planificacion/items-descatalogados/index', 'formulario' => 13]) ?>">Form 13</a><?php endif; ?>
            <?php if ($ver('Planificacion/items-descatalogados/index?formulario=14')): ?><a href="<?= Url::to(['/Planificacion/items-descatalogados/index', 'formulario' => 14]) ?>">Form 14</a><?php endif; ?>
            <?php if (($usaIngresos && $esPadre && $ver('Planificacion/ingreso/index')) || (!$usaIngresos && $ver('Planificacion/techo-unidad/index'))): ?>
            <div class="dropdown form-dropdown">
                <button type="button" class="form-dropbtn">Form 15 ▾</button>
                <div class="dropdown-content">
                    <?php if ($usaIngresos && $esPadre && $ver('Planificacion/ingreso/index')): ?>
                        <a href="<?= Url::to(['/Planificacion/ingreso/index']) ?>">Registrar Ingreso</a>
                    <?php endif; ?>
                    <?php if (!$usaIngresos && $ver('Planificacion/techo-unidad/index')): ?>
                        <a href="<?= Url::to(['/Planificacion/techo-unidad/index']) ?>">Asignar Techo</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            <?php if ($ver('Planificacion/enviar-poa/index')): ?><a href="<?= Url::to(['/Planificacion/enviar-poa/index']) ?>">Enviar POA</a><?php endif; ?>

        </div>
        <?php endif; ?>

    </div>

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


    .form-navigation{
        width:100%;
        background:#ffffff;
        border-radius:10px;
        padding:5px;
        box-shadow:0 3px 10px rgba(0,0,0,.10);
    }

    /*======================
        FILA MENUS
    =======================*/

    .menu-row{
        display:flex;
        justify-content:flex-start;
        gap:10px;
        margin-bottom:15px;
    }

    .menu-row-only{
        margin-bottom:0;
    }

    .dropdown{
        position:relative;
    }

    .dropbtn{
        min-width:160px;
        padding:5px 9px;
        background:#0d6efd;
        color:white;
        border:none;
        border-radius:6px;
        cursor:pointer;
        font-size:13px;
        transition:.25s;
    }

    .dropbtn:hover{
        background:#0b5ed7;
    }

    .dropdown-content{
        display:none;
        position:absolute;
        top:100%;
        left:0;
        width:100%;
        background:white;
        border-radius:6px;
        box-shadow:0 6px 18px rgba(0,0,0,.15);
        overflow:hidden;
        z-index:1000;
        font-size: 13px;
    }

    .dropdown-content a{
        display:block;
        padding:12px;
        text-decoration:none;
        color:#444;
        transition:.2s;
    }

    .dropdown-content a:hover{
        background:#f2f2f2;
        color:#0d6efd;
    }

    .dropdown:hover .dropdown-content{
        display:block;
    }

    /*======================
        FORMULARIOS
    =======================*/

    .forms-row{
        display:flex;
        flex-wrap:wrap;
        gap:5px;
        justify-content:center;
    }

    .forms-row a{
        text-decoration:none;
        background:#f4f6f9;
        color:#444;
        padding:8px 16px;
        border-radius:20px;
        border:1px solid #dcdcdc;
        transition:.25s;
        font-size:13px;
    }

    .forms-row a:hover{
        background:#0d6efd;
        color:white;
        border-color:#0d6efd;
    }

    .form-dropbtn {
        background:#f4f6f9;
        color:#444;
        padding:8px 16px;
        border-radius:20px;
        border:1px solid #dcdcdc;
        cursor:pointer;
        font-size:13px;
        transition:.25s;
    }

    .form-dropbtn:hover {
        background:#0d6efd;
        color:white;
        border-color:#0d6efd;
    }

    .form-dropdown .dropdown-content {
        width:180px;
    }

    .forms-row .form-dropdown .dropdown-content a {
        display:block;
        background:#fff;
        color:#444;
        padding:10px 12px;
        border:0;
        border-radius:0;
    }

    .forms-row .form-dropdown .dropdown-content a:hover {
        background:#f2f2f2;
        color:#0d6efd;
    }

    .dropdown-divider{
        height:1px;
        background:#dcdcdc;
        margin:6px 0;
    }

</style>
