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
    public function listarPoliticas(): array
    {
        $data = PoliticaEstrategica::listAll()
            ->orderBy(['P.Codigo' => SORT_ASC])
            ->asArray()->all();

        return ResponseHelper::success($data, 'Listado de Politicas Estratégicas obtenido.');
    }

    /**
     * obtiene una Politica Estrategico en base a un codigo.
     *
     * @param int $codigo
     * @return PoliticaEstrategica|null
     */
    public function listarPolitica(int $codigo): ?PoliticaEstrategica
    {
        return PoliticaEstrategica::listOne($codigo);
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
        $model = new PoliticaEstrategica([
            'CodigoAreaEstrategica' => $form->codigoAreaEstrategica,
            'Codigo' => $form->codigo,
            'Descripcion' => mb_strtoupper(trim($form->descripcion), 'UTF-8'),
            'CodigoEstado'    => Estado::ESTADO_VIGENTE,
            'CodigoUsuario'   => Yii::$app->user->identity->CodigoUsuario ?? null,
        ]);

        return $this->validarProcesarModelo($model);
    }

    /**
     * Actualiza la informacion de un registro en el modelo
     *
     * @param int $codigo
     * @param PoliticaEstrategicaForm $form
     * @return array
     * @throws Throwable
     * @throws ValidationException
     * @throws StaleObjectException
     */
    public function actualizar(int $codigo, PoliticaEstrategicaForm $form): array
    {
        $model = $this->obtenerModeloValidado($codigo);

        $model->CodigoAreaEstrategica = $form->codigoAreaEstrategica;
        $model->Codigo = $form->codigo;
        $model->Descripcion = mb_strtoupper(trim($form->descripcion), 'UTF-8');

        return $this->validarProcesarModelo($model);
    }

    /**
     * Busca una Politica Estrategica por su código y alterna su estado.
     *
     * @param int $codigo
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     */
    public function cambiarEstado(int $codigo): array
    {
        $modelo = $this->obtenerModeloValidado($codigo);

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
     * @param int $codigo
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     */
    public function eliminar(int $codigo): array
    {
        $model = $this->obtenerModeloValidado($codigo);

        if (PoliticaEstrategicaDao::enUso($model)){
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_EN_USO'],'La Politica estrategica se encuentra asignada a un objetivo estrategico',500);
        }

        $model->eliminar();
        return $this->validarProcesarModelo($model);
    }

    /**
     * Obtiene el modelo segun el codigo enviado.
     *
     * @param int $codigo
     * @return array
     * @throws ValidationException
     */
    public function obtenerModelo(int $codigo): array
    {
        $model = $this->listarPolitica($codigo);

        if (!$model) {
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],'Registro no encontrado',404);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $model->getAttributes(array('CodigoPoliticaEstrategica', 'CodigoAreaEstrategica', 'Codigo', 'Descripcion')),
        ];
    }

    /**
     * Obtiene el modelo segun el codigo enviado y valida si existe.
     *
     * @param int $codigo
     * @return PoliticaEstrategica|null
     * @throws ValidationException
     */
    private function obtenerModeloValidado(int $codigo): ?PoliticaEstrategica
    {
        $model = $this->listarPolitica($codigo);
        if (!$model) {
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],'No se encontro el registro buscado',404);
        }
        return $model;
    }

    /**
     *  Recibe un modelo lo valida y realiza el guardado del mismo.
     *
     * @param PoliticaEstrategica $model
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     */
    public function validarProcesarModelo(PoliticaEstrategica $model): array
    {
        if (!$model->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'],$model->getErrors(),500);
        }

        if (!$model->save(false)) {
            Yii::error("Error al guardar los datos del area estrategica $model->Descripcion", __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'],$model->getErrors(),500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => '',
        ];
    }
}
