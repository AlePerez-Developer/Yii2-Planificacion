<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\formModels\ProgramacionIndicadorDaForm;
use app\modules\Planificacion\models\Da;
use app\modules\Planificacion\models\IndicadorEstrategico;
use app\modules\Planificacion\models\LlavePresupuestaria;
use app\modules\Planificacion\models\ProgramacionIndicadorDa;
use app\modules\Planificacion\models\ProgramacionIndicadorGestion;
use app\modules\Planificacion\models\ProgramacionIndicadorTrimestre;
use app\modules\Planificacion\models\UnidadEjecutora;
use common\models\Estado;
use Throwable;
use Yii;
use yii\db\Exception;
use yii\db\Expression;
use yii\db\StaleObjectException;

class ProgramacionIndicadorDaService
{
    /**
     * Lista indicadores estratégicos con cantidad de DAs asignadas.
     */
    public function listarIndicadores(): array
    {
        $data = IndicadorEstrategico::listAll()
            ->addSelect([
                'DasAsignadas' => new Expression(
                    '(SELECT COUNT(*) FROM ' . ProgramacionIndicadorDa::tableName()
                    . ' pid WHERE pid.IdIndicador = e.IdIndicador)'
                ),
            ])
            ->orderBy(['I.Codigo' => SORT_ASC])
            ->asArray()
            ->all();

        return ResponseHelper::success($data, 'Listado de indicadores estratégicos obtenido.');
    }

    /**
     * Lista DAs vigentes y marca las asignadas al indicador.
     *
     * @throws ValidationException
     */
    public function listarDasPorIndicador(string $idIndicador): array
    {
        $this->obtenerIndicadorValidado($idIndicador);

        $das = Da::find()
            ->select(['IdDa', 'Da', 'Descripcion', 'CodigoEstado'])
            ->where(['!=', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->orderBy(['Da' => SORT_ASC])
            ->asArray()
            ->all();

        $asignadas = ProgramacionIndicadorDa::find()
            ->select(['IdDa'])
            ->where(['IdIndicador' => $idIndicador])
            ->column();
        $asignadasSet = array_flip($asignadas);

        foreach ($das as &$da) {
            $da['Asignado'] = isset($asignadasSet[$da['IdDa']]) ? 1 : 0;
            $da['Etiqueta'] = trim(($da['Da'] ?? '') . ' - ' . ($da['Descripcion'] ?? ''));
        }
        unset($da);

        return ResponseHelper::success($das, 'Listado de DAs para el indicador obtenido.');
    }

    /**
     * Inserta la relación indicador–DA.
     *
     * @throws Exception
     * @throws ValidationException
     */
    public function asignar(ProgramacionIndicadorDaForm $form): array
    {
        $this->obtenerIndicadorValidado($form->idIndicador);
        $this->obtenerDaValidado($form->idDa);

        $existente = ProgramacionIndicadorDa::listOne($form->idIndicador, $form->idDa);
        if ($existente) {
            return [
                'message' => Yii::$app->params['PROCESO_CORRECTO'],
                'data' => 1,
            ];
        }

        $modelo = new ProgramacionIndicadorDa([
            'IdIndicador' => $form->idIndicador,
            'IdDa' => $form->idDa,
        ]);

        if (!$modelo->validate()) {
            throw new ValidationException(
                Yii::$app->params['ERROR_VALIDACION_MODELO'],
                $modelo->getErrors(),
                500
            );
        }

        if (!$modelo->save(false)) {
            throw new ValidationException(
                Yii::$app->params['ERROR_EJECUCION_SQL'],
                $modelo->getErrors(),
                500
            );
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => 1,
        ];
    }

    /**
     * Elimina la relación indicador–DA.
     *
     * @throws StaleObjectException
     * @throws Throwable
     * @throws ValidationException
     */
    public function eliminar(ProgramacionIndicadorDaForm $form): array
    {
        $this->obtenerIndicadorValidado($form->idIndicador);
        $this->obtenerDaValidado($form->idDa);

        $modelo = ProgramacionIndicadorDa::listOne($form->idIndicador, $form->idDa);
        if ($modelo) {
            $this->validarSinProgramacion($form->idIndicador, $form->idDa);
            $modelo->delete();
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => 0,
        ];
    }

    /**
     * Impide quitar la DA si aún tiene programación anual o trimestral
     * en llaves de esa dirección administrativa.
     *
     * @throws ValidationException
     */
    private function validarSinProgramacion(string $idIndicador, string $idDa): void
    {
        $tieneAnual = ProgramacionIndicadorGestion::find()
            ->alias('p')
            ->innerJoin(
                ['lp' => LlavePresupuestaria::tableName()],
                'lp.IdLlavePresupuestaria = p.IdLlavePresupuestaria'
            )
            ->innerJoin(
                ['ue' => UnidadEjecutora::tableName()],
                'ue.IdUnidadEjecutora = lp.IdUnidadEjecutora'
            )
            ->where([
                'p.IdIndicadorEstrategico' => $idIndicador,
                'ue.IdDa' => $idDa,
            ])
            ->exists();

        $tieneTrimestral = ProgramacionIndicadorTrimestre::find()
            ->alias('t')
            ->innerJoin(
                ['p' => ProgramacionIndicadorGestion::tableName()],
                'p.IdProgramacionIndicadorGestion = t.IdProgramacionIndicadorGestion'
            )
            ->innerJoin(
                ['lp' => LlavePresupuestaria::tableName()],
                'lp.IdLlavePresupuestaria = p.IdLlavePresupuestaria'
            )
            ->innerJoin(
                ['ue' => UnidadEjecutora::tableName()],
                'ue.IdUnidadEjecutora = lp.IdUnidadEjecutora'
            )
            ->where([
                'p.IdIndicadorEstrategico' => $idIndicador,
                'ue.IdDa' => $idDa,
            ])
            ->exists();

        if ($tieneAnual || $tieneTrimestral) {
            $detalle = [];
            if ($tieneAnual) {
                $detalle[] = 'programación anual';
            }
            if ($tieneTrimestral) {
                $detalle[] = 'programación trimestral';
            }

            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_EN_USO'],
                'No se puede quitar la asignación de la DA porque existe '
                . implode(' y ', $detalle)
                . ' en sus llaves. Primero elimine o reinicie esas programaciones.',
                400
            );
        }
    }

    /**
     * @throws ValidationException
     */
    private function obtenerIndicadorValidado(string $idIndicador): IndicadorEstrategico
    {
        $modelo = IndicadorEstrategico::listOne($idIndicador);
        if (!$modelo) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],
                'Indicador estratégico no encontrado.',
                404
            );
        }
        return $modelo;
    }

    /**
     * @throws ValidationException
     */
    private function obtenerDaValidado(string $idDa): Da
    {
        $modelo = Da::listOne($idDa);
        if (!$modelo) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],
                'Dirección administrativa no encontrada.',
                404
            );
        }
        return $modelo;
    }
}
