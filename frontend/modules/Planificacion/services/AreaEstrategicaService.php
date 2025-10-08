<?php
namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\formModels\AreaEstrategicaForm;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\dao\AreaEstrategicaDao;
use app\modules\Planificacion\models\AreaEstrategica;
use common\models\Estado;
use yii\db\StaleObjectException;
use yii\db\Exception;
use Throwable;
use Yii;

class AreaEstrategicaService
{
    /**
     * lista un array de Areas Estrategicas no eliminados
     *
     * @return array of Areas
     */
    public function listarAreas(): array
    {
        $data = AreaEstrategica::listAll()
            ->orderBy(['Codigo' => SORT_ASC])
            ->asArray()->all();

        return ResponseHelper::success($data, 'Listado de Áreas Estratégicas obtenido.');
    }

    /**
     * lista un array de Areas Estrategicas no eliminados
     *
     * @return array of Areas
     */
    public function listarAreasS2($search): array
    {
        $data = AreaEstrategica::listAll($search)
            ->orderBy(['Codigo' => SORT_ASC])
            ->asArray()->all();

        return ResponseHelper::success($data, 'Listado de Áreas Estratégicas obtenido.');
    }

    /**
     * obtiene un Objetivo Estrategico en base a un codigo.
     *
     * @param int $codigo
     * @return AreaEstrategica|null
     */
    public function listarArea(int $codigo): ?AreaEstrategica
    {
        return AreaEstrategica::listOne($codigo);
    }

    /**
     * Guarda un nuevo REGISTRO.
     *
     * @param AreaEstrategicaForm $form
     * @return array ['message' => string, 'data' => string]
     * @throws ValidationException|Exception
     */
    public function guardar(AreaEstrategicaForm $form): array
    {
        $model = new AreaEstrategica([
            'CodigoPei' => $form->codigoPei,
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
     * @param AreaEstrategicaForm $form
     * @return array
     * @throws Throwable
     * @throws ValidationException
     * @throws StaleObjectException
     */
    public function actualizar(int $codigo, AreaEstrategicaForm $form): array
    {
        $model = $this->obtenerModeloValidado($codigo);

        $model->CodigoPei = $form->codigoPei;
        $model->Codigo = $form->codigo;
        $model->Descripcion = mb_strtoupper(trim($form->descripcion), 'UTF-8');

        return $this->validarProcesarModelo($model);
    }

    /**
     * Busca un Objetivo por su código y alterna su estado.
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
            Yii::error("Error al guardar el cambio de estado del Area Estrategica $modelo->Descripcion", __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'],$modelo->getErrors(),500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $modelo->CodigoEstado,
        ];
    }

    /**
     * Busca un Objetivo por su código y realiza un soft delete.
     *
     * @param int $codigo
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     */
    public function eliminar(int $codigo): array
    {
        $model = $this->obtenerModeloValidado($codigo);

        if (AreaEstrategicaDao::enUso($model)){
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_EN_USO'],'El Area estrategica se encuentra asignada a una Politica estrategica',500);
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
        $model = $this->listarArea($codigo);

        if (!$model) {
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],'Registro no encontrado',404);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $model->getAttributes(array('CodigoAreaEstrategica', 'CodigoPei', 'Codigo', 'Descripcion')),
        ];
    }

    /**
     * Obtiene el modelo segun el codigo enviado y valida si existe.
     *
     * @param int $codigo
     * @return AreaEstrategica|null
     * @throws ValidationException
     */
    private function obtenerModeloValidado(int $codigo): ?AreaEstrategica
    {
        $model = $this->listarArea($codigo);
        if (!$model) {
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],'No se encontro el registro buscado',404);
        }
        return $model;
    }

    /**
     *  Recibe un modelo lo valida y realiza el guardado del mismo.
     *
     * @param AreaEstrategica $model
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     */
    public function validarProcesarModelo(AreaEstrategica $model): array
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
