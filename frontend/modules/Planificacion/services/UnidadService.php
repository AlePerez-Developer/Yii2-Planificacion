<?php
namespace app\modules\Planificacion\services;

use app\modules\Planificacion\dao\UnidadDao;
use app\modules\Planificacion\models\Unidad;
use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\exceptions\BusinessException;
use common\models\Estado;
use yii\db\Exception as DbException;
use Yii;

class UnidadService
{
    public function listar(): array
    {
        $unidades = Unidad::find()
            ->select([
                'CodigoUnidad', 'Da', 'Ue', 'Descripcion', 'Organizacional',
                'FechaInicio', 'FechaFin', 'CodigoEstado', 'CodigoUsuario'
            ])
            ->where(['!=', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->orderBy(['Da' => SORT_ASC, 'Ue' => SORT_ASC])
            ->asArray()
            ->all();

        return [
            'data' => $unidades,
            'message' => 'Listado de unidades'
        ];
    }

    public function guardar(array $params): array
    {
        $this->validarRequeridos($params, ['da','ue','descripcion','organizacional','fechaInicio','fechaFin']);

        $unidad = new Unidad();
        // PK por DAO (tabla no identidad)
        $unidad->CodigoUnidad = UnidadDao::GenerarCodigoUnidad();
        $unidad->Da = $params['da'];
        $unidad->Ue = $params['ue'];
        $unidad->Descripcion = mb_strtoupper(trim($params['descripcion']), 'UTF-8');
        $unidad->Organizacional = intval($params['organizacional']);
        $unidad->FechaInicio = date('d/m/Y', strtotime($params['fechaInicio']));
        $unidad->FechaFin = date('d/m/Y', strtotime($params['fechaFin']));
        $unidad->CodigoEstado = Estado::ESTADO_VIGENTE;
        $unidad->CodigoUsuario = Yii::$app->user->identity->CodigoUsuario ?? null;

        if ($unidad->exist()) {
            throw new BusinessException(Yii::$app->params['ERROR_REGISTRO_EXISTE'] ?? 'El registro ya existe', 409);
        }

        $this->validarYGuardar($unidad);

        return [
            'data' => $unidad->getAttributes(),
            'message' => 'Unidad guardada correctamente'
        ];
    }

    public function actualizar(array $params): array
    {
        $this->validarRequeridos($params, ['codigoUnidad','da','ue','descripcion','organizacional','fechaInicio','fechaFin']);

        $unidad = Unidad::findOne($params['codigoUnidad']);
        if (!$unidad) {
            throw new BusinessException(Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'] ?? 'Registro no encontrado', 404);
        }

        // Asignar primero, luego validar duplicado (patrón aplicado en otros servicios)
        $unidad->Da = $params['da'];
        $unidad->Ue = $params['ue'];
        $unidad->Descripcion = mb_strtoupper(trim($params['descripcion']), 'UTF-8');
        $unidad->Organizacional = intval($params['organizacional']);
        $unidad->FechaInicio = date('d/m/Y', strtotime($params['fechaInicio']));
        $unidad->FechaFin = date('d/m/Y', strtotime($params['fechaFin']));

        if ($unidad->exist()) {
            throw new BusinessException(Yii::$app->params['ERROR_REGISTRO_EXISTE'] ?? 'El registro ya existe', 409);
        }

        $this->validarYGuardar($unidad);

        return [
            'data' => $unidad->getAttributes(),
            'message' => 'Unidad actualizada correctamente'
        ];
    }

    public function cambiarEstado(int $codigoUnidad): array
    {
        $unidad = Unidad::findOne($codigoUnidad);
        if (!$unidad) {
            throw new BusinessException(Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'] ?? 'Registro no encontrado', 404);
        }

        $unidad->CodigoEstado = ($unidad->CodigoEstado == Estado::ESTADO_VIGENTE)
            ? Estado::ESTADO_CADUCO
            : Estado::ESTADO_VIGENTE;

        if ($unidad->update() === false) {
            throw new DbException(Yii::$app->params['ERROR_EJECUCION_SQL'] ?? 'Error al actualizar estado');
        }

        return [
            'data' => $unidad->getAttributes(),
            'message' => 'Estado actualizado'
        ];
    }

    public function eliminar(int $codigoUnidad): array
    {
        $unidad = Unidad::findOne($codigoUnidad);
        if (!$unidad) {
            throw new BusinessException(Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'] ?? 'Registro no encontrado', 404);
        }
        if ($unidad->enUso()) {
            throw new BusinessException(Yii::$app->params['ERROR_REGISTRO_EN_USO'] ?? 'El registro está en uso', 409);
        }

        $unidad->CodigoEstado = Estado::ESTADO_ELIMINADO;
        if ($unidad->update() === false) {
            throw new DbException(Yii::$app->params['ERROR_EJECUCION_SQL'] ?? 'Error al eliminar');
        }

        return [
            'data' => $unidad->getAttributes(),
            'message' => 'Unidad eliminada correctamente'
        ];
    }

    public function buscar(int $codigoUnidad): array
    {
        $unidad = Unidad::findOne($codigoUnidad);
        if (!$unidad) {
            throw new BusinessException(Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'] ?? 'Registro no encontrado', 404);
        }

        return [
            'data' => $unidad->getAttributes(['CodigoUnidad','Da','Ue','Descripcion','Organizacional','FechaInicio','FechaFin','CodigoEstado']),
            'message' => 'Unidad encontrada'
        ];
    }

    private function validarRequeridos(array $params, array $requeridos): void
    {
        foreach ($requeridos as $campo) {
            if (!isset($params[$campo]) || $params[$campo] === '') {
                throw new ValidationException(
                    Yii::$app->params['ERROR_ENVIO_DATOS'] ?? 'Faltan datos requeridos',
                    [$campo => ['El campo es obligatorio']],
                    422
                );
            }
        }
    }

    private function validarYGuardar(Unidad $unidad): void
    {
        if (!$unidad->validate()) {
            throw new ValidationException(
                Yii::$app->params['ERROR_VALIDACION_MODELO'] ?? 'Errores de validación',
                $unidad->errors,
                422
            );
        }
        if (!$unidad->save()) {
            throw new DbException(Yii::$app->params['ERROR_EJECUCION_SQL'] ?? 'Error al guardar');
        }
    }
}
