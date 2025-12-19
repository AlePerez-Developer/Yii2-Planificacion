<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\formModels\ActividadForm;
use app\modules\Planificacion\dao\ActividadDao;
use app\modules\Planificacion\models\Actividad;
use yii\db\StaleObjectException;
use common\models\Estado;
use yii\db\Exception;
use Throwable;
use Yii;

class ActividadService
{
    public function listarTodo(): array
    {
        $data = Actividad::listAll()
            ->orderBy(['Codigo' => SORT_ASC])
            ->asArray()->all();

        return ResponseHelper::success($data, 'Listado de Actividades obtenido.');
    }

    public function listarUno(int $id): ?Actividad
    {
        return Actividad::listOne($id);
    }

    /**
     * Guarda un nuevo Programa.
     *
     * @param ActividadForm $form
     ** @return array ['message' => string, 'data' => string]
     * * @throws Exception|ValidationException
     */
    public function guardar(ActividadForm $form): array
    {
        $modelo = new Actividad([
            'IdPrograma' => $form->idPrograma,
            'Codigo' => trim($form->codigo),
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
     * @param ActividadForm $form
     * @return array
     * @throws Exception
     * @throws Throwable
     * @throws ValidationException
     * @throws StaleObjectException
     */
    public function actualizar(string $id, ActividadForm $form): array
    {
        $modelo = $this->obtenerModeloValidado($id);

        $modelo->IdPrograma = $form->idPrograma;
        $modelo->Codigo = trim($form->codigo);
        $modelo->Descripcion = mb_strtoupper(trim($form->descripcion), 'UTF-8');

        return $this->validarProcesarModelo($modelo);
    }

    /**
     * Busca una Actividad por su código y alterna su estado.
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
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'],$modelo->getErrors(),500);
        }

        if (!$modelo->save(false)) {
            Yii::error("Error al guardar el cambio de estado del Programa $modelo->Codigo", __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'],$modelo->getErrors(),500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $modelo->CodigoEstado,
        ];
    }

    /**
     * Busca una Actividad por su código y realiza un soft delete.
     *
     * @param string $id
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     */
    public function eliminar(string $id): array
    {
        $modelo = $this->obtenerModeloValidado($id);

        if (ActividadDao::enUso($modelo)){
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_EN_USO'],'La actividad se encuentra en uso',500);
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
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],'Registro no encontrado',404);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $modelo->getAttributes(array('IdActividad', 'IdPrograma', 'Codigo', 'Descripcion')),
        ];
    }

    /**
     * Obtiene el modelo según el código enviado y valida si existe.
     *
     * @param string $id
     * @return Actividad|null
     * @throws ValidationException
     */
    private function obtenerModeloValidado(string $id): ?Actividad
    {
        $modelo = $this->listarUno($id);
        if (!$modelo) {
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],'No se encontro el registro buscado',404);
        }
        return $modelo;
    }

    /**
     * Recibe un modelo lo valida y realiza el guardado del mismo.
     *
     * @param Actividad $modelo
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     */
    public function validarProcesarModelo(Actividad $modelo): array
    {
        if (!$modelo->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'], $modelo->getErrors(), 500);
        }

        if (!$modelo->save(false)) {
            Yii::error("Error al guardar el Programa $modelo->Codigo", __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'], $modelo->getErrors(), 500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => '',
        ];
    }

    /**
     *  Recibe un codigo y verifica si esta en uso.
     *
     * @param string $id
     * @param string $idPrograma
     * @param string $codigo
     * @return bool
     */
    public function verificarCodigo(string $id, string $idPrograma, string $codigo): bool
    {
        return ActividadDao::verificarCodigo($id, $idPrograma, $codigo);
    }

    /**
     *  Recibe un id y verifica si existe.
     *
     * @param string $id
     * @return bool
     */
    public function validarId(string $id): bool
    {
        return ActividadDao::validarId($id);
    }
}
