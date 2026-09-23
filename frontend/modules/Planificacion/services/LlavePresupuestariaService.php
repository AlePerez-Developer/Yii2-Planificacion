<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\formModels\LlavePresupuestariaForm;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\dao\LlavePresupuestariaDao;
use app\modules\Planificacion\models\LlavePresupuestaria;
use app\modules\Planificacion\models\Ue;
use app\modules\Planificacion\models\UnidadEjecutora;
use yii\db\StaleObjectException;
use common\models\Estado;
use yii\db\ActiveRecord;
use yii\db\Exception;
use Throwable;
use Yii;

class LlavePresupuestariaService
{
    public function __construct(
        private UnidadEjecutoraService $serviceUnidadEjecutora,
        private ProgramaService $servicePrograma,
        private ProyectoService $serviceProyecto,
        private ActividadService $serviceActividad,
    ) {
    }

    public function listarTodo(): array
    {
        $data = LlavePresupuestaria::listAll()
            ->asArray()->all();

        return ResponseHelper::success($data, 'Listado de Llaves presupuestarias obtenido.');
    }

    public function listarUno(string $id): ?LlavePresupuestaria
    {
        return LlavePresupuestaria::listOne($id);
    }

    public function listarUnoCompleto(string $id): array|ActiveRecord
    {
        return LlavePresupuestaria::listOneComplete($id);
    }

    public function listAllbyProgramacion(string $idIndicadorEstrategico, string $idGestion): array
    {
        return LlavePresupuestaria::listAllbyProgramacion($idIndicadorEstrategico, $idGestion);
    }

    /**
     * @throws Exception
     * @throws ValidationException
     */
    public function validarGuardar(LlavePresupuestariaForm $form): array
    {
        $unidad = $this->obtenerUnidadValidada($form);
        $this->validarPrograma($form);
        $form->fechaInicio = $this->validarFecha($form->fechaInicio);
        $form->llave = $this->generarLlave($unidad, $form);

        return $this->guardar($form, $unidad);
    }

    /**
     * @throws Exception
     * @throws Throwable
     * @throws ValidationException
     * @throws StaleObjectException
     */
    public function validarActualizar(string $id, LlavePresupuestariaForm $form): array
    {
        $unidad = $this->obtenerUnidadValidada($form);
        $this->validarPrograma($form);
        $form->fechaInicio = $this->validarFecha($form->fechaInicio);
        $form->llave = $this->generarLlave($unidad, $form);

        return $this->actualizar($id, $form, $unidad);
    }

    /**
     * @throws Exception|ValidationException
     */
    public function guardar(LlavePresupuestariaForm $form, UnidadEjecutora $unidad): array
    {
        $modelo = new LlavePresupuestaria([
            'IdUnidadEjecutora' => $form->idUnidadEjecutora,
            'IdDa' => $unidad->IdDa,
            'IdUe' => $this->obtenerIdUeCatalogo($unidad),
            'IdProyecto' => $form->idProyecto,
            'IdActividad' => $form->idActividad,
            'Llave' => $form->llave,
            'Descripcion' => '',
            'esOrganizacional' => $form->esOrganizacional,
            'FechaInicio' => $form->fechaInicio,
            'CodigoEstado' => Estado::ESTADO_VIGENTE,
            'CodigoUsuario' => Yii::$app->user->identity->CodigoUsuario ?? null,
        ]);

        return $this->validarProcesarModelo($modelo);
    }

    /**
     * @throws Exception
     * @throws Throwable
     * @throws ValidationException
     * @throws StaleObjectException
     */
    public function actualizar(string $id, LlavePresupuestariaForm $form, UnidadEjecutora $unidad): array
    {
        $modelo = $this->obtenerModeloValidado($id);

        $modelo->IdUnidadEjecutora = $form->idUnidadEjecutora;
        $modelo->IdDa = $unidad->IdDa;
        $modelo->IdUe = $this->obtenerIdUeCatalogo($unidad);
        $modelo->IdProyecto = $form->idProyecto;
        $modelo->IdActividad = $form->idActividad;
        $modelo->Llave = $form->llave;
        $modelo->Descripcion = '';
        $modelo->esOrganizacional = $form->esOrganizacional;
        $modelo->FechaInicio = $form->fechaInicio;

        return $this->validarProcesarModelo($modelo);
    }

    /**
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
            Yii::error("Error al guardar el cambio de estado de la llave presupuestaria $modelo->Llave", __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'], $modelo->getErrors(), 500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $modelo->CodigoEstado,
        ];
    }

    /**
     * @throws Exception
     * @throws ValidationException
     */
    public function eliminar(string $id): array
    {
        $modelo = $this->obtenerModeloValidado($id);

        if (LlavePresupuestariaDao::enUso($modelo)) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_EN_USO'],
                'La llave presupuestaria se encuentra en uso.',
                500
            );
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
     * @throws ValidationException
     */
    public function obtenerModeloCompleto(string $id): array
    {
        $modelo = $this->listarUnoCompleto($id);

        if (!$modelo) {
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'], 'Registro no encontrado', 404);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $modelo,
        ];
    }

    /**
     * @throws ValidationException
     */
    private function obtenerModeloValidado(string $id): ?LlavePresupuestaria
    {
        $modelo = $this->listarUno($id);
        if (!$modelo) {
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'], 'No se encontro el registro buscado', 404);
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

    public function VerificarLlave(
        string $id,
        string $idUnidadEjecutora,
        string $idProyecto,
        string $idActividad
    ): bool {
        return LlavePresupuestariaDao::verificarCodigo($id, $idUnidadEjecutora, $idProyecto, $idActividad);
    }

    /**
     * @throws ValidationException
     */
    private function obtenerUnidadValidada(LlavePresupuestariaForm $form): UnidadEjecutora
    {
        $unidad = $this->serviceUnidadEjecutora->listarUno($form->idUnidadEjecutora);
        if (!$unidad) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'Unidad ejecutora inválida', 400);
        }

        return $unidad;
    }

    private function obtenerIdUeCatalogo(UnidadEjecutora $unidad): string
    {
        $ue = Ue::find()
            ->where(['Ue' => $unidad->Ue])
            ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->one();

        return (string)($ue?->IdUe ?? '');
    }

    /**
     * @throws ValidationException
     */
    private function validarPrograma(LlavePresupuestariaForm $form): string
    {
        if (!$this->serviceProyecto->validarId($form->idProyecto)
            || !$this->serviceActividad->validarId($form->idActividad)
        ) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'Proyecto o actividad inválidos', 400);
        }

        $pr1 = $this->serviceProyecto->getIdPrograma($form->idProyecto);
        $pr2 = $this->serviceActividad->getIdPrograma($form->idActividad);

        if ($pr1 !== $pr2 || empty($pr1) || $pr1 !== $form->idPrograma) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'Proyecto y Actividad no pertenecen al mismo programa',
                400
            );
        }

        return $pr1;
    }

    private function generarLlave(UnidadEjecutora $unidad, LlavePresupuestariaForm $form): string
    {
        $unidad->das;

        return implode('-', [
            (string)($unidad->das->Da ?? ''),
            (string)$unidad->Ue,
            $this->servicePrograma->getCodigo($form->idPrograma),
            $this->serviceProyecto->getCodigo($form->idProyecto),
            $this->serviceActividad->getCodigo($form->idActividad),
        ]);
    }

    /**
     * @throws ValidationException
     */
    private function validarFecha(string $fechaInicio): string
    {
        $timestamp = strtotime($fechaInicio);

        if (!$timestamp) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'Fecha de inicio invalida', 400);
        }

        return date('d/m/Y', $timestamp);
    }
}
