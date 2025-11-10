<?php
namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\formModels\PeiForm;
use app\modules\Planificacion\dao\PeiDao;
use app\modules\Planificacion\models\Pei;
use yii\db\StaleObjectException;
use common\models\Estado;
use yii\db\Exception;
use Throwable;
use Yii;


class PeiService
{
    /**
     * lista un array de Peis no eliminados
     *
     * @return array of peis
     */
    public  function listarTodo(): array
    {
        $data = Pei::listAll()
            ->asArray()
            ->all();
        return ResponseHelper::success($data,'Listado de PEI obtenido.');
    }

    /**
     * obtiene un pei en base a un codigo.
     *
     * @param string $id
     * @return Pei|null
     */
    public  function listarUno(string $id): ?Pei
    {
        return Pei::listOne($id);
    }

    /**
     * Guarda un nuevo PEI.
     *
     * @param PeiForm $form
     * @return array ['message' => string, 'data' => string]
     * @throws Exception|ValidationException
     * @throws Throwable
     */
    public function guardar(PeiForm $form): array
    {
        $modelo = new Pei([
            'Descripcion'  => mb_strtoupper(trim($form->descripcion), 'UTF-8'),
            'FechaAprobacion' => date("d/m/Y", strtotime($form->fechaAprobacion)),
            'GestionInicio'   => $form->gestionInicio,
            'GestionFin'      => $form->gestionFin,
            'CodigoEstado'    => Estado::ESTADO_VIGENTE,
            'CodigoUsuario'   => Yii::$app->user->identity->CodigoUsuario ?? null,
        ]);

        $transaction = Pei::getDb()->beginTransaction();

        $resultado = $this->validarProcesarModelo($modelo);
        try {
            PeiDao::generarGestionesPei($modelo);

            if ($resultado['message'] != 'ok') {
                $transaction->rollBack();
                return $resultado;
            }

            $transaction->commit();
            return $resultado;
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Actualiza la informacion de un registro en el modelo
     *
     * @param string $id
     * @param PeiForm $form
     * @return array
     * @throws Exception
     * @throws Throwable
     * @throws ValidationException
     * @throws StaleObjectException
     */
    public function actualizar(string $id, PeiForm $form): array
    {
        $modelo = $this->obtenerModeloValidado($id);

        if ($modelo->GestionInicio < $form->gestionInicio ){
            if (!PeiDao::validarGestionInicio($modelo->IdPei, $form->gestionInicio)) {
                throw new ValidationException(Yii::$app->params['ERROR_GESTION_INICIO'],'Existen indicadores programados con meta que serian afectados por el cambio de fecha de inicio',400);
            }
        }

        if ($modelo->GestionFin > $form->gestionFin ){
            if (!PeiDao::validarGestionFin($modelo->IdPei, $form->gestionFin)) {
                throw new ValidationException(Yii::$app->params['ERROR_GESTION_FIN'],'Existen indicadores programados con meta que serian afectados por el cambio de fecha de fin',400);
            }
        }

        $modelo->Descripcion = $form->descripcion;
        $modelo->FechaAprobacion = $form->fechaAprobacion;
        $modelo->GestionInicio = $form->gestionInicio;
        $modelo->GestionFin = $form->gestionFin;

        $transaction = Pei::getDb()->beginTransaction();

        try {
        PeiDao::regularizarProgramacionIndicadoresInicio($modelo->IdPei, $form->gestionInicio);
        PeiDao::regularizarProgramacionIndicadoresFin($modelo->IdPei, $form->gestionFin);

        $resultado = $this->validarProcesarModelo($modelo);

        if ($resultado['message'] != 'ok') {
            $transaction->rollBack();
            return $resultado;
        }

        $transaction->commit();
        return $resultado;
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Busca un PEI por su código y alterna su estado.
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
            Yii::error("Error al guardar el cambio de estado del PEI $modelo->Descripcion", __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'],$modelo->getErrors(),500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $modelo->CodigoEstado,
        ];
    }

    /**
     * Busca un PEI por su código y realiza un soft delete.
     *
     * @param string $id
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     */
    public function eliminar(string $id): array
    {
        $modelo = $this->obtenerModeloValidado($id);

        if (PeiDao::enUso($modelo)){
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_EN_USO'],'El PEI se encuentra asignado a un objetivo estrategico',500);
        }

        $modelo->eliminar();
        PeiDao::eliminarGestionesPei($modelo);

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
        $model = $this->listarUno($id);

        if (!$model) {
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],'Registro no encontrado',404);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $model->getAttributes(array('IdPei', 'Descripcion', 'FechaAprobacion', 'GestionInicio', 'GestionFin')),
        ];
    }


    /**
     * Obtiene el modelo segun el codigo enviado y valida si existe.
     *
     * @param string $id
     * @return Pei|null
     * @throws ValidationException
     */
    private function obtenerModeloValidado(string $id): ?Pei
    {
        $model = $this->listarUno($id);
        if (!$model) {
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],'No se encontro el registro buscado',404);
        }
        return $model;
    }

    /**
     *  Recibe un modelo lo valida y realiza el guardado del mismo.
     *
     * @param Pei $model
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     */
    public function validarProcesarModelo(Pei $model): array
    {
        if (!$model->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'],$model->getErrors(),500);
        }

        if (!$model->save(false)) {
            Yii::error("Error al guardar el cambio de estado del PEI $model->IdPei", __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'],$model->getErrors(),500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => '',
        ];
    }
}
