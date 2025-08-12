<?php
namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\dao\PeiDao;
use app\modules\Planificacion\formModels\PeiForm;
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
    public  function listarPeis(): array
    {
        $data = Pei::listAll()
            ->asArray()
            ->all();
        return ResponseHelper::success($data,'Listado de PEI obtenido.');
    }

    /**
     * obtiene un pei en base a un codigo.
     *
     * @param int $codigoPei
     * @return Pei|null
     */
    public  function listarPei(int $codigoPei): ?Pei
    {
        return Pei::listOne($codigoPei);
    }

    /**
     * Guarda un nuevo PEI.
     *
     * @param PeiForm $form
     * @return array ['message' => string, 'data' => string]
     * @throws Exception|ValidationException
     */
    public function guardarPei(PeiForm $form): array
    {
        $pei = new Pei([
            'CodigoPei'       => PeiDao::generarCodigoPei(),
            'DescripcionPei'  => mb_strtoupper(trim($form->descripcionPei), 'UTF-8'),
            'FechaAprobacion' => date("d/m/Y", strtotime($form->fechaAprobacion)),
            'GestionInicio'   => $form->gestionInicio,
            'GestionFin'      => $form->gestionFin,
            'CodigoEstado'    => Estado::ESTADO_VIGENTE,
            'CodigoUsuario'   => Yii::$app->user->identity->CodigoUsuario ?? null,
        ]);

        return $this->validarProcesarModelo($pei);
    }

    /**
     * Actualiza la informacion de un registro en el modelo
     *
     * @param int $codigo
     * @param PeiForm $form
     * @return array
     * @throws Exception
     * @throws Throwable
     * @throws ValidationException
     * @throws StaleObjectException
     */
    public function actualizarPei(int $codigo, PeiForm $form): array
    {
        $pei = $this->obtenerModeloValidado($codigo);

        if ($pei->GestionInicio < $form->gestionInicio ){
            if (!PeiDao::validarGestionInicio($pei->CodigoPei, $form->gestionInicio)) {
                throw new ValidationException(Yii::$app->params['ERROR_GESTION_INICIO'],'Existen indicadores programados con meta que serian afectados por el cambio de fecha de inicio',400);
            }
        }

        if ($pei->GestionFin > $form->gestionFin ){
            if (!PeiDao::validarGestionFin($pei->CodigoPei, $form->gestionFin)) {
                throw new ValidationException(Yii::$app->params['ERROR_GESTION_FIN'],'Existen indicadores programados con meta que serian afectados por el cambio de fecha de fin',400);
            }
        }

        $pei->DescripcionPei = $form->descripcionPei;
        $pei->FechaAprobacion = $form->fechaAprobacion;
        $pei->GestionInicio = $form->gestionInicio;
        $pei->GestionFin = $form->gestionFin;

        $transaction = Pei::getDb()->beginTransaction();

        try {
        PeiDao::regularizarProgramacionIndicadoresInicio($pei->CodigoPei, $form->gestionInicio);
        PeiDao::regularizarProgramacionIndicadoresFin($pei->CodigoPei, $form->gestionFin);

        $resultado = $this->validarProcesarModelo($pei);

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
     * @param int $codigo
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     */
    public function cambiarEstado(int $codigo): array
    {
        $pei = $this->obtenerModeloValidado($codigo);

        $pei->cambiarEstado();

        if (!$pei->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'],$pei->getErrors(),500);
        }

        if (!$pei->save(false)) {
            Yii::error("Error al guardar el cambio de estado del PEI $pei->CodigoPei", __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'],$pei->getErrors(),500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $pei->CodigoEstado,
        ];
    }

    /**
     * Busca un PEI por su código y realiza un soft delete.
     *
     * @param int $codigo
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     */
    public function eliminarPei(int $codigo): array
    {
        $pei = $this->obtenerModeloValidado($codigo);

        if (PeiDao::enUso($pei)){
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_EN_USO'],'El PEI se encuentra asignado a un objetivo estrategico',500);
        }

        $pei->eliminarPei();

        if (!$pei->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'],$pei->getErrors(),500);
        }

        if (!$pei->save(false)) {
            Yii::error("Error al guardar el cambio de estado del PEI $pei->CodigoPei", __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'],$pei->getErrors(),500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => '',
        ];
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
        $pei = $this->listarPei($codigo);

        if (!$pei) {
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],'Registro no encontrado',404);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $pei->getAttributes(array('CodigoPei', 'DescripcionPei', 'FechaAprobacion', 'GestionInicio', 'GestionFin')),
        ];
    }


    /**
     * Obtiene el modelo segun el codigo enviado y valida si existe.
     *
     * @param int $codigo
     * @return Pei|null
     * @throws ValidationException
     */
    private function obtenerModeloValidado(int $codigo): ?Pei
    {
        $model = $this->listarPei($codigo);
        if (!$model) {
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],'No se encontro el registro buscado',404);
        }
        return $model;
    }

    /**
     *  Recibe un modelo lo valida y realiza el guardado del mismo.
     *
     * @param Pei $pei
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     */
    public function validarProcesarModelo(Pei $pei): array
    {
        if (!$pei->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'],$pei->getErrors(),500);
        }

        if (!$pei->save(false)) {
            Yii::error("Error al guardar el cambio de estado del PEI $pei->CodigoPei", __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'],$pei->getErrors(),500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => '',
        ];
    }
}
