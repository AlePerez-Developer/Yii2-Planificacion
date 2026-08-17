<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\dao\IngresoDao;
use app\modules\Planificacion\dao\PresupuestoConsumoDao;
use app\modules\Planificacion\dao\TechoUnidadDao;
use app\modules\Planificacion\formModels\TechoUnidadForm;
use app\modules\Planificacion\models\LlavePresupuestaria;
use app\modules\Planificacion\models\TechoUnidad;
use common\models\Estado;
use Yii;
use yii\db\Expression;

class TechoUnidadService
{
    public function listarLlaves(
        string $idUnidadEjecutora,
        string $idGestion,
        string $idEstadoPoa
    ): array {
        $data = LlavePresupuestaria::find()->alias('LP')
            ->select([
                'LP.IdLlavePresupuestaria',
                'LP.Llave',
                'LP.Descripcion',
                'TU.IdAsignacion',
                'Techo' => new Expression('COALESCE(TU.Techo, 0)'),
                'TechoEstado' => 'TU.CodigoEstado',
            ])
            ->leftJoin(
                ['TU' => TechoUnidad::tableName()],
                'TU.IdLlavePresupuestaria = LP.IdLlavePresupuestaria
                 AND TU.IdGestion = :gestion
                 AND TU.CodigoEstado <> :eliminado',
                [':gestion' => $idGestion, ':eliminado' => Estado::ESTADO_ELIMINADO]
            )
            ->where([
                'LP.IdUnidadEjecutora' => $idUnidadEjecutora,
                'LP.CodigoEstado' => Estado::ESTADO_VIGENTE,
            ])
            ->orderBy(['LP.Llave' => SORT_ASC])
            ->asArray()
            ->all();

        foreach ($data as &$fila) {
            $usado = PresupuestoConsumoDao::totalPorLlave(
                $fila['IdLlavePresupuestaria'],
                $idGestion,
                $idEstadoPoa
            );
            $fila['MontoUsado'] = $usado;
            $fila['DisponibleReal'] = max((float)$fila['Techo'] - $usado, 0);
        }
        unset($fila);

        return ResponseHelper::success($data, 'Llaves presupuestarias obtenidas.');
    }

    public function resumen(
        string $idUnidadEjecutora,
        string $idGestion,
        string $idEstadoPoa
    ): array {
        $existenIngresos = IngresoDao::existen(
            $idUnidadEjecutora,
            $idGestion,
            $idEstadoPoa
        );
        $totalIngresos = IngresoDao::total(
            $idUnidadEjecutora,
            $idGestion,
            $idEstadoPoa
        );
        $totalTechos = TechoUnidadDao::total($idUnidadEjecutora, $idGestion);

        return ResponseHelper::success([
            'ExistenIngresos' => $existenIngresos,
            'TotalIngresos' => $totalIngresos,
            'TotalTechos' => $totalTechos,
            'Disponible' => $existenIngresos
                ? max($totalIngresos - $totalTechos, 0)
                : null,
        ]);
    }

    public function guardar(
        TechoUnidadForm $form,
        string $idUnidadEjecutora,
        string $idGestion,
        string $idEstadoPoa
    ): array {
        $llaveValida = LlavePresupuestaria::find()
            ->where([
                'IdLlavePresupuestaria' => $form->idLlavePresupuestaria,
                'IdUnidadEjecutora' => $idUnidadEjecutora,
                'CodigoEstado' => Estado::ESTADO_VIGENTE,
            ])
            ->exists();

        if (!$llaveValida) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'La llave no pertenece a la unidad ejecutora activa.',
                400
            );
        }

        $modelo = TechoUnidad::find()
            ->where([
                'IdLlavePresupuestaria' => $form->idLlavePresupuestaria,
                'IdGestion' => $idGestion,
            ])
            ->one();
        $idExcluir = $modelo?->IdAsignacion;
        $montoUsado = PresupuestoConsumoDao::totalPorLlave(
            $form->idLlavePresupuestaria,
            $idGestion,
            $idEstadoPoa
        );
        if ($form->techo < $montoUsado) {
            throw new ValidationException(
                Yii::$app->params['ERROR_VALIDACION_MODELO'],
                "No puede reducir el techo a {$form->techo}; la llave ya tiene {$montoUsado} en uso.",
                422
            );
        }

        $existenIngresos = IngresoDao::existen(
            $idUnidadEjecutora,
            $idGestion,
            $idEstadoPoa
        );
        if ($existenIngresos) {
            $totalIngresos = IngresoDao::total(
                $idUnidadEjecutora,
                $idGestion,
                $idEstadoPoa
            );
            $otrosTechos = TechoUnidadDao::total(
                $idUnidadEjecutora,
                $idGestion,
                $idExcluir
            );

            if ($otrosTechos + $form->techo > $totalIngresos) {
                $disponible = max($totalIngresos - $otrosTechos, 0);
                throw new ValidationException(
                    Yii::$app->params['ERROR_VALIDACION_MODELO'],
                    "El techo supera el monto disponible ({$disponible}).",
                    422
                );
            }
        }

        if ($modelo === null) {
            $modelo = new TechoUnidad([
                'IdLlavePresupuestaria' => $form->idLlavePresupuestaria,
                'IdGestion' => $idGestion,
            ]);
        }

        $modelo->Techo = $form->techo;
        $modelo->CodigoEstado = Estado::ESTADO_VIGENTE;
        $modelo->CodigoUsuario = Yii::$app->user->identity->CodigoUsuario;
        $this->guardarModelo($modelo);

        return ResponseHelper::success([
            'IdAsignacion' => $modelo->IdAsignacion,
            'Techo' => (int)$modelo->Techo,
            'TechoEstado' => $modelo->CodigoEstado,
        ], 'Techo asignado correctamente.');
    }

    public function eliminar(
        string $idAsignacion,
        string $idUnidadEjecutora,
        string $idGestion,
        string $idEstadoPoa
    ): array {
        $modelo = TechoUnidad::find()->alias('TU')
            ->innerJoin(
                ['LP' => LlavePresupuestaria::tableName()],
                'LP.IdLlavePresupuestaria = TU.IdLlavePresupuestaria'
            )
            ->where([
                'TU.IdAsignacion' => $idAsignacion,
                'TU.IdGestion' => $idGestion,
                'LP.IdUnidadEjecutora' => $idUnidadEjecutora,
            ])
            ->andWhere(['<>', 'TU.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->one();

        if ($modelo === null) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],
                'No se encontró el techo asignado.',
                404
            );
        }

        $montoUsado = PresupuestoConsumoDao::totalPorLlave(
            $modelo->IdLlavePresupuestaria,
            $idGestion,
            $idEstadoPoa
        );
        if ($montoUsado > 0) {
            throw new ValidationException(
                Yii::$app->params['ERROR_VALIDACION_MODELO'],
                "No puede eliminar el techo: existen {$montoUsado} en ítems programados.",
                422
            );
        }

        $modelo->CodigoEstado = Estado::ESTADO_ELIMINADO;
        $modelo->CodigoUsuario = Yii::$app->user->identity->CodigoUsuario;
        $this->guardarModelo($modelo);
        return ResponseHelper::success(null, 'Techo eliminado correctamente.');
    }

    private function guardarModelo(TechoUnidad $modelo): void
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
