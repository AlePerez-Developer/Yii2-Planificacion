<?php

namespace app\modules\Planificacion\common\helpers;

use app\modules\Planificacion\common\exceptions\ValidationException;
use common\models\Estado;
use common\models\seguridad\UsuarioUnidadMenuPermiso;
use Yii;

class MenuPermisoHelper
{
    public static function normalizar(string $ruta): string
    {
        $ruta = strtolower(trim($ruta));
        $ruta = ltrim($ruta, '/');
        return $ruta;
    }

    /**
     * @return array<string, bool>|null null = sin filas de permiso (no restringir)
     */
    public static function rutasVisibles(): ?array
    {
        $filas = self::filas();
        if ($filas === null) {
            return null;
        }
        if ($filas === []) {
            return [];
        }

        $visibles = [];
        foreach ($filas as $fila) {
            if ((int)($fila['PuedeVer'] ?? 0) !== 1) {
                continue;
            }
            $visibles[self::normalizar((string)$fila['Ruta'])] = true;
        }
        return $visibles;
    }

    public static function puedeVer(string $ruta, ?array $visibles = null): bool
    {
        if ($visibles === null) {
            $visibles = self::rutasVisibles();
        }
        if ($visibles === null) {
            return true;
        }
        return isset($visibles[self::normalizar($ruta)]);
    }

    /**
     * @return array{ver: bool, crear: bool, editar: bool, eliminar: bool}
     */
    public static function permisosPagina(): array
    {
        $permitido = ['ver' => true, 'crear' => true, 'editar' => true, 'eliminar' => true];
        $filas = self::filas();
        if ($filas === null) {
            return $permitido;
        }

        $ruta = self::normalizar(self::rutaPaginaActual());
        $base = explode('?', $ruta)[0];
        $encontrado = null;
        foreach ($filas as $fila) {
            $menu = self::normalizar((string)$fila['Ruta']);
            if ($menu === $ruta) {
                $encontrado = $fila;
                break;
            }
            if ($encontrado === null && $menu === $base) {
                $encontrado = $fila;
            }
        }
        if ($encontrado === null) {
            return ['ver' => true, 'crear' => false, 'editar' => false, 'eliminar' => false];
        }

        return [
            'ver' => (int)($encontrado['PuedeVer'] ?? 0) === 1,
            'crear' => (int)($encontrado['PuedeCrear'] ?? 0) === 1,
            'editar' => (int)($encontrado['PuedeEditar'] ?? 0) === 1,
            'eliminar' => (int)($encontrado['PuedeEliminar'] ?? 0) === 1,
        ];
    }

    public static function asegurar(string $accion): void
    {
        $permisos = self::permisosPagina();
        $clave = match ($accion) {
            'crear' => 'crear',
            'eliminar' => 'eliminar',
            default => 'editar',
        };
        if ($permisos[$clave]) {
            return;
        }

        throw new ValidationException(
            Yii::$app->params['ERROR_ENVIO_DATOS'],
            'No tiene permiso para realizar esta acción en el menú actual.',
            403
        );
    }

    public static function rutaPaginaActual(): string
    {
        $controller = Yii::$app->controller;
        $ruta = $controller ? $controller->uniqueId . '/index' : '';
        $formulario = Yii::$app->request->get('formulario', Yii::$app->request->post('formulario', ''));
        if ($formulario !== '' && $formulario !== null) {
            $ruta .= '?formulario=' . (int)$formulario;
        }
        return $ruta;
    }

    /**
     * @return array<int, array<string, mixed>>|null
     */
    private static function filas(): ?array
    {
        $usuario = Yii::$app->user->identity;
        $contexto = Yii::$app->userContext->contexto();
        if (!$usuario || !$contexto) {
            return [];
        }

        $idUsuario = (string)$usuario->IdUsuario;
        $idUnidad = (string)($contexto->IdUnidadEjecutora ?? '');
        $idGestion = (string)($contexto->IdGestion ?? '');
        $idEstado = (string)($contexto->IdEstadoPoa ?? '');
        if ($idGestion === '') {
            return [];
        }

        $query = UsuarioUnidadMenuPermiso::find()->alias('P')
            ->innerJoin('seguridad.Menus M', 'M.IdMenu = P.IdMenu')
            ->select([
                'M.Ruta',
                'P.PuedeVer',
                'P.PuedeCrear',
                'P.PuedeEditar',
                'P.PuedeEliminar',
            ])
            ->where([
                'P.IdUsuario' => $idUsuario,
                'P.IdGestion' => $idGestion,
                'P.CodigoEstado' => Estado::ESTADO_VIGENTE,
                'M.CodigoEstado' => Estado::ESTADO_VIGENTE,
            ]);

        if ($idEstado !== '') {
            $query->andWhere(['P.IdEstadoPoa' => $idEstado]);
        }
        if ($idUnidad !== '') {
            $query->andWhere(['P.IdUnidadEjecutora' => $idUnidad]);
        }

        $filas = $query->asArray()->all();
        return $filas === [] ? null : $filas;
    }
}
