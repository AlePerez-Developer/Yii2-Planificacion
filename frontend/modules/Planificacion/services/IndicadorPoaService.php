<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\dao\IndicadorPoaDao;
use app\modules\Planificacion\formModels\IndicadorPoaForm;
use app\modules\Planificacion\models\IndicadorPoa;
use common\models\Estado;
use Yii;

class IndicadorPoaService
{
    public function listarTodo(string $idLlavePresupuestaria, int $gestion): array
    {
        $data = IndicadorPoa::listAll($idLlavePresupuestaria, $gestion)
            ->orderBy(['Oe.Codigo' => SORT_ASC, 'I.Codigo' => SORT_ASC])
            ->asArray()
            ->all();

        return ResponseHelper::success($data, 'Listado de indicadores POA obtenido.');
    }

    public function guardar(IndicadorPoaForm $form, string $idLlavePresupuestaria, int $gestion): array
    {
        $this->validarObjetivoDelContexto($form->idObjEspecifico, $idLlavePresupuestaria, $gestion);

        $modelo = new IndicadorPoa([
            'IdObjEspecifico' => $form->idObjEspecifico,
            'Codigo' => $form->codigo,
            'Descripcion' => mb_strtoupper($form->descripcion, 'UTF-8'),
            'Meta' => $form->meta,
            'LineaBase' => $form->lineaBase,
            'IdTipoResultado' => $form->idTipoResultado,
            'IdCategoriaIndicador' => $form->idCategoriaIndicador,
            'IdUnidadIndicador' => $form->idUnidadIndicador,
            'CodigoEstado' => Estado::ESTADO_VIGENTE,
            'CodigoUsuario' => Yii::$app->user->identity->CodigoUsuario,
        ]);

        return $this->procesar($modelo);
    }

    public function actualizar(
        string $id,
        IndicadorPoaForm $form,
        string $idLlavePresupuestaria,
        int $gestion
    ): array {
        $this->validarObjetivoDelContexto($form->idObjEspecifico, $idLlavePresupuestaria, $gestion);
        $modelo = $this->obtenerModeloValidado($id, $idLlavePresupuestaria, $gestion);

        $modelo->setAttributes([
            'IdObjEspecifico' => $form->idObjEspecifico,
            'Codigo' => $form->codigo,
            'Descripcion' => mb_strtoupper($form->descripcion, 'UTF-8'),
            'Meta' => $form->meta,
            'LineaBase' => $form->lineaBase,
            'IdTipoResultado' => $form->idTipoResultado,
            'IdCategoriaIndicador' => $form->idCategoriaIndicador,
            'IdUnidadIndicador' => $form->idUnidadIndicador,
        ]);

        return $this->procesar($modelo);
    }

    public function cambiarEstado(string $id, string $idLlavePresupuestaria, int $gestion): array
    {
        $modelo = $this->obtenerModeloValidado($id, $idLlavePresupuestaria, $gestion);
        $modelo->cambiarEstado();
        $this->guardarModelo($modelo);
        return ['message' => Yii::$app->params['PROCESO_CORRECTO'], 'data' => $modelo->CodigoEstado];
    }

    public function eliminar(string $id, string $idLlavePresupuestaria, int $gestion): array
    {
        $modelo = $this->obtenerModeloValidado($id, $idLlavePresupuestaria, $gestion);

        if (IndicadorPoaDao::enUso($modelo)) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_EN_USO'],
                'El indicador POA tiene programación relacionada.',
                409
            );
        }

        $modelo->eliminar();
        return $this->procesar($modelo);
    }

    public function obtenerModelo(string $id, string $idLlavePresupuestaria, int $gestion): array
    {
        $modelo = $this->obtenerModeloValidado($id, $idLlavePresupuestaria, $gestion);

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $modelo->getAttributes([
                'IdIndicadorPoa', 'IdObjEspecifico', 'Codigo', 'Descripcion',
                'Meta', 'LineaBase', 'IdTipoResultado', 'IdCategoriaIndicador','IdUnidadIndicador'
            ]),
        ];
    }

    public function verificarCodigo(string $id, string $idObjEspecifico, int $codigo): bool
    {
        return IndicadorPoaDao::verificarCodigo($id, $idObjEspecifico, $codigo);
    }

    private function validarObjetivoDelContexto(
        string $idObjEspecifico,
        string $idLlavePresupuestaria,
        int $gestion
    ): void {
        $existe = \app\modules\Planificacion\models\ObjetivoEspecifico::listAll(
            $idLlavePresupuestaria,
            $gestion
        )->andWhere(['OE.IdObjEspecifico' => $idObjEspecifico])->exists();

        if (!$existe) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                ['idObjEspecifico' => ['El objetivo específico no pertenece al contexto activo.']],
                400
            );
        }
    }

    private function obtenerModeloValidado(string $id, string $idLlavePresupuestaria, int $gestion): IndicadorPoa
    {
        $modelo = IndicadorPoa::listAll($idLlavePresupuestaria, $gestion)
            ->andWhere(['I.IdIndicadorPoa' => $id])
            ->one();

        if ($modelo === null) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],
                'No se encontró el indicador POA solicitado.',
                404
            );
        }

        return $modelo;
    }

    private function procesar(IndicadorPoa $modelo): array
    {
        $this->guardarModelo($modelo);
        return ['message' => Yii::$app->params['PROCESO_CORRECTO'], 'data' => ''];
    }

    private function guardarModelo(IndicadorPoa $modelo): void
    {
        if (!$modelo->validate()) {
            throw new ValidationException(
                Yii::$app->params['ERROR_VALIDACION_MODELO'],
                $modelo->getErrors(),
                422
            );
        }

        if (!$modelo->save(false)) {
            throw new ValidationException(
                Yii::$app->params['ERROR_EJECUCION_SQL'],
                $modelo->getErrors(),
                500
            );
        }
    }
}
