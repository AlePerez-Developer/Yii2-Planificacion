<?php

namespace app\modules\Planificacion\common\traits;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\MenuPermisoHelper;
use app\modules\Planificacion\common\helpers\PoaEdicionHelper;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use Yii;
use yii\web\Response;

trait ControlaEdicionPoa
{
    /** @var array<string, string> actionId => crear|editar|eliminar */
    protected array $accionesEdicionPoa = [
        'guardar' => 'crear',
        'actualizar' => 'editar',
        'eliminar' => 'eliminar',
        'cambiar-estado' => 'editar',
        'guardar-meta-trimestral' => 'editar',
        'guardar-meta' => 'editar',
        'actualizar-meta' => 'editar',
    ];

    public function beforeAction($action): bool
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        $tipo = $this->mapearAccionEdicionPoa($action->id);
        if ($tipo === null) {
            return true;
        }

        try {
            PoaEdicionHelper::asegurarEdicion();
            MenuPermisoHelper::asegurar($tipo);
        } catch (ValidationException $e) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            Yii::$app->response->statusCode = $e->getCode() ?: 400;
            Yii::$app->response->data = ResponseHelper::error(
                $e->getMessage(),
                ['errores' => $e->getErrors()]
            );
            return false;
        }

        return true;
    }

    protected function mapearAccionEdicionPoa(string $actionId): ?string
    {
        return $this->accionesEdicionPoa[$actionId] ?? null;
    }
}
