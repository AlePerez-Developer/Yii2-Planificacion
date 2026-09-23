<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use common\models\Estado;
use common\models\seguridad\Menu;
use common\models\seguridad\UsuarioUnidadMenuPermiso;
use Yii;

class PermisoMenuService
{
    public function listar(string $idUsuario, string $idUnidad, string $idGestion, string $idEstadoPoa): array
    {
        $menus = Menu::find()
            ->select(['IdMenu', 'Nombre', 'Ruta', 'CodigoPermiso', 'Orden'])
            ->where(['CodigoEstado' => Estado::ESTADO_VIGENTE, 'Visible' => 1])
            ->orderBy(['Orden' => SORT_ASC, 'Nombre' => SORT_ASC])
            ->asArray()
            ->all();

        $permisos = UsuarioUnidadMenuPermiso::find()
            ->where([
                'IdUsuario' => $idUsuario,
                'IdUnidadEjecutora' => $idUnidad,
                'IdGestion' => $idGestion,
                'IdEstadoPoa' => $idEstadoPoa,
            ])
            ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->indexBy('IdMenu')
            ->asArray()
            ->all();

        foreach ($menus as &$menu) {
            $permiso = $permisos[$menu['IdMenu']] ?? null;
            $menu['PuedeVer'] = (int)($permiso['PuedeVer'] ?? 0);
            $menu['PuedeCrear'] = (int)($permiso['PuedeCrear'] ?? 0);
            $menu['PuedeEditar'] = (int)($permiso['PuedeEditar'] ?? 0);
            $menu['PuedeEliminar'] = (int)($permiso['PuedeEliminar'] ?? 0);
        }
        unset($menu);

        return ResponseHelper::success($menus);
    }

    public function guardar(
        string $idUsuario,
        string $idUnidad,
        string $idGestion,
        string $idEstadoPoa,
        array $items
    ): array {
        if ($idUsuario === '' || $idUnidad === '' || $idGestion === '' || $idEstadoPoa === '') {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'Debe seleccionar usuario, unidad, gestión y estado POA.',
                400
            );
        }

        $registra = Yii::$app->user->id;
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($items as $item) {
                $idMenu = (string)($item['idMenu'] ?? '');
                if ($idMenu === '') {
                    continue;
                }
                $modelo = UsuarioUnidadMenuPermiso::find()
                    ->where([
                        'IdUsuario' => $idUsuario,
                        'IdUnidadEjecutora' => $idUnidad,
                        'IdMenu' => $idMenu,
                        'IdGestion' => $idGestion,
                        'IdEstadoPoa' => $idEstadoPoa,
                    ])
                    ->one();
                if ($modelo === null) {
                    $modelo = new UsuarioUnidadMenuPermiso([
                        'IdUsuario' => $idUsuario,
                        'IdUnidadEjecutora' => $idUnidad,
                        'IdMenu' => $idMenu,
                        'IdGestion' => $idGestion,
                        'IdEstadoPoa' => $idEstadoPoa,
                    ]);
                }
                $modelo->PuedeVer = (int)($item['puedeVer'] ?? 0);
                $modelo->PuedeCrear = (int)($item['puedeCrear'] ?? 0);
                $modelo->PuedeEditar = (int)($item['puedeEditar'] ?? 0);
                $modelo->PuedeEliminar = (int)($item['puedeEliminar'] ?? 0);
                $modelo->CodigoEstado = Estado::ESTADO_VIGENTE;
                $modelo->Usuario = $registra;
                if (!$modelo->save()) {
                    throw new ValidationException(
                        Yii::$app->params['ERROR_ENVIO_DATOS'],
                        $modelo->getErrors(),
                        400
                    );
                }
            }
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        return ResponseHelper::success(true, 'Permisos guardados.');
    }
}
