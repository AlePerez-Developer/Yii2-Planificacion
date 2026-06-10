<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\formModels\AccionEstrategicaForm;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\dao\AccionEstrategicaDao;
use app\modules\Planificacion\models\AccionEstrategica;
use common\models\Estado;
use yii\db\Exception;
use Yii;

class AccionEstrategicaService
{
    /**
     * Lista un array de Acciones Estrategicas no eliminados
     *
     * @return array of Areas
     */
    public function listarTodo(): array
    {
        $data = AccionEstrategica::listAll()
            ->orderBy(['Descripcion' => SORT_ASC])
            ->asArray()->all();

        return ResponseHelper::success($data, 'Listado de Áreas Estratégicas obtenido.');
    }

    /**
     * Lista un array de Áreas Estrategicas no eliminados
     * @param string $search
     * @return array of Areas
     */
    public function listarAccionesS2(string $search): array
    {
        $data = AccionEstrategica::listAll($search)
            ->orderBy(['Descripcion' => SORT_ASC])
            ->asArray()->all();

        return ResponseHelper::success($data, 'Listado de Áreas Estratégicas obtenido.');
    }

    /**
     * Obtiene un Objetivo Estrategico con base en un codigo.
     *
     * @param string $id
     * @return AccionEstrategica|null
     */
    public function listarUno(string $id): ?AccionEstrategica
    {
        return AccionEstrategica::listOne($id);
    }

    /**
     * Guarda un nuevo REGISTRO.
     *
     * @param AccionEstrategicaForm $form
     * @return array ['message' => string, 'data' => string]
     * * @throws ValidationException|Exception
     */
    public function guardar(AccionEstrategicaForm $form): array
    {
        $model = new AccionEstrategica([
            'Descripcion' => mb_strtoupper(trim($form->descripcion), 'UTF-8'),
            'CodigoEstado' => Estado::ESTADO_VIGENTE,
            'CodigoUsuario' => Yii::$app->user->identity->CodigoUsuario ?? null,
        ]);

        return $this->validarProcesarModelo($model);
    }

    /**
     * Actualiza la informacion de un registro en el modelo
     *
     * @param string $id
     * @param AccionEstrategicaForm $form
     * @return array
     * @throws Exception
     * @throws ValidationException
     */
    public function actualizar(string $id, AccionEstrategicaForm $form): array
    {
        $modelo = $this->obtenerModeloValidado($id);

        $modelo->Descripcion = mb_strtoupper(trim($form->descripcion), 'UTF-8');
        $modelo->Descripcion = mb_strtoupper(trim($form->descripcion), 'UTF-8');

        return $this->validarProcesarModelo($modelo);
    }

    /**
     * Busca un Objetivo por su código y alterna su estado.
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
            Yii::error("Error al guardar el cambio de estado del Area Estrategica $modelo->Descripcion", __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'], $modelo->getErrors(), 500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $modelo->CodigoEstado,
        ];
    }

    /**
     * Busca un Objetivo por su código y realiza un soft delete.
     *
     * @param string $id
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     */
    public function eliminar(string $id): array
    {
        $modelo = $this->obtenerModeloValidado($id);

        if (AccionEstrategicaDao::enUso($modelo)) {
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_EN_USO'], 'El Area estrategica se encuentra asignada a una Politica estrategica', 500);
        }

        $modelo->eliminar();
        return $this->validarProcesarModelo($modelo);
    }

    /**
     * Obtiene el modelo según el codigo enviado.
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
            'data' => $modelo->getAttributes(array('IdAccionEstrategica', 'Descripcion')),
        ];
    }

    /**
     * Obtiene el modelo según el codigo enviado y válida si existe.
     *
     * @param string $id
     * @return AccionEstrategica|null
     * @throws ValidationException
     */
    private function obtenerModeloValidado(string $id): ?AccionEstrategica
    {
        $modelo = $this->listarUno($id);
        if (!$modelo) {
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'], 'No se encontro el registro buscado', 404);
        }
        return $modelo;
    }

    /**
     *  Recibe un modelo lo valida y realiza el guardado del mismo.
     *
     * @param AccionEstrategica $modelo
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     */
    public function validarProcesarModelo(AccionEstrategica $modelo): array
    {
        if (!$modelo->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'], $modelo->getErrors(), 500);
        }

        if (!$modelo->save(false)) {
            Yii::error("Error al guardar los datos del area estrategica $modelo->Descripcion", __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'], $modelo->getErrors(), 500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => '',
        ];
    }

    /**
     *  Recibe un id y verifica si existe.
     *
     * @param string $id
     * @return bool
     */
    public function validarId(string $id): bool
    {
        return AccionEstrategicaDao::validarId($id);
    }

}