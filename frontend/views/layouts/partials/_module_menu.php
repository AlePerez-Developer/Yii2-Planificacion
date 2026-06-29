<?php

use yii\helpers\Url;

$modulo = Yii::$app->userContext->moduloActivo();

if (!$modulo) {
    return;
}

$menus = $modulo->menus;

?>

<nav class="module-navbar">



    <div class="form-navigation">

        <!-- Primera fila -->
        <div class="menu-row">

            <div class="dropdown">
                <button class="dropbtn">Pei ▾</button>
                <div class="dropdown-content">
                    <a href="<?= Url::to(['/Planificacion/peis/index']) ?>">Manejo Pei</a>
                    <div class="dropdown-divider"></div>
                    <a href="<?= Url::to(['/Planificacion/area-estrategica/index']) ?>">Areas Estrategicas</a>
                    <a href="<?= Url::to(['/Planificacion/politica-estrategica/index']) ?>">Politicas Estrategicas</a>
                </div>
            </div>

            <div class="dropdown">
                <button class="dropbtn">Estructura Organizacional ▾</button>
                <div class="dropdown-content">
                    <a href="<?= Url::to(['/Planificacion/da/index']) ?>">Direcciones Administrativas</a>
                    <a href="<?= Url::to(['/Planificacion/ue/index']) ?>">Unidades Ejecutorias</a>
                    <a href="<?= Url::to(['/Planificacion/programa/index']) ?>">Programas</a>
                    <a href="<?= Url::to(['/Planificacion/proyecto/index']) ?>">Proyectos</a>
                    <a href="<?= Url::to(['/Planificacion/actividad/index']) ?>">Actividades</a>
                    <div class="dropdown-divider"></div>
                    <a href="<?= Url::to(['/Planificacion/llave-presupuestaria/index']) ?>">Llaves Presupuestarias</a>
                </div>
            </div>

            <div class="dropdown">
                <button class="dropbtn">Indicadores Estrategicos ▾</button>
                <div class="dropdown-content">
                    <a href="<?= Url::to(['/Planificacion/indicador-estrategico/index']) ?>">Manejar Indicadores</a>
                    <div class="dropdown-divider"></div>
                    <a href="<?= Url::to(['/Planificacion/obj-estrategico/index']) ?>">Programacion Anual</a>
                    <a href="<?= Url::to(['/Planificacion/obj-estrategico/index']) ?>">Programacion Trimestral</a>
                    <div class="dropdown-divider"></div>
                    <a href="<?= Url::to(['/Planificacion/indicador-estrategico-accion/index']) ?>">Asignar Acciones Estrategicas</a>
                </div>
            </div>

            <div class="dropdown">
                <button class="dropbtn">Catalogos ▾</button>
                <div class="dropdown-content">
                    <a href="<?= Url::to(['/Planificacion/estado-poa/index']) ?>">Estados Poa</a>
                    <a href="<?= Url::to(['/Planificacion/gasto/index']) ?>">Gastos</a>
                    <a href="<?= Url::to(['/Planificacion/accion-estrategica/index']) ?>">Acciones Estrategicas</a>
                </div>
            </div>

        </div>

        <!-- Segunda fila -->
        <div class="forms-row">

            <a href="<?= Url::to(['/Planificacion/obj-estrategico/index']) ?>">Form 1</a>
            <a href="<?= Url::to(['/Planificacion/obj-institucional/index']) ?>">Form 2</a>
            <a href="#">Form 3</a>
            <a href="#">Form 4</a>
            <a href="#">Form 5</a>
            <a href="#">Form 6</a>
            <a href="#">Form 7</a>
            <a href="#">Form 8</a>
            <a href="#">Form 9</a>
            <a href="#">Form 10</a>
            <a href="#">Form 11</a>
            <a href="#">Form 12</a>
            <a href="#">Form 13</a>
            <a href="#">Form 14</a>
            <a href="#">Form 15</a>

        </div>

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

    .dropdown-divider{
        height:1px;
        background:#dcdcdc;
        margin:6px 0;
    }

</style>
