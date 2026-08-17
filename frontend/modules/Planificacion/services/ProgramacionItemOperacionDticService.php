<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\formModels\ProgramacionItemOperacionDticForm;
use app\modules\Planificacion\models\Item;
use app\modules\Planificacion\models\OperacionDtic;
use app\modules\Planificacion\models\ProgramacionItem;
use app\modules\Planificacion\models\ProgramacionItemOperacionDtic;
use common\models\Estado;
use Yii;

class ProgramacionItemOperacionDticService
{
    public function listar(
        string $idOperacion,
        string $idLlave,
        string $idGestion,
        string $idEstadoPoa,
        int $codigoEstadoPoa
    ): array {
        $this->obtenerOperacionValidada($idOperacion, $idLlave, $idGestion, $idEstadoPoa);

        $data = ProgramacionItem::find()->alias('PI')
            ->select([
                'PI.IdProgramacionItem',
                'PI.IdItem',
                'PI.Cantidad',
                'PI.PrecioUnitario',
                'TipoItem' => 'I.TipoItem',
                'Descripcion' => 'I.Descripcion',
                'IdProgramacionItemOperacion' => 'A.IdProgramacionItemOperacion',
                'CantidadAsignada' => 'A.CantidadAsignada',
            ])
            ->innerJoin(['I' => Item::tableName()], 'I.IdItem = PI.IdItem')
            ->leftJoin(
                ['A' => ProgramacionItemOperacionDtic::tableName()],
                'A.IdProgramacionItem = PI.IdProgramacionItem AND A.IdOperacion = :idOperacion',
                [':idOperacion' => $idOperacion]
            )
            ->where([
                'PI.IdLlavePresupuestaria' => $idLlave,
                'PI.IdGestion' => $idGestion,
                'PI.CodigoEstadoPoa' => $codigoEstadoPoa,
                'PI.CodigoEstado' => Estado::ESTADO_VIGENTE,
                'I.CodigoEstado' => Estado::ESTADO_VIGENTE,
            ])
            ->orderBy(['I.Descripcion' => SORT_ASC])
            ->asArray()
            ->all();

        return ResponseHelper::success($data, 'Listado de ítems programados obtenido.');
    }

    public function guardar(
        ProgramacionItemOperacionDticForm $form,
        string $idLlave,
        string $idGestion,
        string $idEstadoPoa,
        int $codigoEstadoPoa
    ): array {
        $this->obtenerOperacionValidada(
            $form->idOperacion,
            $idLlave,
            $idGestion,
            $idEstadoPoa
        );
        $this->obtenerProgramacionValidada(
            $form->idProgramacionItem,
            $idLlave,
            $idGestion,
            $codigoEstadoPoa
        );

        $modelo = ProgramacionItemOperacionDtic::findOne([
            'IdProgramacionItem' => $form->idProgramacionItem,
            'IdOperacion' => $form->idOperacion,
        ]) ?? new ProgramacionItemOperacionDtic([
            'IdProgramacionItem' => $form->idProgramacionItem,
            'IdOperacion' => $form->idOperacion,
        ]);

        $modelo->CantidadAsignada = $form->cantidadAsignada;
        $this->guardarModelo($modelo);

        return ResponseHelper::success(
            [
                'IdProgramacionItemOperacion' => $modelo->IdProgramacionItemOperacion,
                'CantidadAsignada' => $modelo->CantidadAsignada,
            ],
            'Asignación guardada correctamente.'
        );
    }

    public function quitar(
        string $idOperacion,
        string $idProgramacionItem,
        string $idLlave,
        string $idGestion,
        string $idEstadoPoa,
        int $codigoEstadoPoa
    ): array {
        $this->obtenerOperacionValidada($idOperacion, $idLlave, $idGestion, $idEstadoPoa);
        $this->obtenerProgramacionValidada(
            $idProgramacionItem,
            $idLlave,
            $idGestion,
            $codigoEstadoPoa
        );

        $modelo = ProgramacionItemOperacionDtic::findOne([
            'IdProgramacionItem' => $idProgramacionItem,
            'IdOperacion' => $idOperacion,
        ]);

        if ($modelo === null) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],
                'No se encontró la asignación solicitada.',
                404
            );
        }

        if ($modelo->delete() === false) {
            throw new ValidationException(
                Yii::$app->params['ERROR_EJECUCION_SQL'],
                $modelo->getErrors(),
                500
            );
        }

        return ResponseHelper::success(null, 'Asignación eliminada correctamente.');
    }

    private function obtenerOperacionValidada(
        string $idOperacion,
        string $idLlave,
        string $idGestion,
        string $idEstadoPoa
    ): OperacionDtic {
        $modelo = OperacionDtic::listAll($idLlave, $idGestion, $idEstadoPoa)
            ->andWhere(['Op.IdOperacion' => $idOperacion])
            ->one();

        if ($modelo === null) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],
                'La operación no pertenece al contexto activo.',
                404
            );
        }

        return $modelo;
    }

    private function obtenerProgramacionValidada(
        string $idProgramacionItem,
        string $idLlave,
        string $idGestion,
        int $codigoEstadoPoa
    ): ProgramacionItem {
        $modelo = ProgramacionItem::find()->alias('PI')
            ->innerJoin(['I' => Item::tableName()], 'I.IdItem = PI.IdItem')
            ->where([
                'PI.IdProgramacionItem' => $idProgramacionItem,
                'PI.IdLlavePresupuestaria' => $idLlave,
                'PI.IdGestion' => $idGestion,
                'PI.CodigoEstadoPoa' => $codigoEstadoPoa,
                'PI.CodigoEstado' => Estado::ESTADO_VIGENTE,
                'I.CodigoEstado' => Estado::ESTADO_VIGENTE,
            ])
            ->one();

        if ($modelo === null) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],
                'El ítem programado no pertenece al contexto activo.',
                404
            );
        }

        return $modelo;
    }

    private function guardarModelo(ProgramacionItemOperacionDtic $modelo): void
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
