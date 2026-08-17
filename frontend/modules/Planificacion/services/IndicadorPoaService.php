<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\dao\IndicadorPoaDao;
use app\modules\Planificacion\formModels\IndicadorPoaForm;
use app\modules\Planificacion\models\Indicador;
use app\modules\Planificacion\models\IndicadorPoa;
use common\models\Estado;
use Throwable;
use Yii;

class IndicadorPoaService
{
    public function __construct(
        private CatCategoriaIndicadorService $categoriaService,
        private CatTipoResultadoService $tipoResultadoService,
        private CatUnidadIndicadorService $unidadService
    ) {
    }

    public function listarTodo(string $idGestion): array
    {
        $data = IndicadorPoa::listAll($idGestion)
            ->orderBy(['P.Codigo' => SORT_ASC])
            ->asArray()
            ->all();

        return ResponseHelper::success($data, 'Listado de indicadores POA obtenido.');
    }

    public function guardar(IndicadorPoaForm $form, string $idGestion): array
    {
        $this->validarEntidades($form);
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
                'CodigoUsuario' => Yii::$app->user->identity->CodigoUsuario,
            ]);
            $this->guardarIndicador($indicador);

            $indicadorPoa = new IndicadorPoa([
                'IdIndicador' => $indicador->IdIndicador,
                'IdGestion' => $idGestion,
                'Codigo' => $form->codigo,
                'CodigoEstado' => Estado::ESTADO_VIGENTE,
            ]);
            $this->guardarIndicadorPoa($indicadorPoa);

            $transaction->commit();
            return ResponseHelper::success(null, Yii::$app->params['PROCESO_CORRECTO']);
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    public function actualizar(
        string $id,
        IndicadorPoaForm $form,
        string $idGestion
    ): array {
        $this->validarEntidades($form);
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $indicadorPoa = $this->obtenerModeloValidado($id, $idGestion);
            $indicador = $this->obtenerIndicadorValidado($id);

            $indicador->setAttributes([
                'Meta' => $form->meta,
                'Descripcion' => mb_strtoupper(trim($form->descripcion), 'UTF-8'),
                'LineaBase' => $form->lineaBase,
                'IdTipoResultado' => $form->idTipoResultado,
                'IdCategoriaIndicador' => $form->idCategoriaIndicador,
                'IdUnidadIndicador' => $form->idUnidadIndicador,
                'CodigoUsuario' => Yii::$app->user->identity->CodigoUsuario,
            ]);
            $this->guardarIndicador($indicador);

            $indicadorPoa->Codigo = $form->codigo;
            $this->guardarIndicadorPoa($indicadorPoa);

            $transaction->commit();
            return ResponseHelper::success(null, Yii::$app->params['PROCESO_CORRECTO']);
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    public function cambiarEstado(string $id, string $idGestion): array
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $indicadorPoa = $this->obtenerModeloValidado($id, $idGestion);
            $indicador = $this->obtenerIndicadorValidado($id);

            $indicadorPoa->cambiarEstado();
            $indicador->CodigoEstado = $indicadorPoa->CodigoEstado;
            $this->guardarIndicador($indicador);
            $this->guardarIndicadorPoa($indicadorPoa);

            $transaction->commit();
            return ResponseHelper::success(
                $indicadorPoa->CodigoEstado,
                Yii::$app->params['PROCESO_CORRECTO']
            );
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    public function eliminar(string $id, string $idGestion): array
    {
        $indicadorPoa = $this->obtenerModeloValidado($id, $idGestion);

        if (IndicadorPoaDao::enUso($indicadorPoa)) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_EN_USO'],
                'El indicador POA tiene programación relacionada.',
                409
            );
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            $indicador = $this->obtenerIndicadorValidado($id);
            $indicadorPoa->eliminar();
            $indicador->eliminar();
            $this->guardarIndicador($indicador);
            $this->guardarIndicadorPoa($indicadorPoa);

            $transaction->commit();
            return ResponseHelper::success(null, Yii::$app->params['PROCESO_CORRECTO']);
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    public function obtenerModelo(string $id, string $idGestion): array
    {
        $data = IndicadorPoa::listOneArray($id, $idGestion);

        if ($data === null) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],
                'No se encontró el indicador POA solicitado.',
                404
            );
        }

        return ResponseHelper::success($data);
    }

    public function verificarCodigo(
        string $id,
        string $idGestion,
        int $codigo
    ): bool {
        return IndicadorPoaDao::verificarCodigo($id, $idGestion, $codigo);
    }

    private function validarEntidades(IndicadorPoaForm $form): void
    {
        $validaciones = [
            'Tipo de resultado' => $this->tipoResultadoService->validarId($form->idTipoResultado),
            'Categoría de indicador' => $this->categoriaService->validarId($form->idCategoriaIndicador),
            'Unidad de indicador' => $this->unidadService->validarId($form->idUnidadIndicador),
        ];

        foreach ($validaciones as $nombre => $valido) {
            if (!$valido) {
                throw new ValidationException(
                    Yii::$app->params['ERROR_ENVIO_DATOS'],
                    "$nombre inválido.",
                    400
                );
            }
        }
    }

    private function obtenerModeloValidado(string $id, string $idGestion): IndicadorPoa
    {
        $modelo = IndicadorPoa::listOne($id, $idGestion);

        if ($modelo === null) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],
                'No se encontró el indicador POA solicitado.',
                404
            );
        }

        return $modelo;
    }

    private function obtenerIndicadorValidado(string $id): Indicador
    {
        $modelo = Indicador::findOne($id);

        if ($modelo === null) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],
                'No se encontró la información base del indicador.',
                404
            );
        }

        return $modelo;
    }

    private function guardarIndicador(Indicador $modelo): void
    {
        if (!$modelo->validate() || !$modelo->save(false)) {
            throw new ValidationException(
                Yii::$app->params['ERROR_VALIDACION_MODELO'],
                $modelo->getErrors(),
                422
            );
        }
    }

    private function guardarIndicadorPoa(IndicadorPoa $modelo): void
    {
        if (!$modelo->validate() || !$modelo->save(false)) {
            throw new ValidationException(
                Yii::$app->params['ERROR_VALIDACION_MODELO'],
                $modelo->getErrors(),
                422
            );
        }
    }
}
