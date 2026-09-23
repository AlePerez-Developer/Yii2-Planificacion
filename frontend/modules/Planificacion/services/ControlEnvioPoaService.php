<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\dao\IngresoDao;
use app\modules\Planificacion\dao\PresupuestoConsumoDao;
use app\modules\Planificacion\dao\TechoUnidadDao;
use app\modules\Planificacion\models\ControlEnvioPoa;
use app\modules\Planificacion\models\CronogramaPoa;
use app\modules\Planificacion\models\Indicador;
use app\modules\Planificacion\models\LlavePresupuestaria;
use app\modules\Planificacion\models\Operacion;
use app\modules\Planificacion\models\ProgramacionIndicadorGestion;
use app\modules\Planificacion\models\ProgramacionIndicadorPoaGestion;
use app\modules\Planificacion\models\UnidadEjecutora;
use common\models\Estado;
use Yii;

class ControlEnvioPoaService
{
    public function listarTodo(): array
    {
        $data = ControlEnvioPoa::listAll()->asArray()->all();
        return ResponseHelper::success($data, 'Listado de envíos obtenido.');
    }

    public function validar(string $idUnidad, string $idGestion, string $idEstadoPoa): array
    {
        $restricciones = [];
        $cronograma = CronogramaPoa::vigenteHoy();
        if ($cronograma === null) {
            $restricciones[] = 'No existe un cronograma POA vigente para la fecha actual.';
            return $restricciones;
        }

        if (ControlEnvioPoa::envioActivo($idUnidad, $cronograma->IdCronograma) !== null) {
            $restricciones[] = 'La unidad ya tiene un envío vigente en el cronograma actual.';
        }

        $unidad = UnidadEjecutora::find()
            ->where(['IdUnidadEjecutora' => $idUnidad])
            ->with('das')
            ->one();
        if ($unidad === null) {
            $restricciones[] = 'No se encontró la unidad ejecutora activa.';
            return $restricciones;
        }

        $codigoDa = (string)($unidad->das->Da ?? '');
        $esPadre = $unidad->esUnidadPadre();
        $usaIngresos = $unidad->usaIngresosGlobales();

        if ($esPadre) {
            foreach (UnidadEjecutora::hijasVigentes($unidad->IdDa) as $hija) {
                if (ControlEnvioPoa::envioActivo($hija->IdUnidadEjecutora, $cronograma->IdCronograma) === null) {
                    $restricciones[] = sprintf(
                        'La unidad %s-%s aún no envió su POA.',
                        $codigoDa,
                        $hija->Ue
                    );
                }
            }
        }

        if ($usaIngresos) {
            if ($esPadre) {
                $programadoDa = PresupuestoConsumoDao::totalPorDa($unidad->IdDa, $idGestion, $idEstadoPoa);
                $ingresosDa = IngresoDao::totalPorDa($unidad->IdDa, $idGestion, $idEstadoPoa);
                if ($ingresosDa <= 0) {
                    $restricciones[] = sprintf('La DA %s no tiene ingresos asignados.', $codigoDa);
                } elseif (abs($programadoDa - $ingresosDa) > 0.01) {
                    $restricciones[] = sprintf(
                        'Los ítems de todas las unidades de la DA %s (%s) deben igualar los ingresos de esa DA (%s).',
                        $codigoDa,
                        $this->monto($programadoDa),
                        $this->monto($ingresosDa)
                    );
                }
            }
        } else {
            $programado = PresupuestoConsumoDao::totalPorUnidad($idUnidad, $idGestion, $idEstadoPoa);
            $techos = (float)TechoUnidadDao::total($idUnidad, $idGestion);
            if ($techos <= 0) {
                $restricciones[] = 'La unidad no tiene techo presupuestario asignado.';
            } elseif (abs($programado - $techos) > 0.01) {
                $restricciones[] = sprintf(
                    'El presupuesto programado de la unidad (%s) debe ser igual al techo asignado (%s).',
                    $this->monto($programado),
                    $this->monto($techos)
                );
            }
        }

        $operaciones = Operacion::find()
            ->where([
                'IdUnidadEjecutora' => $idUnidad,
                'IdGestion' => $idGestion,
                'IdEstadoPoa' => $idEstadoPoa,
                'CodigoEstado' => Estado::ESTADO_VIGENTE,
            ])
            ->all();

        foreach ($operaciones as $operacion) {
            $total = (int)$operacion->PrimerTrimestre
                + (int)$operacion->SegundoTrimestre
                + (int)$operacion->TercerTrimestre
                + (int)$operacion->CuartoTrimestre;
            if ($total !== 100) {
                $restricciones[] = sprintf(
                    'La operación %s no suma 100 en su programación trimestral (total: %d).',
                    $operacion->Codigo ?: $operacion->IdOperacion,
                    $total
                );
            }
        }

        $porId = [];
        foreach (array_merge(
            $this->indicadoresPoaProgramados($idUnidad, $idGestion),
            $this->indicadoresEstrategicosProgramados($idUnidad, $idGestion)
        ) as $indicador) {
            $porId[$indicador['id']] = $indicador;
        }
        $asignados = array_values($porId);
        $usados = Operacion::find()
            ->select('IdIndicador')
            ->where([
                'IdUnidadEjecutora' => $idUnidad,
                'IdGestion' => $idGestion,
                'IdEstadoPoa' => $idEstadoPoa,
            ])
            ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->column();
        $usados = array_flip($usados);

        foreach ($asignados as $indicador) {
            if (!isset($usados[$indicador['id']])) {
                $restricciones[] = sprintf(
                    'El indicador %s (%s) está programado para la unidad y no está asignado a ninguna operación.',
                    $indicador['descripcion'],
                    $indicador['tipo']
                );
            }
        }

        return $restricciones;
    }

    public function enviar(string $idUnidad, string $idGestion, string $idEstadoPoa): array
    {
        $restricciones = $this->validar($idUnidad, $idGestion, $idEstadoPoa);
        if ($restricciones !== []) {
            return [
                'ok' => false,
                'restricciones' => $restricciones,
            ];
        }

        $cronograma = CronogramaPoa::vigenteHoy();
        $modelo = new ControlEnvioPoa([
            'IdCronograma' => $cronograma->IdCronograma,
            'IdUnidadEjecutora' => $idUnidad,
            'Estado' => ControlEnvioPoa::ESTADO_ENVIADO,
            'CodigoEstado' => Estado::ESTADO_VIGENTE,
            'CodigoUsuario' => Yii::$app->user->identity->CodigoUsuario,
        ]);
        $this->guardarModelo($modelo);

        return [
            'ok' => true,
            'restricciones' => [],
            'modelo' => $modelo,
        ];
    }

    public function eliminar(string $id): array
    {
        $modelo = ControlEnvioPoa::listOne($id);
        if ($modelo === null) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],
                'No se encontró el envío solicitado.',
                404
            );
        }
        $modelo->eliminar();
        $this->guardarModelo($modelo);

        return ResponseHelper::success($modelo, 'Envío eliminado. La unidad puede volver a editar el POA.');
    }

    private function indicadoresPoaProgramados(string $idUnidad, string $idGestion): array
    {
        $filas = ProgramacionIndicadorPoaGestion::find()->alias('PG')
            ->select([
                'id' => 'PG.IdIndicadorPoa',
                'descripcion' => 'I.Descripcion',
            ])
            ->innerJoin(['LP' => LlavePresupuestaria::tableName()], 'LP.IdLlavePresupuestaria = PG.IdLlavePresupuestaria')
            ->leftJoin(['I' => Indicador::tableName()], 'I.IdIndicador = PG.IdIndicadorPoa')
            ->where([
                'LP.IdUnidadEjecutora' => $idUnidad,
                'PG.IdGestion' => $idGestion,
                'LP.CodigoEstado' => Estado::ESTADO_VIGENTE,
            ])
            ->asArray()
            ->all();

        return array_map(static fn(array $fila): array => [
            'id' => $fila['id'],
            'descripcion' => $fila['descripcion'] ?: $fila['id'],
            'tipo' => 'POA',
        ], $filas);
    }

    private function indicadoresEstrategicosProgramados(string $idUnidad, string $idGestion): array
    {
        $filas = ProgramacionIndicadorGestion::find()->alias('PG')
            ->select([
                'id' => 'PG.IdIndicadorEstrategico',
                'descripcion' => 'I.Descripcion',
            ])
            ->innerJoin(['LP' => LlavePresupuestaria::tableName()], 'LP.IdLlavePresupuestaria = PG.IdLlavePresupuestaria')
            ->leftJoin(['I' => Indicador::tableName()], 'I.IdIndicador = PG.IdIndicadorEstrategico')
            ->where([
                'LP.IdUnidadEjecutora' => $idUnidad,
                'PG.IdGestion' => $idGestion,
                'LP.CodigoEstado' => Estado::ESTADO_VIGENTE,
            ])
            ->asArray()
            ->all();

        return array_map(static fn(array $fila): array => [
            'id' => $fila['id'],
            'descripcion' => $fila['descripcion'] ?: $fila['id'],
            'tipo' => 'Estratégico',
        ], $filas);
    }

    private function monto(float $valor): string
    {
        return number_format($valor, 2, ',', '.');
    }

    private function guardarModelo(ControlEnvioPoa $modelo): void
    {
        if (!$modelo->validate()) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                $modelo->getErrors(),
                400
            );
        }
        if (!$modelo->save(false)) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'No se pudo guardar el envío.',
                500
            );
        }
    }
}
