<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\formModels\IndicadorEstrategicoAccionForm;
use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\models\IndicadorEstrategico;
use yii\db\StaleObjectException;
use yii\db\Exception;
use Throwable;
use Yii;

class IndicadorEstrategicoAccionService
{
    private AccionEstrategicaService $serviceAccionEstrategica;
    public function __construct(AccionEstrategicaService $serviceAccionEstrategica)
    {
        $this->serviceAccionEstrategica = $serviceAccionEstrategica;
    }

    /**
     * @param string $id
     * @param IndicadorEstrategicoAccionForm $form
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     * @throws Throwable
     */
    public function validarActualizar(string $id, IndicadorEstrategicoAccionForm $form): array
    {
        $this->validarEntidades($form);

        return $this->actualizar($id, $form);
    }

    /**
     * Actualiza la informacion de un registro en el modelo
     *
     * @param string $id
     * @param IndicadorEstrategicoAccionForm $form
     * @return array
     * @throws Exception
     * @throws Throwable
     * @throws ValidationException
     * @throws StaleObjectException
     */
    public function actualizar(string $id, IndicadorEstrategicoAccionForm $form): array
    {
        $modelo = $this->obtenerModeloValidado($id);

        $modelo->IdAccionEstrategica = $form->idAccionEstrategica;
        $modelo->AccionDescripcion = mb_strtoupper(trim($form->accionDescripcion), 'UTF-8');

        return $this->validarProcesarModelo($modelo);
    }

    /**
     * Obtiene el modelo según el codigo enviado y válida si existe.
     *
     * @param string $id
     * @return IndicadorEstrategico|null
     * @throws ValidationException
     */
    private function obtenerModeloValidado(string $id): ?IndicadorEstrategico
    {
        $modelo = $this->listarUno($id);
        if (!$modelo) {
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],'No se encontro el registro buscado',404);
        }
        return $modelo;
    }

    /**
     * Obtiene un Objetivo Estrategico con base en un codigo.
     *
     * @param string $id
     * @return IndicadorEstrategico|null
     */
    public  function listarUno(string $id): ?IndicadorEstrategico
    {
        return IndicadorEstrategico::listOne($id);
    }

    /**
     *  Recibe un modelo lo valida y realiza el guardado del mismo.
     *
     * @param IndicadorEstrategico $modelo
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     */
    public function validarProcesarModelo(IndicadorEstrategico $modelo): array
    {
        if (!$modelo->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'],$modelo->getErrors(),500);
        }

        if (!$modelo->save(false)) {
            Yii::error("Error al guardar los datos del Indicador Estrategico $modelo->Codigo", __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'],$modelo->getErrors(),500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => '',
        ];
    }


    /**
     * @throws ValidationException
     */
    private function validarEntidades(IndicadorEstrategicoAccionForm $form): void
    {
        $validaciones = [
            'AccionEstrategica' => $this->serviceAccionEstrategica->validarId($form->idAccionEstrategica),
        ];

        foreach ($validaciones as $nombre => $valido) {
            if (!$valido) {
                throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], "$nombre inválido", 400);
            }
        }
    }
}