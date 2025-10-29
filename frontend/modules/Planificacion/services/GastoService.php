<?php
namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\dao\GastoDao;
use app\modules\Planificacion\formModels\GastoForm;
use app\modules\Planificacion\models\Gasto;
use yii\db\StaleObjectException;
use common\models\Estado;
use yii\db\Exception;
use Throwable;
use Yii;

class GastoService
{
    /**
     * Lista un array de Gastos no eliminados
     *
     * @return array of gastos
     */
    public function listarGastos(): array
    {
        $data = Gasto::listAll()
            ->asArray()
            ->all();
        return ResponseHelper::success($data, 'Listado de Gastos obtenido.');
    }

    /**
     * Obtiene un gasto en base a un código.
     *
     * @param int $codigoGasto
     * @return Gasto|null
     */
    public function listarGasto(int $codigoGasto): ?Gasto
    {
        return Gasto::listOne($codigoGasto);
    }

    /**
     * Guarda un nuevo Gasto.
     *
     * @param GastoForm $form
     * @return array ['message' => string, 'data' => string]
     * @throws Exception|ValidationException
     */
    public function guardarGasto(GastoForm $form): array
    {
        $gasto = new Gasto([
            //'CodigoGasto'         => GastoDao::generarCodigoGasto(),
            'Descripcion'         => mb_strtoupper(trim($form->descripcion), 'UTF-8'),
            'EntidadTransferencia' => trim($form->entidadTransferencia),
            'CodigoEstado'        => Estado::ESTADO_VIGENTE,
            'CodigoUsuario'       => Yii::$app->user->identity->CodigoUsuario ?? null,
        ]);

        return $this->validarProcesarModelo($gasto);
    }

    /**
     * Actualiza la información de un registro en el modelo
     *
     * @param int $codigo
     * @param GastoForm $form
     * @return array
     * @throws Exception
     * @throws Throwable
     * @throws ValidationException
     * @throws StaleObjectException
     */
    public function actualizarGasto(int $codigo, GastoForm $form): array
    {
        $gasto = $this->obtenerModeloValidado($codigo);

        // Asignar nuevos valores antes de verificar duplicados
        $gasto->Descripcion = mb_strtoupper(trim($form->descripcion), 'UTF-8');
        $gasto->EntidadTransferencia = trim($form->entidadTransferencia);

        // Verificar si existe otro gasto con la misma descripción (excluyéndose a sí mismo)
        if ($gasto->exist()) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_EXISTE'],
                'Ya existe un gasto con esa descripción',
                400
            );
        }

        return $this->validarProcesarModelo($gasto);
    }

    /**
     * Busca un Gasto por su código y alterna su estado.
     *
     * @param int $codigo
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     */
    public function cambiarEstado(int $codigo): array
    {
        $gasto = $this->obtenerModeloValidado($codigo);

        $gasto->cambiarEstado();

        if (!$gasto->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'], $gasto->getErrors(), 500);
        }

        if (!$gasto->save(false)) {
            Yii::error("Error al guardar el cambio de estado del Gasto $gasto->CodigoGasto", __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'], $gasto->getErrors(), 500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $gasto->CodigoEstado,
        ];
    }

    /**
     * Busca un Gasto por su código y realiza un soft delete.
     *
     * @param int $codigo
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     */
    public function eliminarGasto(int $codigo): array
    {
        $gasto = $this->obtenerModeloValidado($codigo);

        if ($gasto->enUso()) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_EN_USO'],
                'El Gasto se encuentra en uso y no puede ser eliminado',
                500
            );
        }

        $gasto->eliminarGasto();

        if (!$gasto->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'], $gasto->getErrors(), 500);
        }

        if (!$gasto->save(false)) {
            Yii::error("Error al guardar el cambio de estado del Gasto $gasto->CodigoGasto", __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'], $gasto->getErrors(), 500);
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
        $gasto = $this->listarGasto($codigo);

        if (!$gasto) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],
                'Registro no encontrado',
                404
            );
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $gasto->getAttributes([
                'CodigoGasto',
                'Descripcion',
                'EntidadTransferencia'
            ]),
        ];
    }

    /**
     * Obtiene el modelo según el código enviado y valida si existe.
     *
     * @param int $codigo
     * @return Gasto|null
     * @throws ValidationException
     */
    private function obtenerModeloValidado(int $codigo): ?Gasto
    {
        $model = $this->listarGasto($codigo);
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
     * @param Gasto $gasto
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     */
    public function validarProcesarModelo(Gasto $gasto): array
    {
        if (!$gasto->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'], $gasto->getErrors(), 500);
        }

        if (!$gasto->save(false)) {
            Yii::error("Error al guardar el Gasto $gasto->CodigoGasto", __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'], $gasto->getErrors(), 500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => '',
        ];
    }
}