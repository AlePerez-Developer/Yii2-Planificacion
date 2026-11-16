<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\formModels\ProgramacionIndicadorMetaAnualGlobalForm;
use app\modules\Planificacion\models\IndicadorEstrategico;
use app\modules\Planificacion\models\PeiGestion;
use app\modules\Planificacion\models\ProgramacionIndicadorMetaAnualGlobal;
use common\models\Estado;
use Yii;
use yii\db\Exception;
use yii\db\Expression;

class ProgramacionIndicadorMetaAnualGlobalService
{
    /**
     * Lista indicadores estratégicos con meta total y meta programada global.
     */
    public function listarIndicadores(): array
    {
        $idPei = Yii::$app->contexto->getPei();

        $data = IndicadorEstrategico::listAll()
            ->andWhere(['I.IdPei' => $idPei])
            ->orderBy(['I.Codigo' => SORT_ASC])
            ->asArray()
            ->all();

        $sumas = ProgramacionIndicadorMetaAnualGlobal::find()
            ->alias('pg')
            ->select([
                'pg.IdIndicador',
                'MetaProgramada' => new Expression('ISNULL(SUM(pg.Meta), 0)'),
            ])
            ->innerJoin(['g' => PeiGestion::tableName()], 'g.IdGestion = pg.IdGestion')
            ->where(['g.IdPei' => $idPei])
            ->andWhere(['<>', 'g.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->groupBy(['pg.IdIndicador'])
            ->asArray()
            ->all();

        $map = [];
        foreach ($sumas as $suma) {
            $map[$suma['IdIndicador']] = (float)$suma['MetaProgramada'];
        }

        foreach ($data as &$row) {
            $row['MetaProgramada'] = $map[$row['IdIndicador']] ?? 0.0;
        }
        unset($row);

        return ResponseHelper::success($data, 'Listado de indicadores estratégicos obtenido.');
    }

    /**
     * Lista gestiones del PEI activo con la meta asignada al indicador.
     *
     * @throws ValidationException
     */
    public function listarGestionesPorIndicador(string $idIndicador): array
    {
        $indicador = $this->obtenerIndicadorValidado($idIndicador);
        $idPei = Yii::$app->contexto->getPei();
        if ($idPei === '') {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'No se ha definido el PEI activo.',
                400
            );
        }

        $gestiones = PeiGestion::listAll()
            ->andWhere(['G.IdPei' => $idPei])
            ->orderBy(['G.Gestion' => SORT_ASC])
            ->asArray()
            ->all();

        $programadas = ProgramacionIndicadorMetaAnualGlobal::find()
            ->select(['IdGestion', 'IdProgramcion', 'Meta'])
            ->where(['IdIndicador' => $idIndicador])
            ->asArray()
            ->all();

        $porGestion = [];
        foreach ($programadas as $item) {
            $porGestion[$item['IdGestion']] = $item;
        }

        $metaIndicador = (float)($indicador->indicador->Meta ?? 0);
        $totalProgramado = 0.0;

        foreach ($gestiones as &$gestion) {
            $prog = $porGestion[$gestion['IdGestion']] ?? null;
            $meta = $prog ? (float)$prog['Meta'] : 0.0;
            $totalProgramado += $meta;
            $gestion['Meta'] = $meta;
            $gestion['IdProgramcion'] = $prog['IdProgramcion'] ?? null;
            $gestion['TieneProgramacion'] = $prog ? 1 : 0;
        }
        unset($gestion);

        return ResponseHelper::success([
            'metaIndicador' => $metaIndicador,
            'metaProgramada' => $totalProgramado,
            'pendiente' => max(0, $metaIndicador - $totalProgramado),
            'gestiones' => $gestiones,
        ], 'Listado de gestiones obtenido.');
    }

    /**
     * Guarda o actualiza la meta de una gestión para un indicador.
     *
     * @throws Exception
     * @throws ValidationException
     */
    public function guardarMeta(ProgramacionIndicadorMetaAnualGlobalForm $form): array
    {
        $indicador = $this->obtenerIndicadorValidado($form->idIndicador);
        $gestion = $this->obtenerGestionValidada($form->idGestion);

        $metaNueva = (float)$form->meta;
        if ($metaNueva < 0) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'La meta no puede ser negativa.',
                400
            );
        }

        $metaIndicador = (float)($indicador->indicador->Meta ?? 0);
        $sumaOtras = (float)ProgramacionIndicadorMetaAnualGlobal::find()
            ->where(['IdIndicador' => $form->idIndicador])
            ->andWhere(['<>', 'IdGestion', $form->idGestion])
            ->sum('Meta');

        if (($sumaOtras + $metaNueva) > $metaIndicador + 0.00001) {
            $disponible = max(0, $metaIndicador - $sumaOtras);
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'La meta programada supera la meta del indicador. Disponible: ' . $this->formatoMeta($disponible) . '.',
                400
            );
        }

        $modelo = ProgramacionIndicadorMetaAnualGlobal::listOne($form->idIndicador, $form->idGestion);
        if (!$modelo) {
            $modelo = new ProgramacionIndicadorMetaAnualGlobal([
                'IdIndicador' => $form->idIndicador,
                'IdGestion' => $gestion->IdGestion,
            ]);
        }

        $modelo->Meta = $metaNueva;

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

        $metaProgramada = (float)ProgramacionIndicadorMetaAnualGlobal::find()
            ->alias('pg')
            ->innerJoin(['g' => PeiGestion::tableName()], 'g.IdGestion = pg.IdGestion')
            ->where(['pg.IdIndicador' => $form->idIndicador])
            ->andWhere(['g.IdPei' => Yii::$app->contexto->getPei()])
            ->andWhere(['<>', 'g.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->sum('pg.Meta');

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => [
                'idProgramcion' => $modelo->IdProgramcion,
                'meta' => $metaNueva,
                'metaIndicador' => $metaIndicador,
                'metaProgramada' => $metaProgramada,
                'pendiente' => max(0, $metaIndicador - $metaProgramada),
                'completo' => $metaProgramada >= $metaIndicador && $metaIndicador > 0,
            ],
        ];
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
    private function obtenerGestionValidada(string $idGestion): PeiGestion
    {
        $modelo = PeiGestion::find()
            ->where(['IdGestion' => $idGestion])
            ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->one();

        if (!$modelo) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],
                'Gestión no encontrada.',
                404
            );
        }

        if ($modelo->IdPei !== Yii::$app->contexto->getPei()) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'La gestión no pertenece al PEI activo.',
                400
            );
        }

        return $modelo;
    }

    private function formatoMeta(float $valor): string
    {
        if (floor($valor) == $valor) {
            return (string)(int)$valor;
        }
        return rtrim(rtrim(number_format($valor, 2, '.', ''), '0'), '.');
    }
}
