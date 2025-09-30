<?php
namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\formModels\ObjetivoEstrategicoForm;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\models\ObjetivoEstrategico;
use app\modules\Planificacion\dao\ObjEstrategicoDao;
use yii\db\StaleObjectException;
use common\models\Estado;
use yii\db\Exception;
use Throwable;
use Yii;

class ObjetivoEstrategicoService
{
    /**
     * lista un array de Objetivos Estrategicos no eliminados
     *
     * @return array of Objetivos
     */
    public function listarObjetivos(): array
    {
        $data = ObjetivoEstrategico::listAll()
            ->asArray()
            ->all();
        return ResponseHelper::success($data,'Listado de Objetivos obtenido.');
    }

    /**
     * obtiene un Objetivo Estrategico en base a un codigo.
     *
     * @param int $codigo
     * @return ObjetivoEstrategico|null
     */
    public  function listarObjetivo(int $codigo): ?ObjetivoEstrategico
    {
        return ObjetivoEstrategico::listOne($codigo);
    }

    /**
     * Guarda un nuevo REGISTRO.
     *
     * @param ObjetivoEstrategicoForm $form
     * @return array ['message' => string, 'data' => string]
     * @throws Exception|ValidationException
     */
    public function guardarObjetivo(ObjetivoEstrategicoForm $form): array
    {
        $objetivo = new ObjetivoEstrategico([
            'CodigoObjEstrategico' => ObjEstrategicoDao::GenerarCodigoObjEstrategico(),
            'CodigoObjetivo'  => $form->codigoObjetivo,
            'Objetivo'  => mb_strtoupper(trim($form->objetivo), 'UTF-8'),
            'CodigoPei' => $form->CodigoPei,
            'CodigoEstado'    => Estado::ESTADO_VIGENTE,
            'CodigoUsuario'   => Yii::$app->user->identity->CodigoUsuario ?? null,
        ]);

        return $this->validarProcesarModelo($objetivo);
    }

    /**
     * Actualiza la informacion de un registro en el modelo
     *
     * @param int $codigo
     * @param ObjetivoEstrategicoForm $form
     * @return array
     * @throws Exception
     * @throws Throwable
     * @throws ValidationException
     * @throws StaleObjectException
     */
    public function actualizarObjetivo(int $codigo, ObjetivoEstrategicoForm $form): array
    {
        $objetivo = $this->obtenerModeloValidado($codigo);

        $objetivo->CodigoObjetivo = $form->codigoObjetivo;
        $objetivo->Objetivo = mb_strtoupper(trim($form->objetivo), 'UTF-8');
        $objetivo->CodigoPei = $form->CodigoPei;

        return $this->validarProcesarModelo($objetivo);
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
        $objetivo = $this->obtenerModeloValidado($codigo);

        $objetivo->cambiarEstado();

        if (!$objetivo->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'],$objetivo->getErrors(),500);
        }

        if (!$objetivo->save(false)) {
            Yii::error("Error al guardar el cambio de estado del Objetivo Estrategico $objetivo->CodigoObjetivo", __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'],$objetivo->getErrors(),500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $objetivo->CodigoEstado,
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
    public function eliminarObjetivo(int $codigo): array
    {
        $objetivo = $this->obtenerModeloValidado($codigo);

        if (ObjEstrategicoDao::enUso($objetivo)){
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_EN_USO'],'El Objetivo se encuentra asignado a un objetivo institucional',500);
        }

        $objetivo->eliminarObjetivo();
        return $this->validarProcesarModelo($objetivo);
    }

    /**
     * @param int $codigoPei
     * @param int $codigoObjEstrategico
     * @param string $codigoObjetivo
     * @return bool
     */
    public function verificarCodigo(int $codigoPei, int $codigoObjEstrategico, string $codigoObjetivo): bool
    {
        return ObjEstrategicoDao::verificarCodigo($codigoPei, $codigoObjEstrategico, $codigoObjetivo);
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
        $objetivo = $this->listarObjetivo($codigo);

        if (!$objetivo) {
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],'Registro no encontrado',404);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $objetivo->getAttributes(array('CodigoObjEstrategico', 'CodigoObjetivo', 'Objetivo', 'CodigoPei',)),
        ];
    }


    /**
     * Obtiene el modelo segun el codigo enviado y valida si existe.
     *
     * @param int $codigo
     * @return ObjetivoEstrategico|null
     * @throws ValidationException
     */
    private function obtenerModeloValidado(int $codigo): ?ObjetivoEstrategico
    {
        $model = $this->listarObjetivo($codigo);
        if (!$model) {
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],'No se encontro el registro buscado',404);
        }
        return $model;
    }

    /**
     *  Recibe un modelo lo valida y realiza el guardado del mismo.
     *
     * @param ObjetivoEstrategico $objetivo
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     */
    public function validarProcesarModelo(ObjetivoEstrategico $objetivo): array
    {
        if (!$objetivo->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'],$objetivo->getErrors(),500);
        }

        if (!$objetivo->save(false)) {
            Yii::error("Error al guardar los datos del objetivo $objetivo->CodigoObjetivo", __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'],$objetivo->getErrors(),500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => '',
        ];
    }
}