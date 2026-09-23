<?php

namespace app\modules\Planificacion\common\helpers;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\models\ControlEnvioPoa;
use app\modules\Planificacion\models\CronogramaPoa;
use Yii;

class PoaEdicionHelper
{
    public static function estado(?string $idUnidadEjecutora = null): array
    {
        $contexto = Yii::$app->userContext->contexto();
        $idUnidad = $idUnidadEjecutora ?: (string)($contexto?->IdUnidadEjecutora ?? '');
        $cronograma = CronogramaPoa::vigenteHoy();
        $enCronograma = $cronograma !== null;
        $enviado = false;
        if ($enCronograma && $idUnidad !== '') {
            $enviado = ControlEnvioPoa::envioActivo($idUnidad, $cronograma->IdCronograma) !== null;
        }

        return [
            'enCronograma' => $enCronograma,
            'enviado' => $enviado,
            'puedeEditarPoa' => $enCronograma && !$enviado && $idUnidad !== '',
            'puedeEditar' => $enCronograma && !$enviado && $idUnidad !== '',
            'puedeEnviar' => $enCronograma && !$enviado && $idUnidad !== '',
            'cronograma' => $cronograma,
        ];
    }

    /**
     * El bloqueo de cronograma/envío tiene prioridad sobre el permiso de menú.
     *
     * @return array{puedeCrear: bool, puedeEditar: bool, puedeEliminar: bool, enCronograma: bool, enviado: bool, puedeEditarPoa: bool}
     */
    public static function accionesUi(?string $idUnidadEjecutora = null): array
    {
        $estado = self::estado($idUnidadEjecutora);
        $lock = (bool)$estado['puedeEditarPoa'];
        $permisos = MenuPermisoHelper::permisosPagina();

        return [
            'enCronograma' => $estado['enCronograma'],
            'enviado' => $estado['enviado'],
            'puedeEditarPoa' => $lock,
            'puedeCrear' => $lock && $permisos['crear'],
            'puedeEditar' => $lock && $permisos['editar'],
            'puedeEliminar' => $lock && $permisos['eliminar'],
        ];
    }

    public static function registrarFlagJs(): void
    {
        $ui = self::accionesUi();
        $crear = $ui['puedeCrear'] ? 'true' : 'false';
        $editar = $ui['puedeEditar'] ? 'true' : 'false';
        $eliminar = $ui['puedeEliminar'] ? 'true' : 'false';
        Yii::$app->view->registerJs(
            "window.poaPuedeCrear = {$crear}; window.poaPuedeEditar = {$editar}; window.poaPuedeEliminar = {$eliminar};",
            \yii\web\View::POS_HEAD
        );
    }

    public static function asegurarEdicion(?string $idUnidadEjecutora = null): void
    {
        $estado = self::estado($idUnidadEjecutora);
        if ($estado['puedeEditar']) {
            return;
        }

        $idUnidad = $idUnidadEjecutora ?: (string)(Yii::$app->userContext->contexto()?->IdUnidadEjecutora ?? '');
        if (!$estado['enCronograma']) {
            $mensaje = 'El cronograma POA no está vigente. No se pueden registrar ni modificar ítems u operaciones.';
        } elseif ($idUnidad === '') {
            $mensaje = 'Debe seleccionar una unidad ejecutora para editar el POA.';
        } else {
            $mensaje = 'La unidad ya envió el POA. Debe eliminar el envío para realizar cambios.';
        }

        throw new ValidationException(
            Yii::$app->params['ERROR_ENVIO_DATOS'],
            $mensaje,
            400
        );
    }
}
