<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\formModels\IndicadorEstrategicoForm;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\dao\IndicadorEstrategicoDao;
use app\modules\Planificacion\models\Indicador;
use app\modules\Planificacion\models\IndicadorEstrategico;
use yii\db\StaleObjectException;
use common\models\Estado;
use yii\db\Exception;
use Throwable;
use Yii;

class IndicadorEstrategicoService
{
    private ObjetivoEstrategicoService $serviceObjEstrategico;
    private CatCategoriaIndicadorService $serviceCategoriaIndicador;
    private CatTipoResultadoService $serviceTipoResultado;
    private CatUnidadIndicadorService $serviceUnidadIndicador;

    private AccionEstrategicaService $serviceAccionEstrategica;

    public function __construct(ObjetivoEstrategicoService   $serviceObjEstrategico,
                                CatCategoriaIndicadorService $serviceCategoriaIndicador,
                                CatTipoResultadoService      $serviceTipoResultado,
                                CatUnidadIndicadorService    $serviceUnidadIndicador,
                                AccionEstrategicaService     $serviceAccionEstrategica)
    {
        $this->serviceObjEstrategico = $serviceObjEstrategico;
        $this->serviceCategoriaIndicador = $serviceCategoriaIndicador;
        $this->serviceTipoResultado = $serviceTipoResultado;
        $this->serviceUnidadIndicador = $serviceUnidadIndicador;
        $this->serviceAccionEstrategica = $serviceAccionEstrategica;
    }


    /**
     * Lista un array de Indicadores Estrategicos no eliminados
     *
     * @return array of Indicadores Estategicos
     */
    public function listarTodo(): array
    {
        $data = IndicadorEstrategico::listAll()
            ->orderBy(['Codigo' => SORT_ASC])
            ->asArray()->all();

        return ResponseHelper::success($data, 'Listado de Indicadores Estrategicos obtenido.');
    }

    /**
     * Lista un array de Indicadores Estrategicos no eliminados según un, Id Objetivo Estrategico
     *
     * @return array of Indicadores Estategicos segun
     */
    public function listarTodobyObj(string $id): array
    {
        $data = IndicadorEstrategico::listAll()
            ->andWhere(['I.IdObjEstrategico' => $id])
            ->orderBy(['Codigo' => SORT_ASC])
            ->asArray()->all();

        return ResponseHelper::success($data, 'Listado de Indicadores Estrategicos por Objetivo obtenido.');
    }

    /**
     * Obtiene un Objetivo Estrategico con base en un codigo.
     *
     * @param string $id
     * @return IndicadorEstrategico|null
     */
    public function listarUno(string $id): ?IndicadorEstrategico
    {
        return IndicadorEstrategico::listOne($id);
    }

    /**
     * Obtiene un Objetivo Estrategico con base en un codigo.
     *
     * @param string $id
     * @return array
     */
    public function listarUnoArray(string $id): array
    {
        return IndicadorEstrategico::listOneArray($id);
    }

    /**
     * @param IndicadorEstrategicoForm $form
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     * @throws Throwable
     */
    public function validarGuardar(IndicadorEstrategicoForm $form): array
    {
        $this->validarEntidades($form);
        return $this->guardar($form);
    }

    /**
     * Guarda un nuevo REGISTRO.
     *
     * @param IndicadorEstrategicoForm $form
     * @return array ['message' => string, 'data' => string]
     * @throws Exception|ValidationException
     * @throws Throwable
     */
    public function guardar(IndicadorEstrategicoForm $form): array
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $indicador = new Indicador([
                'Meta' => $form->meta,
                'Descripcion' => mb_strtoupper(trim($form->descripcion), 'UTF-8'),
                'LineaBase' => $form->lineaBase,
                'IdTipoResultado' => $form->idTipoResultado,
                'IdCategoriaIndicador' => $form->idCategoriaIndicador,
                'IdUnidadIndicador' => $form->idUnidadIndicador,
                'CodigoEstado' => Estado::ESTADO_VIGENTE,
                'CodigoUsuario' => Yii::$app->user->identity->CodigoUsuario ?? null,
            ]);

            if (!$indicador->validate()) {
                throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'], $indicador->getErrors(),  500);
            }

            if (!$indicador->save(false)) {
                Yii::error('Error al guardar Indicador',  __METHOD__ );
                throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'], $indicador->getErrors(),  500);
            }

            // =====================================================
            // 3. INDICADOR ESTRATÉGICO
            // =====================================================

            $indicadorEstrategico = new IndicadorEstrategico([
                'IdIndicador' => $indicador->IdIndicador,
                'IdPei' => $form->idPei,
                'IdObjEstrategico' => $form->idObjEstrategico,
                'IdAccionEstrategica' => $form->idAccionEstrategica,
                'Codigo' => $form->codigo,
                'CodigoEstado' => Estado::ESTADO_VIGENTE
            ]);

            // =====================================================
            // 4. GUARDAR INDICADOR ESTRATÉGICO
            // =====================================================

            if (!$indicadorEstrategico->validate()) {
                throw new ValidationException(
                    Yii::$app->params['ERROR_VALIDACION_MODELO'],
                    $indicadorEstrategico->getErrors(),
                    500
                );
            }

            if (!$indicadorEstrategico->save(false)) {
                Yii::error(
                    "Error al guardar el Indicador Estratégico $form->codigo",
                    __METHOD__
                );

                throw new ValidationException(
                    Yii::$app->params['ERROR_EJECUCION_SQL'],
                    $indicadorEstrategico->getErrors(),
                    500
                );
            }

            $transaction->commit();

            return [
                'message' => Yii::$app->params['PROCESO_CORRECTO'],
                'data' => '',
            ];

        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * @param string $id
     * @param IndicadorEstrategicoForm $form
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     * @throws Throwable
     */
    public function validarActualizar(string $id, IndicadorEstrategicoForm $form): array
    {
        $this->validarEntidades($form);

        return $this->actualizar($id, $form);
    }

    /**
     * Actualiza la informacion de un registro en el modelo
     *
     * @param string $id
     * @param IndicadorEstrategicoForm $form
     * @return array
     * @throws Exception
     * @throws Throwable
     * @throws ValidationException
     * @throws StaleObjectException
     */
    public function actualizar(string $id, IndicadorEstrategicoForm $form): array
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $indicador = $this->obtenerIndicadorValidado($id);
            $indicadorEstrategico = $this->obtenerModeloValidado($id);

            $indicador->Meta = $form->meta;
            $indicador->Descripcion = mb_strtoupper(trim($form->descripcion), 'UTF-8');
            $indicador->LineaBase = $form->lineaBase;
            $indicador->IdTipoResultado = $form->idTipoResultado;
            $indicador->IdCategoriaIndicador = $form->idCategoriaIndicador;
            $indicador->IdUnidadIndicador = $form->idUnidadIndicador;
            $indicador->CodigoUsuario = Yii::$app->user->identity->CodigoUsuario ?? null;

            if (!$indicador->validate()) {
                throw new ValidationException(
                    Yii::$app->params['ERROR_VALIDACION_MODELO'],
                    $indicador->getErrors(),
                    500
                );
            }

            if (!$indicador->save(false)) {
                Yii::error(
                    "Error al actualizar el Indicador $id",
                    __METHOD__
                );

                throw new ValidationException(
                    Yii::$app->params['ERROR_EJECUCION_SQL'],
                    $indicador->getErrors(),
                    500
                );
            }

            $indicadorEstrategico->IdPei = $form->idPei;
            $indicadorEstrategico->IdObjEstrategico = $form->idObjEstrategico;
            $indicadorEstrategico->IdAccionEstrategica = $form->idAccionEstrategica;
            $indicadorEstrategico->Codigo = $form->codigo;

            if (!$indicadorEstrategico->validate()) {
                throw new ValidationException(
                    Yii::$app->params['ERROR_VALIDACION_MODELO'],
                    $indicadorEstrategico->getErrors(),
                    500
                );
            }

            if (!$indicadorEstrategico->save(false)) {
                Yii::error(
                    "Error al actualizar el Indicador Estratégico $id",
                    __METHOD__
                );

                throw new ValidationException(
                    Yii::$app->params['ERROR_EJECUCION_SQL'],
                    $indicadorEstrategico->getErrors(),
                    500
                );
            }

            $transaction->commit();

            return [
                'message' => Yii::$app->params['PROCESO_CORRECTO'],
                'data' => ''
            ];

        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Busca un Indicador estrategico por su código y alterna su estado.
     *
     * @param string $id
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     */
    public function cambiarEstado(string $id): array
    {
        $modelo = $this->obtenerModeloValidado($id);
        $indicador = $this->obtenerIndicadorValidado($id);

        $modelo->cambiarEstado();
        $indicador->cambiarEstado();

        if (!$modelo->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'], $modelo->getErrors(), 500);
        }

        if (!$modelo->save(false)) {
            Yii::error("Error al guardar el cambio de estado del Indicador Estrategico $modelo->Codigo", __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'], $modelo->getErrors(), 500);
        }

        $indicador->save(false);

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $modelo->CodigoEstado,
        ];
    }

    /**
     * Busca un Indicador estrategico por su código y realiza un soft delete.
     *
     * @param string $id
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     */
    public function eliminar(string $id): array
    {
        $modelo = $this->obtenerModeloValidado($id);
        $indicador = $this->obtenerIndicadorValidado($id);

        if (IndicadorEstrategicoDao::enUso($modelo)) {
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_EN_USO'], 'El Indicador estrategico cuenta con una programacion', 500);
        }

        $modelo->eliminar();
        $indicador->eliminar();

        $indicador->save(false);
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
            'data' => $modelo
        ];
    }

    /**
     * Obtiene el modelo según el codigo enviado.
     *
     * @param string $id
     * @return array
     * @throws ValidationException
     */
    public function obtenerModeloArray(string $id): array
    {
        $modelo = $this->listarUnoArray($id);

        if (!$modelo) {
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'], 'Registro no encontrado', 404);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $modelo
        ];
    }


    /**
     * Obtiene el modelo según el codigo enviado y válida si existe.
     *
     * @param string $id
     * @return IndicadorEstrategico
     * @throws ValidationException
     */
    private function obtenerModeloValidado(string $id): IndicadorEstrategico
    {
        $modelo = $this->listarUno($id);
        if (!$modelo) {
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'], 'No se encontro el registro buscado', 404);
        }
        return $modelo;
    }

    /**
     * Obtiene el modelo según el codigo enviado y válida si existe.
     *
     * @param string $id
     * @return Indicador|null
     * @throws ValidationException
     */
    private function obtenerIndicadorValidado(string $id): ?Indicador
    {
        $modelo = Indicador::findOne($id);
        if (!$modelo) {
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'], 'No se encontro el registro buscado', 404);
        }
        return $modelo;
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
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'], $modelo->getErrors(), 500);
        }

        if (!$modelo->save(false)) {
            Yii::error("Error al guardar los datos del Indicador Estrategico $modelo->Codigo", __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'], $modelo->getErrors(), 500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => '',
        ];
    }

    /**
     *  Recibe un codigo y verifica si está en uso.
     *
     * @param string $id
     * @param int $codigo
     * @return bool
     */
    public function verificarCodigo(string $id, int $codigo): bool
    {
        return IndicadorEstrategicoDao::verificarCodigo($id, $codigo);
    }

    /**
     *  Recibe un id y verifica si existe.
     *
     * @param string $id
     * @param string $idObjEstrategico
     * @return bool
     */
    public function validarId(string $id, string $idObjEstrategico): bool
    {
        return IndicadorEstrategicoDao::validarId($id, $idObjEstrategico);
    }

    /**
     * @throws ValidationException
     */
    private function validarEntidades(IndicadorEstrategicoForm $form): void
    {
        $validaciones = [
            'ObjetivoEstrategico' => $this->serviceObjEstrategico->validarId($form->idObjEstrategico),
            'UnidadIndicador' => $this->serviceUnidadIndicador->validarId($form->idUnidadIndicador),
            'TipoResultado' => $this->serviceTipoResultado->validarId($form->idTipoResultado),
            'CategoriaIndicador' => $this->serviceCategoriaIndicador->validarId($form->idCategoriaIndicador),
            'AccionEstrategica' => $this->serviceAccionEstrategica->validarId($form->idAccionEstrategica),
        ];

        foreach ($validaciones as $nombre => $valido) {
            if (!$valido) {
                throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], "$nombre inválido", 400);
            }
        }
    }
}