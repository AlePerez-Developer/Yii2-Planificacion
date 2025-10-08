<?php
namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\dao\ProgramaDao;
use app\modules\Planificacion\formModels\ProgramaForm;
use app\modules\Planificacion\models\Programa;
use yii\db\StaleObjectException;
use common\models\Estado;
use yii\db\Exception;
use Throwable;
use Yii;

class ProgramaService
{
    /**
     * Lista un array de Programas no eliminados
     *
     * @return array of programas
     */
    public function listarProgramas(): array
    {
        $data = Programa::listAll()
            ->asArray()
            ->all();
        return ResponseHelper::success($data, 'Listado de Programas obtenido.');
    }

    /**
     * Obtiene un programa en base a un código.
     *
     * @param int $codigoPrograma
     * @return Programa|null
     */
    public function listarPrograma(int $codigoPrograma): ?Programa
    {
        return Programa::listOne($codigoPrograma);
    }

    /**
     * Guarda un nuevo Programa.
     *
     * @param ProgramaForm $form
     * @return array ['message' => string, 'data' => string]
     * @throws Exception|ValidationException
     */
    public function guardarPrograma(ProgramaForm $form): array
    {
        $programa = new Programa([
            'CodigoPrograma' => ProgramaDao::generarCodigoPrograma(),
            'Codigo'          => trim($form->codigo),
            'Descripcion'     => mb_strtoupper(trim($form->descripcion), 'UTF-8'),
            'CodigoEstado'    => Estado::ESTADO_VIGENTE,
            'CodigoUsuario'   => Yii::$app->user->identity->CodigoUsuario ?? null,
        ]);

        return $this->validarProcesarModelo($programa);
    }

    /**
     * Actualiza la información de un registro en el modelo
     *
     * @param int $codigo
     * @param ProgramaForm $form
     * @return array
     * @throws Exception
     * @throws Throwable
     * @throws ValidationException
     * @throws StaleObjectException
     */
    public function actualizarPrograma(int $codigo, ProgramaForm $form): array
    {
        $programa = $this->obtenerModeloValidado($codigo);

        // Asignar nuevos valores antes de verificar duplicados
        $programa->Codigo = trim($form->codigo);
        $programa->Descripcion = mb_strtoupper(trim($form->descripcion), 'UTF-8');

        // Verificar si existe otro programa con el mismo código (excluyéndose a sí mismo)
        if ($programa->exist()) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_EXISTE'],
                'Ya existe un programa con ese código',
                400
            );
        }

        return $this->validarProcesarModelo($programa);
    }

    /**
     * Busca un Programa por su código y alterna su estado.
     *
     * @param int $codigo
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     */
    public function cambiarEstado(int $codigo): array
    {
        $programa = $this->obtenerModeloValidado($codigo);

        $programa->cambiarEstado();

        if (!$programa->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'], $programa->getErrors(), 500);
        }

        if (!$programa->save(false)) {
            Yii::error("Error al guardar el cambio de estado del Programa $programa->CodigoPrograma", __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'], $programa->getErrors(), 500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $programa->CodigoEstado,
        ];
    }

    /**
     * Busca un Programa por su código y realiza un soft delete.
     *
     * @param int $codigo
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     */
    public function eliminarPrograma(int $codigo): array
    {
        $programa = $this->obtenerModeloValidado($codigo);

        if ($programa->enUso()) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_EN_USO'],
                'El Programa se encuentra en uso y no puede ser eliminado',
                500
            );
        }

        $programa->eliminarPrograma();

        if (!$programa->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'], $programa->getErrors(), 500);
        }

        if (!$programa->save(false)) {
            Yii::error("Error al guardar el cambio de estado del Programa $programa->CodigoPrograma", __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'], $programa->getErrors(), 500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => '',
        ];
    }

    /**
     * Obtiene el modelo según el código enviado.
     *
     * @param int $codigo
     * @return array
     * @throws ValidationException
     */
    public function obtenerModelo(int $codigo): array
    {
        $programa = $this->listarPrograma($codigo);

        if (!$programa) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],
                'Registro no encontrado',
                404
            );
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $programa->getAttributes([
                'CodigoPrograma',
                'Codigo',
                'Descripcion'
            ]),
        ];
    }

    /**
     * Obtiene el modelo según el código enviado y valida si existe.
     *
     * @param int $codigo
     * @return Programa|null
     * @throws ValidationException
     */
    private function obtenerModeloValidado(int $codigo): ?Programa
    {
        $model = $this->listarPrograma($codigo);
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
     * @param Programa $programa
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     */
    public function validarProcesarModelo(Programa $programa): array
    {
        if (!$programa->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'], $programa->getErrors(), 500);
        }

        if (!$programa->save(false)) {
            Yii::error("Error al guardar el Programa $programa->CodigoPrograma", __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'], $programa->getErrors(), 500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => '',
        ];
    }
}