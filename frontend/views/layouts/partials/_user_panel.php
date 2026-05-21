<?php
use yii\helpers\Html;

$usuario = Yii::$app->user->identity;
$persona = $usuario->persona;

$nombreCompleto =
    $persona->Nombres . ' ' .
    $persona->Paterno . ' ' .
    $persona->Materno;

?>

<div class="user-panel-custom" style="background-color: ">

    <div class="image">

        <img
                src="http://201.131.45.4/declaracionjurada/archivos/fotografias/F_A_<?=trim($persona->IdPersona)?>.jpg"
                class="img-circle elevation-2"
                alt="Usuario"
        >

    </div>

    <div class="info">

        <div class="user-name">
            <?= Html::encode($nombreCompleto) ?>
        </div>

        <div class="user-details">

            <div>
                CI:
                <?= Html::encode($persona->IdPersona ?? '-') ?>
            </div>

            <div>
                <?= Html::encode($usuario->CodigoUsuario ?? '-') ?>
            </div>

            <div>
                @<?= Html::encode($usuario->Nick ?? '-') ?>
            </div>

        </div>

    </div>

</div>

<style>

    .user-panel-custom {

        display: flex;

        align-items: center;

        padding: 14px 12px;

        margin: 10px;

        border-radius: 14px;

        background: #2c3643;

        border: 1px solid #3b4655;

        box-shadow: 0 4px 10px rgba(0,0,0,0.25);

        overflow: hidden;

        transition: all .3s ease;
    }

    .user-panel-custom .image {

        flex-shrink: 0;
    }

    .user-panel-custom .image img {

        width: 54px;

        height: 54px;

        object-fit: cover;

        border-radius: 50%;

        border: 2px solid #5d6d7e;

        transition: all .3s ease;
    }

    .user-panel-custom .info {

        margin-left: 12px;

        overflow: hidden;

        transition: all .3s ease;
    }

    .user-panel-custom .user-name {

        color: #ffffff;

        font-weight: 600;

        font-size: 14px;

        line-height: 1.2;

        white-space: nowrap;

        overflow: hidden;

        text-overflow: ellipsis;
    }

    .user-panel-custom .user-details {

        margin-top: 6px;

        font-size: 11px;

        color: #c7d0d9;

        line-height: 1.6;
    }

    .user-panel-custom .user-details div {

        white-space: nowrap;

        overflow: hidden;

        text-overflow: ellipsis;
    }

    .sidebar-collapse .user-panel-custom {

        justify-content: center;

        padding: 10px 0;
    }

    .sidebar-collapse .user-panel-custom .info {

        opacity: 0;

        width: 0;

        margin-left: 0;

        overflow: hidden;
    }

    .sidebar-collapse .user-panel-custom .image img {

        width: 40px;

        height: 40px;
    }

    .user-panel-custom {

        border-top: 3px solid #3c8dbc;
    }


</style>