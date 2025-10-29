<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\formModels\PoliticaEstrategicaForm;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\dao\PoliticaEstrategicaDao;
use app\modules\Planificacion\models\PoliticaEstrategica;
use yii\db\StaleObjectException;
use common\models\Estado;
use yii\db\Exception;
use Throwable;
use Yii;

class PoliticaEstrategicaService
{
    /**
     * lista un array de Politicas Estrategicas no eliminados
     *
     * @return array of Areas
     */
    public function listarTodo(): array
    {
        $data = PoliticaEstrategica::listAll()
            ->orderBy(['P.Codigo' => SORT_ASC])
            ->asArray()->all();

        return ResponseHelper::success($data, 'Listado de Politicas Estratégicas obtenido.');
    }

    /**
     * lista un array de Areas Estrategicas no eliminados
     * @param string $idAreaEstrategica
     * @param string $search
     *
     * @return array of Politicas
     */
    public function listarPoliticasS2(string $idAreaEstrategica, string $search): array
    {
        $data = PoliticaEstrategica::listAllByArea($idAreaEstrategica,$search)
            ->orderBy(['Codigo' => SORT_ASC])
            ->asArray()->all();

        return ResponseHelper::success($data, 'Listado de Áreas Estratégicas obtenido.');
    }

    /**
     * obtiene una Politica Estrategico en base a un codigo.
     *
     * @param string $id
     * @return PoliticaEstrategica|null
     */
    public function listarUno(string $id): ?PoliticaEstrategica
    {
        return PoliticaEstrategica::listOne($id);
    }

    /**
     * Guarda un nuevo REGISTRO.
     *
     * @param PoliticaEstrategicaForm $form
     * @return array ['message' => string, 'data' => string]
     * @throws ValidationException|Exception
     */
    public function guardar(PoliticaEstrategicaForm $form): array
    {
        $modelo = new PoliticaEstrategica([
            'IdAreaEstrategica' => $form->idAreaEstrategica,
            'Codigo' => $form->codigo,
            'Descripcion' => mb_strtoupper(trim($form->descripcion), 'UTF-8'),
            'CodigoEstado'    => Estado::ESTADO_VIGENTE,
            'CodigoUsuario'   => Yii::$app->user->identity->CodigoUsuario ?? null,
        ]);

        return $this->validarProcesarModelo($modelo);
    }

    /**
     * Actualiza la informacion de un registro en el modelo
     *
     * @param string $id
     * @param PoliticaEstrategicaForm $form
     * @return array
     * @throws Throwable
     * @throws ValidationException
     * @throws StaleObjectException
     */
    public function actualizar(string $id, PoliticaEstrategicaForm $form): array
    {
        $modelo = $this->obtenerModeloValidado($id);

        $modelo->IdAreaEstrategica = $form->idAreaEstrategica;
        $modelo->Codigo = $form->codigo;
        $modelo->Descripcion = mb_strtoupper(trim($form->descripcion), 'UTF-8');

        return $this->validarProcesarModelo($modelo);
    }

    /**
     * Busca una Politica Estrategica por su código y alterna su estado.
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
            Yii::error("Error al guardar el cambio de estado de la Politica Estrategica $modelo->Descripcion", __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'],$modelo->getErrors(),500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $modelo->CodigoEstado,
        ];
    }

    /**
     * Busca una Politica Estrategica por su código y realiza un soft delete.
     *
     * @param string $id
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     */
    public function eliminar(string $id): array
    {
        $modelo = $this->obtenerModeloValidado($id);

        if (PoliticaEstrategicaDao::enUso($modelo)){
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_EN_USO'],'La Politica estrategica se encuentra asignada a un objetivo estrategico',500);
        }

        $modelo->eliminar();
        return $this->validarProcesarModelo($modelo);
    }

    /**
     * Obtiene el modelo segun el codigo enviado.
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
            'data' => $modelo->getAttributes(array('IdPoliticaEstrategica', 'IdAreaEstrategica', 'Codigo', 'Descripcion')),
        ];
    }

    /**
     * Obtiene el modelo segun el codigo enviado y valida si existe.
     *
     * @param string $id
     * @return PoliticaEstrategica|null
     * @throws ValidationException
     */
    private function obtenerModeloValidado(string $id): ?PoliticaEstrategica
    {
        $modelo = $this->listarUno($id);
        if (!$modelo) {
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],'No se encontro el registro buscado',404);
        }
        return $modelo;
    }

    /**
     *  Recibe un modelo lo valida y realiza el guardado del mismo.
     *
     * @param PoliticaEstrategica $modelo
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     */
    public function validarProcesarModelo(PoliticaEstrategica $modelo): array
    {
        if (!$modelo->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'],$modelo->getErrors(),500);
        }

        if (!$modelo->save(false)) {
            Yii::error("Error al guardar los datos del area estrategica $modelo->Descripcion", __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'],$modelo->getErrors(),500);
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
     * @param string $idAreaEstrategica
     * @param int $codigo
     * @return bool
     */
    public function verificarCodigo(string $id, string $idAreaEstrategica, int $codigo): bool
    {
        return PoliticaEstrategicaDao::verificarCodigo($id, $idAreaEstrategica, $codigo);
    }

    /**
     *  Recibe un id y verifica si existe.
     *
     * @param string $id
     * @param string $idAreaEstrategica
     * @return bool
     */
    public function validarId(string $id, string $idAreaEstrategica): bool
    {
        return PoliticaEstrategicaDao::validarId($id, $idAreaEstrategica);
    }
}
