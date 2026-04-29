<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\formModels\LlavePresupuestariaForm;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\dao\LlavePresupuestariaDao;
use app\modules\Planificacion\models\LlavePresupuestaria;
use yii\db\StaleObjectException;
use common\models\Estado;
use yii\db\Exception;
use Throwable;
use Yii;

class LlavePresupuestariaService
{
    /**
     * lista un array de Objetivos Estrategicos no eliminados
     *
     * @return array of Objetivos
     */
    public function listarTodo(): array
    {
        $data = LlavePresupuestaria::listAll()
            ->asArray()->all();

        return ResponseHelper::success($data,'Listado de Llaves presupuestarias obtenido.');
    }

    /**
     * obtiene un Objetivo Estrategico en base a un codigo.
     *
     * @param string $id
     * @return LlavePresupuestaria|null
     */
    public  function listarUno(string $id): ?LlavePresupuestaria
    {
        return LlavePresupuestaria::listOne($id);
    }

    /**
     * Guarda un nuevo REGISTRO.
     *
     * @param LlavePresupuestariaForm $form
     * @return array ['message' => string, 'data' => string]
     * @throws Exception|ValidationException
     */
    public function guardar(LlavePresupuestariaForm $form): array
    {
        $modelo = new LlavePresupuestaria([
            'IdUnidad' => $form->idUnidad,
            'IdPrograma' => $form->idPrograma,
            'IdProyecto' => $form->idProyecto,
            'IdActividad' => $form->idActividad,
            'Descripcion'  => mb_strtoupper(trim($form->descripcion), 'UTF-8'),
            'TechoPresupuestario'  => $form->techoPresupuestario,
            'FechaInicio'  => $form->fechaInicio,
            'CodigoEstado'    => Estado::ESTADO_VIGENTE,
            'CodigoUsuario'   => Yii::$app->user->identity->CodigoUsuario ?? null,
        ]);

        return $this->validarProcesarModelo($modelo);
    }

    /**
     * Actualiza la informacion de un registro en el modelo
     *
     * @param string $id
     * @param LlavePresupuestariaForm $form
     * @return array
     * @throws Exception
     * @throws Throwable
     * @throws ValidationException
     * @throws StaleObjectException
     */
    public function actualizar(string $id, LlavePresupuestariaForm $form): array
    {
        $modelo = $this->obtenerModeloValidado($id);

        $modelo->IdUnidad = $form->idUnidad;
        $modelo->IdPrograma = $form->idPrograma;
        $modelo->IdProyecto = $form->idProyecto;
        $modelo->IdActividad = $form->idActividad;
        $modelo->Descripcion  = mb_strtoupper(trim($form->descripcion), 'UTF-8');
        $modelo->TechoPresupuestario = $form->techoPresupuestario;
        $modelo->FechaInicio = $form->fechaInicio;

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
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'],$modelo->getErrors(),500);
        }

        if (!$modelo->save(false)) {
            Yii::error("Error al guardar el cambio de estado de la llave presupuestaria $modelo->Descripcion", __METHOD__);
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
     * @param string $id
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     */
    public function eliminar(string $id): array
    {
        $modelo = $this->obtenerModeloValidado($id);

        if (LlavePresupuestariaDao::enUso($modelo)){
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_EN_USO'],'El Objetivo se encuentra asignado a un objetivo institucional',500);
        }

        $modelo->eliminar();
        return $this->validarProcesarModelo($modelo);
    }

    /**
     * @throws Exception
     * @throws ValidationException
     */
    public function finalizar(string $id): array
    {
        $modelo = $this->obtenerModeloValidado($id);

        $modelo->finalizar();

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
            'data' => $modelo->getAttributes(array('IdUnidad', 'IdPrograma', 'IdProyecto', 'IdActividad', 'Descripcion', 'TechoPresupuestario', 'FechaInicio')),
        ];
    }


    /**
     * Obtiene el modelo segun el codigo enviado y valida si existe.
     *
     * @param string $id
     * @return LlavePresupuestaria|null
     * @throws ValidationException
     */
    private function obtenerModeloValidado(string $id): ?LlavePresupuestaria
    {
        $modelo = $this->listarUno($id);
        if (!$modelo) {
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],'No se encontro el registro buscado',404);
        }
        return $modelo;
    }

    /**
     * @throws Exception
     * @throws ValidationException
     */
    private function validarProcesarModelo(LlavePresupuestaria $llave): array
    {
        if (!$llave->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'], $llave->getErrors(), 500);
        }

        if (!$llave->save(false)) {
            Yii::error('Error al guardar la Llave Presupuestaria', __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'], $llave->getErrors(), 500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => '',
        ];
    }
}
