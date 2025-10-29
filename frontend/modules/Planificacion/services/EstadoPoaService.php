<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\formModels\EstadoPoaForm;
use app\modules\Planificacion\models\EstadoPoa;
use common\models\Estado;
use Yii;
use yii\db\Exception;

class EstadoPoaService
{
    public function listarEstadosPoa(): array
    {
        $data = EstadoPoa::listAll()
            ->asArray()
            ->all();

        return ResponseHelper::success($data, 'Listado de Estados POA obtenido.');
    }

    public function listarEstadoPoa(int $codigoEstadoPoa): ?EstadoPoa
    {
        return EstadoPoa::listOne($codigoEstadoPoa);
    }

    /**
     * @throws Exception
     * @throws ValidationException
     */
    public function guardarEstadoPoa(EstadoPoaForm $form): array
    {
        $codigoUsuario = Yii::$app->user->identity->CodigoUsuario ?? null;
        if (!$codigoUsuario) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'Usuario no autenticado o CódigoUsuario no disponible.',
                401
            );
        }

        $estado = new EstadoPoa([
            'Descripcion' => mb_strtoupper(trim($form->descripcion), 'UTF-8'),
            'Abreviacion' => mb_strtoupper(trim($form->abreviacion), 'UTF-8'),
            'EtapaActual' => (int) $form->etapaActual,
            'EtapaPredeterminada' => (int) $form->etapaPredeterminada,
            'Orden' => (int) $form->orden,
            'CodigoEstado' => Estado::ESTADO_VIGENTE,
            'CodigoUsuario' => $codigoUsuario,
        ]);

        if ($estado->exist()) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_EXISTE'],
                'Ya existe un estado POA con la misma descripción o abreviación.',
                409
            );
        }

        return $this->validarProcesarModelo($estado);
    }

    /**
     * @throws Exception
     * @throws ValidationException
     */
    public function actualizarEstadoPoa(int $codigoEstadoPoa, EstadoPoaForm $form): array
    {
        $estado = $this->obtenerModeloValidado($codigoEstadoPoa);

        $estado->Descripcion = mb_strtoupper(trim($form->descripcion), 'UTF-8');
        $estado->Abreviacion = mb_strtoupper(trim($form->abreviacion), 'UTF-8');
        $estado->EtapaActual = (int) $form->etapaActual;
        $estado->EtapaPredeterminada = (int) $form->etapaPredeterminada;
        $estado->Orden = (int) $form->orden;

        if ($estado->exist()) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_EXISTE'],
                'Ya existe un estado POA con la misma descripción o abreviación.',
                409
            );
        }

        return $this->validarProcesarModelo($estado);
    }

    /**
     * @throws Exception
     * @throws ValidationException
     */
    public function cambiarEstado(int $codigoEstadoPoa): array
    {
        $estado = $this->obtenerModeloValidado($codigoEstadoPoa);

        $estado->cambiarEstado();

        if (!$estado->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'], $estado->getErrors(), 500);
        }

        if (!$estado->save(false)) {
            Yii::error('Error al guardar el cambio de estado del Estado POA', __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'], $estado->getErrors(), 500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $estado->CodigoEstado,
        ];
    }

    /**
     * @throws Exception
     * @throws ValidationException
     */
    public function eliminarEstadoPoa(int $codigoEstadoPoa): array
    {
        $estado = $this->obtenerModeloValidado($codigoEstadoPoa);

        if ($estado->enUso()) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_EN_USO'],
                'El Estado POA se encuentra en uso y no puede ser eliminado.',
                500
            );
        }

        $estado->eliminar();

        if (!$estado->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'], $estado->getErrors(), 500);
        }

        if (!$estado->save(false)) {
            Yii::error('Error al eliminar (soft delete) el Estado POA', __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'], $estado->getErrors(), 500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => '',
        ];
    }

    /**
     * @throws ValidationException
     */
    public function obtenerModelo(int $codigoEstadoPoa): array
    {
        $estado = $this->listarEstadoPoa($codigoEstadoPoa);

        if (!$estado) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],
                'Registro no encontrado',
                404
            );
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $estado->getAttributes([
                'CodigoEstadoPOA',
                'Descripcion',
                'Abreviacion',
                'EtapaActual',
                'EtapaPredeterminada',
                'Orden',
            ]),
        ];
    }

    /**
     * @throws ValidationException
     */
    private function obtenerModeloValidado(int $codigoEstadoPoa): EstadoPoa
    {
        $estado = $this->listarEstadoPoa($codigoEstadoPoa);

        if (!$estado) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],
                'No se encontró el Estado POA solicitado.',
                404
            );
        }

        return $estado;
    }

    /**
     * @throws Exception
     * @throws ValidationException
     */
    private function validarProcesarModelo(EstadoPoa $estado): array
    {
        if (!$estado->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'], $estado->getErrors(), 500);
        }

        if (!$estado->save(false)) {
            Yii::error('Error al guardar el Estado POA', __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'], $estado->getErrors(), 500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => '',
        ];
    }
}
