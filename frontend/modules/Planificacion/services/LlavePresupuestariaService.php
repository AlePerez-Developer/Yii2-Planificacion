<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\formModels\LlavePresupuestariaForm;
use app\modules\Planificacion\models\LlavePresupuestaria;
use common\models\Estado;
use Yii;
use yii\db\Exception;
use yii\db\Expression;

class LlavePresupuestariaService
{
    public function listarLlaves(): array
    {
        $data = LlavePresupuestaria::listAll()
            ->asArray()
            ->all();

        return ResponseHelper::success($data, 'Listado de Llaves Presupuestarias obtenido.');
    }

    public function listarLlave(int $codigoUnidad, int $codigoPrograma, int $codigoProyecto, int $codigoActividad): ?LlavePresupuestaria
    {
        return LlavePresupuestaria::listOne($codigoUnidad, $codigoPrograma, $codigoProyecto, $codigoActividad);
    }

    /**
     * @throws Exception
     * @throws ValidationException
     */
    public function guardarLlave(LlavePresupuestariaForm $form): array
    {
        $codigoUsuario = Yii::$app->user->identity->CodigoUsuario ?? null;
        if (!$codigoUsuario) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'] ?? 'errorEnvio',
                'Usuario no autenticado o CódigoUsuario no disponible.',
                401
            );
        }

        $existente = LlavePresupuestaria::find()
            ->where([
                'CodigoUnidad' => (int) $form->codigoUnidad,
                'CodigoPrograma' => (int) $form->codigoPrograma,
                'CodigoProyecto' => (int) $form->codigoProyecto,
                'CodigoActividad' => (int) $form->codigoActividad,
            ])
            ->one();

        if ($existente !== null && $existente->CodigoEstado !== Estado::ESTADO_ELIMINADO) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_EXISTE'] ?? 'errorExiste',
                'Ya existe una llave presupuestaria para la combinación seleccionada.',
                409
            );
        }

        if ($existente !== null && $existente->CodigoEstado === Estado::ESTADO_ELIMINADO) {
            $existente->CodigoEstado = Estado::ESTADO_VIGENTE;
            $existente->FechaHoraRegistro = new Expression('GETDATE()');
            $existente->CodigoUsuario = $codigoUsuario;

            $this->mapearFormulario($existente, $form);

            return $this->validarProcesarModelo($existente);
        }

        $llave = new LlavePresupuestaria([
            'CodigoEstado' => Estado::ESTADO_VIGENTE,
            'FechaHoraRegistro' => new Expression('GETDATE()'),
            'CodigoUsuario' => $codigoUsuario,
        ]);

        $this->mapearFormulario($llave, $form);

        if ($llave->exist()) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_EXISTE'] ?? 'errorExiste',
                'Ya existe una llave presupuestaria para la combinación seleccionada.',
                409
            );
        }

        return $this->validarProcesarModelo($llave);
    }

    /**
     * @throws Exception
     * @throws ValidationException
     * @throws Throwable
     */
    public function actualizarLlave(
        int $codigoUnidad,
        int $codigoPrograma,
        int $codigoProyecto,
        int $codigoActividad,
        LlavePresupuestariaForm $form
    ): array {
        $llave = $this->obtenerModeloValidado($codigoUnidad, $codigoPrograma, $codigoProyecto, $codigoActividad);

        $this->mapearFormulario($llave, $form);

        if ($llave->exist()) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_EXISTE'] ?? 'errorExiste',
                'Ya existe una llave presupuestaria para la combinación seleccionada.',
                409
            );
        }

        return $this->validarProcesarModelo($llave);
    }

    /**
     * @throws Exception
     * @throws ValidationException
     */
    public function cambiarEstado(int $codigoUnidad, int $codigoPrograma, int $codigoProyecto, int $codigoActividad): array
    {
        $llave = $this->obtenerModeloValidado($codigoUnidad, $codigoPrograma, $codigoProyecto, $codigoActividad);

        $llave->cambiarEstado();

        if (!$llave->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'], $llave->getErrors(), 500);
        }

        if (!$llave->save(false)) {
            Yii::error('Error al guardar el cambio de estado de la Llave Presupuestaria', __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'], $llave->getErrors(), 500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $llave->CodigoEstado,
        ];
    }

    /**
     * @throws Exception
     * @throws ValidationException
     */
    public function eliminarLlave(int $codigoUnidad, int $codigoPrograma, int $codigoProyecto, int $codigoActividad): array
    {
        $llave = $this->obtenerModeloValidado($codigoUnidad, $codigoPrograma, $codigoProyecto, $codigoActividad);

        if ($llave->enUso()) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_EN_USO'],
                'La llave presupuestaria está en uso y no puede ser eliminada',
                500
            );
        }

        $llave->eliminar();

        if (!$llave->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'], $llave->getErrors(), 500);
        }

        if (!$llave->save(false)) {
            Yii::error('Error al guardar el cambio de estado de la Llave Presupuestaria', __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'], $llave->getErrors(), 500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => '',
        ];
    }

    /**
     * @throws Exception
     * @throws ValidationException
     */
    public function finalizarLlave(int $codigoUnidad, int $codigoPrograma, int $codigoProyecto, int $codigoActividad): array
    {
        $llave = $this->obtenerModeloValidado($codigoUnidad, $codigoPrograma, $codigoProyecto, $codigoActividad);

        // La validación de fecha de inicio se realiza en el modelo LlavePresupuestaria::finalizar()
        // Si la fecha de inicio es futura, lanzará una excepción antes de llegar aquí
        
        $llave->finalizar();

        if (!$llave->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'], $llave->getErrors(), 500);
        }

        if (!$llave->save(false)) {
            Yii::error('Error al guardar el cambio de estado de la Llave Presupuestaria', __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'], $llave->getErrors(), 500);
        }

        $llave->refresh();

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => [
                'FechaFin' => $llave->FechaFin,
                'CodigoEstado' => $llave->CodigoEstado,
            ],
        ];
    }

    /**
     * @throws ValidationException
     */
    public function obtenerModelo(int $codigoUnidad, int $codigoPrograma, int $codigoProyecto, int $codigoActividad): array
    {
        $llave = $this->listarLlave($codigoUnidad, $codigoPrograma, $codigoProyecto, $codigoActividad);

        if (!$llave) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],
                'Registro no encontrado',
                404
            );
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $llave->getAttributes([
                'CodigoUnidad',
                'CodigoPrograma',
                'CodigoProyecto',
                'CodigoActividad',
                'Descripcion',
                'TechoPresupuestario',
                'FechaInicio',
                'FechaFin',
            ]),
        ];
    }

    /**
     * @throws ValidationException
     */
    private function obtenerModeloValidado(
        int $codigoUnidad,
        int $codigoPrograma,
        int $codigoProyecto,
        int $codigoActividad
    ): LlavePresupuestaria {
        $llave = $this->listarLlave($codigoUnidad, $codigoPrograma, $codigoProyecto, $codigoActividad);

        if (!$llave) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'] ?? 'errorNoEncontrado',
                'No se encontró la llave presupuestaria solicitada.',
                404
            );
        }

        return $llave;
    }

    /**
     * @throws ValidationException
     */
    private function mapearFormulario(LlavePresupuestaria $llave, LlavePresupuestariaForm $form): void
    {
        $fechaInicio = $this->normalizarFecha($form->fechaInicio, 'fechaInicio', false);
        $fechaFin = $this->normalizarFecha($form->fechaFin, 'fechaFin', true);

        $inicioTimestamp = strtotime($fechaInicio);
        $finTimestamp = $fechaFin ? strtotime($fechaFin) : null;
        if ($finTimestamp !== null && $finTimestamp < $inicioTimestamp) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'] ?? 'errorEnvio',
                ['fechaFin' => ['La fecha fin no puede ser anterior a la fecha inicio.']],
                422
            );
        }

        if ($finTimestamp !== null && $finTimestamp > time()) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'] ?? 'errorEnvio',
                ['fechaFin' => ['La fecha fin no puede ser posterior a la fecha actual (restricción chk_LlaveFechas).']],
                422
            );
        }

        $llave->CodigoUnidad = (int) $form->codigoUnidad;
        $llave->CodigoPrograma = (int) $form->codigoPrograma;
        $llave->CodigoProyecto = (int) $form->codigoProyecto;
        $llave->CodigoActividad = (int) $form->codigoActividad;
        $llave->Descripcion = mb_strtoupper(trim($form->descripcion), 'UTF-8');
        $llave->TechoPresupuestario = (float) $form->techoPresupuestario;
        $llave->FechaInicio = $fechaInicio;

        $llave->FechaFin = $fechaFin;
    }

    private function normalizarFecha(?string $fecha, string $atributo, bool $permiteNulo): ?string
    {
        if ($fecha === null || $fecha === '') {
            if ($permiteNulo) {
                return null;
            }

            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'] ?? 'errorEnvio',
                [$atributo => ['El campo es obligatorio.']],
                422
            );
        }

        $timestamp = strtotime($fecha);
        if ($timestamp === false) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'] ?? 'errorEnvio',
                [$atributo => ['La fecha proporcionada no tiene un formato válido.']],
                422
            );
        }

        return date('Y-m-d\TH:i:s', $timestamp);
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
