<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\dao\UnidadEjecutoraDao;
use app\modules\Planificacion\formModels\UnidadEjecutoraForm;
use app\modules\Planificacion\models\UnidadEjecutora;
use common\models\Estado;
use Yii;
use yii\db\Exception;

class UnidadEjecutoraService
{
    /**
     * Lista un array de Unidades Ejecutoras no eliminados
     *
     * @return array of Unidades Ejecutoras
     */
    public function listarTodo(string $search = ""): array
    {
        $data = UnidadEjecutora::listAll($search)
            ->asArray()->all();

        return ResponseHelper::success($data, 'Listado de Unidades Ejecutoras obtenido.');
    }

    /**
     * Obtiene un ue con base en un código.
     *
     * @param string $id
     * @return UnidadEjecutora|null
     */
    public function listarUno(string $id): ?UnidadEjecutora
    {
        return UnidadEjecutora::listOne($id);
    }


    /**
     * Guarda un nuevo Ue.
     *
     * @param UnidadEjecutoraForm $form
     * @return array ['message' => string, 'data' => string]
     * @throws Exception|ValidationException
     */
    public function guardar(UnidadEjecutoraForm $form): array
    {
        $modelo = new UnidadEjecutora([
            'IdDa' => trim($form->idDa),
            'Ue' => trim($form->ue),
            'Descripcion' => mb_strtoupper(trim($form->descripcion), 'UTF-8'),
            'CodigoEstado' => Estado::ESTADO_VIGENTE,
            'CodigoUsuario' => Yii::$app->user->identity->CodigoUsuario ?? null,
        ]);

        return $this->validarProcesarModelo($modelo);
    }

    /**
     * Actualiza la información de un registro en el modelo
     *
     * @param string $id
     * @param UnidadEjecutoraForm $form
     * @return array
     * @throws Exception
     * @throws ValidationException
     */
    public function actualizar(string $id, UnidadEjecutoraForm $form): array
    {
        $modelo = $this->obtenerModeloValidado($id);

        $modelo->IdDa = trim($form->idDa);
        $modelo->Ue = trim($form->ue);
        $modelo->Descripcion = mb_strtoupper(trim($form->descripcion), 'UTF-8');

        return $this->validarProcesarModelo($modelo);
    }

    /**
     * Busca un registro por su código y alterna su estado.
     *
     * @param string $id
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     */
    public function cambiarEstado(string $id): array
    {
        $modelo = $this->obtenerModeloValidado($id);

        $modelo->cambiarEstado();

        if (!$modelo->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'], $modelo->getErrors(), 500);
        }

        if (!$modelo->save(false)) {
            Yii::error("Error al guardar el cambio de estado del Ue $modelo->Ue", __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'], $modelo->getErrors(), 500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $modelo->CodigoEstado,
        ];
    }

    /**
     * Busca un registro por su código y realiza un soft delete.
     *
     * @param string $id
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     */
    public function eliminar(string $id): array
    {
        $modelo = $this->obtenerModeloValidado($id);

        if (UnidadEjecutoraDao::enUso($modelo)) {
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_EN_USO'], 'La unidad ejecutora se encuentra en uso', 500);
        }

        $modelo->eliminar();
        return $this->validarProcesarModelo($modelo);
    }

    /**
     * Obtiene el modelo según el código enviado.
     *
     * @param string $id
     * @return array
     * @throws ValidationException
     */
    public function obtenerModelo(string $id): array
    {
        $modelo = $this->listarUno($id);

        if (!$modelo) {
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'], 'Registro no encontrado', 404);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $modelo->getAttributes(array('IdUe', 'IdDa', 'Ue', 'Descripcion')),
        ];
    }

    /**
     * Obtiene el modelo según el código enviado y válida si existe.
     *
     * @param string $id
     * @return UnidadEjecutora|null
     * @throws ValidationException
     */
    private function obtenerModeloValidado(string $id): ?UnidadEjecutora
    {
        $modelo = $this->listarUno($id);
        if (!$modelo) {
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'], 'No se encontro el registro buscado', 404);
        }
        return $modelo;
    }

    /**
     * Recibe un modelo lo valida y realiza el guardado del mismo.
     *
     * @param UnidadEjecutora $modelo
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     */
    public function validarProcesarModelo(UnidadEjecutora $modelo): array
    {
        if (!$modelo->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'], $modelo->getErrors(), 500);
        }

        if (!$modelo->save(false)) {
            Yii::error("Error al guardar el Ue $modelo->Ue", __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'], $modelo->getErrors(), 500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => '',
        ];
    }

    /**
     *  Recibe un codigo y verifica si está en uso.
     *
     * @param string $id
     * @param string $IdDa
     * @param string $codigo
     * @return bool
     */
    public function verificarCodigo(string $id, string $IdDa, string $codigo): bool
    {
        return UnidadEjecutoraDao::verificarCodigo($id, $IdDa, $codigo);
    }

    /**
     *  Recibe un id y verifica si existe.
     *
     * @param string $id
     * @param string $IdDa
     * @return bool
     */
    public function validarId(string $id, string $IdDa): bool
    {
        return UnidadEjecutoraDao::validarId($id, $IdDa);
    }
}