<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\dao\ProyectoDao;
use app\modules\Planificacion\formModels\ProyectoForm;
use app\modules\Planificacion\models\Proyecto;
use common\models\Estado;
use Throwable;
use Yii;
use yii\db\Exception;
use yii\db\Expression;
use yii\db\StaleObjectException;

class ProyectoService
{
    /**
     * Lista un array de Proyectos no eliminados
     *
     * @return array
     */
    public function listarProyectos(): array
    {
        $data = Proyecto::find()
            ->select([
                'CodigoProyecto',
                'Programa',
                'Codigo',
                'Descripcion',
                'CodigoEstado',
                'CodigoUsuario',
            ])
            ->where(['!=', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->orderBy(['CodigoProyecto' => SORT_ASC])
            ->asArray()
            ->all();

        return ResponseHelper::success($data, 'Listado de Proyectos obtenido.');
    }

    /**
     * Obtiene un proyecto en base a un código (excluye eliminados).
     */
    public function listarProyecto(int $codigoProyecto): ?Proyecto
    {
        return Proyecto::find()
            ->where(['CodigoProyecto' => $codigoProyecto])
            ->andWhere(['!=', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->one();
    }

    public function guardarProyecto(ProyectoForm $form): array
    {
        // Validar duplicado por Código (vigente)
        $existe = Proyecto::find()
            ->where(['Codigo' => trim($form->codigo)])
            ->andWhere(['CodigoEstado' => Estado::ESTADO_VIGENTE])
            ->exists();

        if ($existe) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_EXISTE'] ?? 'errorExiste',
                'Ya existe un proyecto con ese código',
                400
            );
        }

        // Usuario obligatorio por constraint
        $codigoUsuario = Yii::$app->user->identity->CodigoUsuario ?? null;
        if (!$codigoUsuario) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'] ?? 'errorEnvio',
                'Usuario no autenticado o CódigoUsuario no disponible',
                401
            );
        }

        // Generar PK (la columna no es IDENTITY en SQL Server)
        $nuevoCodigoProyecto = ProyectoDao::GenerarCodigoProyecto();

        $proyecto = new Proyecto([
            'CodigoProyecto' => (int) $nuevoCodigoProyecto,
            'Programa' => (int) $form->programa_id, // Cambiado de $form->programa
            'Codigo' => trim($form->codigo),
            'Descripcion' => mb_strtoupper(trim($form->descripcion), 'UTF-8'),
            'CodigoEstado' => Estado::ESTADO_VIGENTE,
            'FechaHoraRegistro' => new Expression('GETDATE()'), // Cambiado a GETDATE()
            'CodigoUsuario' => $codigoUsuario,
        ]);

        return $this->validarProcesarModelo($proyecto);
    }

    /**
     * Actualiza la información de un Proyecto.
     *
     * @throws Exception
     * @throws Throwable
     * @throws ValidationException
     * @throws StaleObjectException
     */
    public function actualizarProyecto(int $codigo, ProyectoForm $form): array
    {
        $proyecto = $this->obtenerModeloValidado($codigo);

        // Asignar nuevos valores del formulario
        $proyecto->Programa    = (int) $form->programa_id;
        $proyecto->Codigo      = trim($form->codigo);
        $proyecto->Descripcion = mb_strtoupper(trim($form->descripcion), 'UTF-8');

        // Verificar si existe otro proyecto con el mismo código (vigente) usando el nuevo código
        if ($proyecto->exist()) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_EXISTE'],
                'Ya existe un proyecto con ese código',
                400
            );
        }

        return $this->validarProcesarModelo($proyecto);
    }

    /**
     * Busca un Proyecto por su código y alterna su estado V/C.
     *
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     */
    public function cambiarEstado(int $codigo): array
    {
        $proyecto = $this->obtenerModeloValidado($codigo);

        // Alterna estado sin depender de método en el modelo
        $proyecto->CodigoEstado = $proyecto->CodigoEstado == Estado::ESTADO_VIGENTE
            ? Estado::ESTADO_CADUCO
            : Estado::ESTADO_VIGENTE;

        if (!$proyecto->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'], $proyecto->getErrors(), 500);
        }

        if (!$proyecto->save(false)) {
            Yii::error("Error al guardar el cambio de estado del Proyecto $proyecto->CodigoProyecto", __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'], $proyecto->getErrors(), 500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $proyecto->CodigoEstado,
        ];
    }

    /**
     * Busca un Proyecto por su código y realiza un soft delete.
     *
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     */
    public function eliminarProyecto(int $codigo): array
    {
        $proyecto = $this->obtenerModeloValidado($codigo);

        // Verificar si el proyecto está en uso (modelo define isUsed())
        if ($proyecto->isUsed()) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_EN_USO'],
                'El Proyecto se encuentra en uso y no puede ser eliminado',
                500
            );
        }

        // Soft delete
        $proyecto->CodigoEstado = Estado::ESTADO_ELIMINADO;

        if (!$proyecto->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'], $proyecto->getErrors(), 500);
        }

        if (!$proyecto->save(false)) {
            Yii::error("Error al guardar el cambio de estado del Proyecto $proyecto->CodigoProyecto", __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'], $proyecto->getErrors(), 500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => '',
        ];
    }

    /**
     * Obtiene el modelo según el código enviado.
     *
     * @throws ValidationException
     */
    public function obtenerModelo(int $codigo): array
    {
        $proyecto = $this->listarProyecto($codigo);

        if (!$proyecto) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],
                'Registro no encontrado',
                404
            );
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $proyecto->getAttributes([
                'CodigoProyecto',
                'Programa',
                'Codigo',
                'Descripcion',
            ]),
        ];
    }

    /**
     * Obtiene el modelo según el código enviado y valida si existe.
     *
     * @throws ValidationException
     */
    private function obtenerModeloValidado(int $codigo): ?Proyecto
    {
        $model = $this->listarProyecto($codigo);
        if (!$model) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],
                'No se encontró el registro buscado',
                404
            );
        }
        return $model;
    }

    /**
     * Recibe un modelo lo valida y realiza el guardado del mismo.
     *
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     */
    public function validarProcesarModelo(Proyecto $proyecto): array
    {
        if (!$proyecto->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'], $proyecto->getErrors(), 500);
        }

        if (!$proyecto->save(false)) {
            Yii::error("Error al guardar el Proyecto $proyecto->CodigoProyecto", __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'], $proyecto->getErrors(), 500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => '',
        ];
    }
}
