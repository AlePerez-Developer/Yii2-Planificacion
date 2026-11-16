<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\models\ProgramacionIndicadorGestion;
use app\modules\Planificacion\models\ProgramacionIndicadorMetaAnualGlobal;
use app\modules\Planificacion\models\ProgramacionIndicadorDa;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\models\IndicadorEstrategico;
use app\modules\Planificacion\models\LlavePresupuestaria;
use app\modules\Planificacion\models\UnidadEjecutora;
use app\modules\Planificacion\dao\ProgramacionAnualDao;
use app\modules\Planificacion\models\PeiGestion;
use yii\db\Expression;
use yii\db\StaleObjectException;
use common\models\Estado;
use yii\db\Exception;
use Throwable;
use Yii;

class IndicadorEstrategicoProgramacionAnualService
{
    private LlavePresupuestariaService $serviceLlavePresupuestaria;

    public function __construct(LlavePresupuestariaService $serviceLlavePresupuestaria)
    {
        $this->serviceLlavePresupuestaria = $serviceLlavePresupuestaria;
    }

    public function listarTodo(string $idGestion): array
    {
        $data = ProgramacionIndicadorGestion::listAllbyGestion()
            ->andWhere(['IdGestion' => $idGestion])
            ->asArray()
            ->all();
        return ResponseHelper::success($data, 'Listado de PEIs obtenido.');
    }

    public function listarUno(string $id): ?ProgramacionIndicadorGestion
    {
        return ProgramacionIndicadorGestion::listOne($id);
    }

    /**
     * Indicadores del objetivo con meta global (MetaAnualGlobal) y meta programada en llaves.
     */
    public function listarIndicadoresbyObjConProgramacion(string $id): array
    {
        $idPei = Yii::$app->contexto->getPei();

        $data = IndicadorEstrategico::listAll()
            ->addSelect([
                'MetaProgramada' => new Expression('ISNULL(SUM(Ip.MetaProgramada), 0)'),
            ])
            ->joinWith('programacionesIndicadoresGestiones Ip')
            ->andWhere(['I.IdObjEstrategico' => $id])
            ->orderBy(['Codigo' => SORT_ASC])
            ->asArray()
            ->all();

        $metasGlobales = ProgramacionIndicadorMetaAnualGlobal::find()
            ->alias('mg')
            ->select([
                'mg.IdIndicador',
                'MetaGlobal' => new Expression('ISNULL(SUM(mg.Meta), 0)'),
            ])
            ->innerJoin(['g' => PeiGestion::tableName()], 'g.IdGestion = mg.IdGestion')
            ->where(['g.IdPei' => $idPei])
            ->andWhere(['<>', 'g.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->groupBy(['mg.IdIndicador'])
            ->asArray()
            ->all();

        $mapGlobal = [];
        foreach ($metasGlobales as $item) {
            $mapGlobal[$item['IdIndicador']] = (float)$item['MetaGlobal'];
        }

        foreach ($data as &$row) {
            $row['MetaGlobal'] = $mapGlobal[$row['IdIndicador']] ?? 0.0;
            // Compatibilidad con UI previa que leía Meta
            $row['Meta'] = $row['MetaGlobal'];
            $row['MetaProgramada'] = (float)($row['MetaProgramada'] ?? 0);
        }
        unset($row);

        return ResponseHelper::success($data, 'Listado de Indicadores Estrategicos por Objetivo obtenido.');
    }

    public function listarGestionesbyPei(string $id, string $idIndicadorEstrategico): array
    {
        $data = PeiGestion::listAll()
            ->addSelect([
                'MetaProgramada' => new Expression('ISNULL(SUM(Ip.MetaProgramada), 0)'),
                'MetaGlobal' => new Expression('ISNULL(MAX(Mg.Meta), 0)'),
            ])
            ->joinWith([
                'gestionProgramacion Ip' => function ($query) use ($idIndicadorEstrategico) {
                    $query->andOnCondition([
                        'Ip.IdIndicadorEstrategico' => $idIndicadorEstrategico,
                    ]);
                },
            ])
            ->leftJoin(
                ['Mg' => ProgramacionIndicadorMetaAnualGlobal::tableName()],
                'Mg.IdGestion = G.IdGestion AND Mg.IdIndicador = :idIndicadorMeta',
                [':idIndicadorMeta' => $idIndicadorEstrategico]
            )
            ->andWhere(['G.IdPei' => $id])
            ->orderBy(['G.Gestion' => SORT_ASC])
            ->asArray()
            ->all();

        return ResponseHelper::success($data, 'Listado de gestiones obtenido.');
    }

    /**
     * @throws ValidationException
     */
    public function listarProgramacionbyGestion(int $codigoIndicador, int $gestion, string $pei): array
    {
        $idIndicador = $this->obtenerIdIndicador($codigoIndicador, $pei);
        $idGestion = $this->obtenerIdGestion($gestion, $pei);

        $data = ProgramacionIndicadorGestion::listAllbyGestion()
            ->andWhere(['I.IdIndicador' => $idIndicador])
            ->andWhere(['P.IdGestion' => $idGestion])
            ->asArray()
            ->all();

        return ResponseHelper::success($data, 'Listado de programaciones obtenido.');
    }

    /**
     * @throws ValidationException
     */
    public function listarLlavesPresupuestarias(int $codigoIndicador, int $gestion, string $pei): array
    {
        $idIndicador = $this->obtenerIdIndicador($codigoIndicador, $pei);
        $idGestion = $this->obtenerIdGestion($gestion, $pei);

        $tieneDas = ProgramacionIndicadorDa::find()
            ->where(['IdIndicador' => $idIndicador])
            ->exists();

        if (!$tieneDas) {
            return ResponseHelper::success([], 'El indicador no tiene DAs asignadas.');
        }

        $data = $this->serviceLlavePresupuestaria->listAllbyProgramacion($idIndicador, $idGestion);
        return ResponseHelper::success($data, 'Listado de llaves presupuestarias obtenido.');
    }

    /**
     * @throws Exception
     * @throws StaleObjectException
     * @throws ValidationException
     * @throws Throwable
     */
    public function cambiarEstado($idLlavePresupuestaria, $codigoIndicador, $gestion, $pei): array
    {
        $idIndicadorEstrategico = $this->obtenerIdIndicador($codigoIndicador, $pei);
        $idGestion = $this->obtenerIdGestion($gestion, $pei);
        $data = 0;

        $modelo = $this->obtenerModelo(
            $idIndicadorEstrategico,
            $idGestion,
            $idLlavePresupuestaria
        );

        if ($modelo !== null) {
            $modelo->delete();
        } else {
            $this->validarLlaveAsignable($idIndicadorEstrategico, $idLlavePresupuestaria);

            $modelo = new ProgramacionIndicadorGestion([
                'IdIndicadorEstrategico' => $idIndicadorEstrategico,
                'IdGestion' => $idGestion,
                'IdLlavePresupuestaria' => $idLlavePresupuestaria,
                'MetaProgramada' => 0,
                'CodigoUsuario' => Yii::$app->user->identity->CodigoUsuario ?? null,
            ]);
            $data = 1;
            if (!$modelo->save()) {
                throw new ValidationException(
                    Yii::$app->params['ERROR_EJECUCION_SQL'],
                    $modelo->getErrors(),
                    500
                );
            }
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $data,
        ];
    }

    /**
     * @throws StaleObjectException
     * @throws Throwable
     * @throws ValidationException
     */
    public function quitarProgramacion(string $idProgramacion): array
    {
        $modelo = ProgramacionIndicadorGestion::findOne($idProgramacion);

        if (!$modelo) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],
                'Programación no encontrada.',
                404
            );
        }

        if (ProgramacionAnualDao::enUso($modelo)) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_EN_USO'],
                'La programacion ya se encuentra desglosada en trimestres',
                500
            );
        }

        $modelo->delete();

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => '',
        ];
    }

    /**
     * @throws Exception
     * @throws ValidationException
     */
    public function guardarMeta(string $idProgramacion, int $meta): array
    {
        $modelo = $this->obtenerModeloValidado($idProgramacion);

        $metaProgramada = filter_var($meta, FILTER_VALIDATE_INT);
        if ($metaProgramada === false || $metaProgramada < 0) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'Meta programada invalida.', 400);
        }

        $this->validarMetaContraGlobal($modelo, $metaProgramada);

        $modelo->MetaProgramada = $metaProgramada;

        return $this->validarProcesarModelo($modelo);
    }

    /**
     * @throws ValidationException
     */
    public function calcularMeta(string $codigo, string $pei): array
    {
        $idIndicadorEstrategico = $this->obtenerIdIndicador((int)$codigo, $pei);
        $total = ProgramacionIndicadorGestion::find()
            ->where([
                'IdIndicadorEstrategico' => $idIndicadorEstrategico,
            ])
            ->sum('MetaProgramada') ?? 0;

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $total,
        ];
    }

    /**
     * @throws ValidationException
     */
    public function calcularMetaGestion(string $idIndicadorEstrategico, int $gestion, string $pei): array
    {
        $idGestion = $this->obtenerIdGestion($gestion, $pei);
        $total = ProgramacionIndicadorGestion::find()
            ->where([
                'IdIndicadorEstrategico' => $idIndicadorEstrategico,
                'IdGestion' => $idGestion,
            ])
            ->sum('MetaProgramada') ?? 0;

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $total,
        ];
    }

    private function obtenerModelo(
        string $idIndicadorEstrategico,
        string $idGestion,
        string $idLlavePresupuestaria
    ): ?ProgramacionIndicadorGestion {
        return ProgramacionIndicadorGestion::findOne([
            'IdIndicadorEstrategico' => $idIndicadorEstrategico,
            'IdGestion' => $idGestion,
            'IdLlavePresupuestaria' => $idLlavePresupuestaria,
        ]);
    }

    /**
     * @throws ValidationException
     */
    private function obtenerModeloValidado(string $id): ProgramacionIndicadorGestion
    {
        $model = $this->listarUno($id);
        if (!$model) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],
                'No se encontro el registro buscado',
                404
            );
        }
        return $model;
    }

    /**
     * @throws Exception
     * @throws ValidationException
     */
    public function validarProcesarModelo(ProgramacionIndicadorGestion $model): array
    {
        if (!$model->validate()) {
            throw new ValidationException(
                Yii::$app->params['ERROR_VALIDACION_MODELO'],
                $model->getErrors(),
                500
            );
        }

        if (!$model->save(false)) {
            Yii::error(
                "Error al guardar el cambio de estado de la programacion  $model->IdProgramacionIndicadorGestion",
                __METHOD__
            );
            throw new ValidationException(
                Yii::$app->params['ERROR_EJECUCION_SQL'],
                $model->getErrors(),
                500
            );
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => '',
        ];
    }

    /**
     * @throws ValidationException
     */
    private function validarLlaveAsignable(string $idIndicador, string $idLlavePresupuestaria): void
    {
        $llave = LlavePresupuestaria::find()
            ->alias('lp')
            ->innerJoin(
                ['Un' => UnidadEjecutora::tableName()],
                'Un.IdUnidadEjecutora = lp.IdUnidadEjecutora'
            )
            ->innerJoin(
                ['pida' => ProgramacionIndicadorDa::tableName()],
                'pida.IdDa = Un.IdDa AND pida.IdIndicador = :idIndicador',
                [':idIndicador' => $idIndicador]
            )
            ->where(['lp.IdLlavePresupuestaria' => $idLlavePresupuestaria])
            ->andWhere(['!=', 'lp.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->one();

        if (!$llave) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'La llave no pertenece a una DA asignada al indicador estratégico.',
                400
            );
        }
    }

    /**
     * Valida que la meta de la llave no exceda MetaAnualGlobal de la gestión
     * ni la meta global total del indicador.
     *
     * @throws ValidationException
     */
    private function validarMetaContraGlobal(ProgramacionIndicadorGestion $modelo, int $nuevaMeta): void
    {
        $metaGlobalGestion = ProgramacionIndicadorMetaAnualGlobal::find()
            ->where([
                'IdIndicador' => $modelo->IdIndicadorEstrategico,
                'IdGestion' => $modelo->IdGestion,
            ])
            ->one();

        $topeGestion = $metaGlobalGestion ? (float)$metaGlobalGestion->Meta : 0.0;

        $sumaGestion = (float)(ProgramacionIndicadorGestion::find()
            ->where([
                'IdIndicadorEstrategico' => $modelo->IdIndicadorEstrategico,
                'IdGestion' => $modelo->IdGestion,
            ])
            ->andWhere(['<>', 'IdProgramacionIndicadorGestion', $modelo->IdProgramacionIndicadorGestion])
            ->sum('MetaProgramada') ?? 0);

        $totalGestion = $sumaGestion + $nuevaMeta;

        if ($totalGestion > $topeGestion) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                "La meta programada ($totalGestion) excede la meta global de la gestión ($topeGestion).",
                400
            );
        }

        $idPei = Yii::$app->contexto->getPei();
        $topeGlobal = (float)(ProgramacionIndicadorMetaAnualGlobal::find()
            ->alias('mg')
            ->innerJoin(['g' => PeiGestion::tableName()], 'g.IdGestion = mg.IdGestion')
            ->where(['mg.IdIndicador' => $modelo->IdIndicadorEstrategico])
            ->andWhere(['g.IdPei' => $idPei])
            ->andWhere(['<>', 'g.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->sum('mg.Meta') ?? 0);

        $sumaTotal = (float)(ProgramacionIndicadorGestion::find()
            ->where(['IdIndicadorEstrategico' => $modelo->IdIndicadorEstrategico])
            ->andWhere(['<>', 'IdProgramacionIndicadorGestion', $modelo->IdProgramacionIndicadorGestion])
            ->sum('MetaProgramada') ?? 0);

        $totalIndicador = $sumaTotal + $nuevaMeta;
        if ($totalIndicador > $topeGlobal) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                "La meta programada ($totalIndicador) excede la meta global del indicador ($topeGlobal).",
                400
            );
        }
    }

    /**
     * @throws ValidationException
     */
    private function obtenerIdIndicador(int $codigo, string $pei): string
    {
        /** @var IndicadorEstrategico|null $modelo */
        $modelo = IndicadorEstrategico::listAllSimple()
            ->addSelect(['O.IdObjEstrategico'])
            ->joinWith('objetivosEstrategicos O', true, 'INNER JOIN')
            ->andWhere(['!=', 'O.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['E.Codigo' => $codigo])
            ->andWhere(['O.IdPei' => $pei])
            ->addGroupBy(['O.IdObjEstrategico'])
            ->one();

        if (!$modelo) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'El codigo de Indicador estrategico no es valido en el contexto',
                500
            );
        }

        return $modelo->IdIndicador;
    }

    /**
     * @throws ValidationException
     */
    private function obtenerIdGestion(int $gestion, string $pei): string
    {
        /** @var PeiGestion|null $modelo */
        $modelo = PeiGestion::listAll()
            ->andWhere(['G.Gestion' => $gestion])
            ->andWhere(['G.IdPei' => $pei])
            ->one();

        if (!$modelo) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'Gestion invalida para el PEI actual.',
                400
            );
        }

        return $modelo->IdGestion;
    }
}
