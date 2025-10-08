<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\dao\ActividadDao;
use app\modules\Planificacion\formModels\ActividadForm;
use app\modules\Planificacion\models\Actividad;
use common\models\Estado;
use Throwable;
use Yii;
use yii\db\Exception;
use yii\db\Expression;
use yii\db\StaleObjectException;

class ActividadService
{
    public function listarActividades(): array
    {
        $data = Actividad::find()
            ->select([
                'CodigoActividad',
                'Programa',
                'Codigo',
                'Descripcion',
                'CodigoEstado',
                'CodigoUsuario',
            ])
            ->where(['!=', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->orderBy(['CodigoActividad' => SORT_ASC])
            ->asArray()
            ->all();

        return ResponseHelper::success($data, 'Listado de Actividades obtenido.');
    }

    public function listarActividad(int $codigoActividad): ?Actividad
    {
        return Actividad::find()
            ->where(['CodigoActividad' => $codigoActividad])
            ->andWhere(['!=', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->one();
    }

    public function guardarActividad(ActividadForm $form): array
    {
        // Duplicado por Codigo (vigente)
        $existe = Actividad::find()
            ->where(['Codigo' => trim($form->codigo)])
            ->andWhere(['CodigoEstado' => Estado::ESTADO_VIGENTE])
            ->exists();
        if ($existe) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_EXISTE'] ?? 'errorExiste',
                'Ya existe una actividad con ese código',
                400
            );
        }

        $codigoUsuario = Yii::$app->user->identity->CodigoUsuario ?? null;
        if (!$codigoUsuario) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'] ?? 'errorEnvio',
                'Usuario no autenticado o CódigoUsuario no disponible',
                401
            );
        }

        $nuevoCodigo = ActividadDao::GenerarCodigoActividad();
        $actividad = new Actividad([
            'CodigoActividad' => (int) $nuevoCodigo,
            'Programa'        => (int) $form->programa_id,
            'Codigo'          => trim($form->codigo),
            'Descripcion'     => mb_strtoupper(trim($form->descripcion), 'UTF-8'),
            'CodigoEstado'    => Estado::ESTADO_VIGENTE,
            'FechaHoraRegistro' => new Expression('GETDATE()'),
            'CodigoUsuario'   => $codigoUsuario,
        ]);

        return $this->validarProcesarModelo($actividad);
    }

    public function actualizarActividad(int $codigo, ActividadForm $form): array
    {
        $actividad = $this->obtenerModeloValidado($codigo);

        $actividad->Programa    = (int) $form->programa_id;
        $actividad->Codigo      = trim($form->codigo);
        $actividad->Descripcion = mb_strtoupper(trim($form->descripcion), 'UTF-8');

        if ($actividad->exist()) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_EXISTE'],
                'Ya existe una actividad con ese código',
                400
            );
        }

        return $this->validarProcesarModelo($actividad);
    }

    public function cambiarEstado(int $codigo): array
    {
        $actividad = $this->obtenerModeloValidado($codigo);
        $actividad->CodigoEstado = $actividad->CodigoEstado == Estado::ESTADO_VIGENTE
            ? Estado::ESTADO_CADUCO
            : Estado::ESTADO_VIGENTE;

        if (!$actividad->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'], $actividad->getErrors(), 500);
        }

        if (!$actividad->save(false)) {
            Yii::error("Error al guardar el cambio de estado de la Actividad $actividad->CodigoActividad", __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'], $actividad->getErrors(), 500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $actividad->CodigoEstado,
        ];
    }

    public function eliminarActividad(int $codigo): array
    {
        $actividad = $this->obtenerModeloValidado($codigo);

        if ($actividad->isUsed()) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_EN_USO'],
                'La Actividad se encuentra en uso y no puede ser eliminada',
                500
            );
        }

        $actividad->CodigoEstado = Estado::ESTADO_ELIMINADO;

        if (!$actividad->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'], $actividad->getErrors(), 500);
        }

        if (!$actividad->save(false)) {
            Yii::error("Error al guardar el cambio de estado de la Actividad $actividad->CodigoActividad", __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'], $actividad->getErrors(), 500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => '',
        ];
    }

    public function obtenerModelo(int $codigo): array
    {
        $actividad = $this->listarActividad($codigo);
        if (!$actividad) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],
                'Registro no encontrado',
                404
            );
        }
        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $actividad->getAttributes([
                'CodigoActividad',
                'Programa',
                'Codigo',
                'Descripcion',
            ]),
        ];
    }

    private function obtenerModeloValidado(int $codigo): ?Actividad
    {
        $model = $this->listarActividad($codigo);
        if (!$model) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],
                'No se encontró el registro buscado',
                404
            );
        }
        return $model;
    }

    public function validarProcesarModelo(Actividad $actividad): array
    {
        if (!$actividad->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'], $actividad->getErrors(), 500);
        }

        if (!$actividad->save(false)) {
            Yii::error("Error al guardar la Actividad $actividad->CodigoActividad", __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'], $actividad->getErrors(), 500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => '',
        ];
    }
}
