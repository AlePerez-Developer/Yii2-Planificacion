<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\models\AccionEstrategica;
use app\modules\Planificacion\models\Da;
use app\modules\Planificacion\models\Indicador;
use app\modules\Planificacion\models\IndicadorEstrategico;
use app\modules\Planificacion\models\ObjetivoEstrategico;
use app\modules\Planificacion\models\PeiGestion;
use app\modules\Planificacion\models\ProgramacionIndicadorGestion;
use app\modules\Planificacion\models\ProgramacionIndicadorMetaAnualGlobal;
use app\modules\Planificacion\models\ProgramacionIndicadorTrimestre;
use app\modules\Planificacion\models\UnidadEjecutora;
use common\models\Estado;
use Yii;
use yii\db\Expression;

class IndicadorEstrategicoProgramacionTrimestralService
{
    public function obtenerDetalleObjetivo(string $idObjEstrategico): ?array
    {
        return ObjetivoEstrategico::listAll()
            ->andWhere(['O.IdObjEstrategico' => $idObjEstrategico])
            ->asArray()
            ->one();
    }

    /**
     * Lista los indicadores estratégicos asignados al objetivo.
     */
    public function listarIndicadores(string $idObjEstrategico, string $idGestion): array
    {
        $data = IndicadorEstrategico::find()
            ->alias('I')
            ->select([
                'I.IdIndicador as IdIndicadorEstrategico',
                'I.Codigo',
                'ii.Descripcion',
                'Meta' => new Expression('ISNULL(Mg.Meta, ii.Meta)'),
                'MetaGlobal' => new Expression('ISNULL(Mg.Meta, ii.Meta)'),
                'MetaProgramada' => new Expression('ISNULL(SUM(P.MetaProgramada), 0)'),
                'Ac.Codigo as AccionCodigo',
                'Ac.Descripcion as AccionDescripcion',
            ])
            ->innerJoin(
                ['ii' => Indicador::tableName()],
                'ii.IdIndicador = I.IdIndicador'
            )
            ->leftJoin(
                ['Ac' => AccionEstrategica::tableName()],
                'Ac.IdAccionEstrategica = I.IdAccionEstrategica'
            )
            ->leftJoin(
                ['Mg' => ProgramacionIndicadorMetaAnualGlobal::tableName()],
                'Mg.IdIndicador = I.IdIndicador AND Mg.IdGestion = :idGestionMeta',
                [':idGestionMeta' => $idGestion]
            )
            ->leftJoin(
                ['P' => ProgramacionIndicadorGestion::tableName()],
                'P.IdIndicadorEstrategico = I.IdIndicador AND P.IdGestion = :idGestion',
                [':idGestion' => $idGestion]
            )
            ->where(['I.IdObjEstrategico' => $idObjEstrategico])
            ->andWhere(['<>', 'I.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->groupBy([
                'I.IdIndicador',
                'I.Codigo',
                'ii.Descripcion',
                'ii.Meta',
                'Mg.Meta',
                'Ac.Codigo',
                'Ac.Descripcion',
            ])
            ->orderBy(['I.Codigo' => SORT_ASC])
            ->asArray()
            ->all();

        return ResponseHelper::success($data, 'Indicadores estratégicos asignados obtenidos.');
    }

    /**
     * Retorna la única gestión activa que se mostrará como tab.
     */
    public function obtenerGestionActiva(string $idGestion): array
    {
        $gestion = $this->obtenerGestionValidada($idGestion);

        return ResponseHelper::success([
            'IdGestion' => $gestion->IdGestion,
            'Gestion' => $gestion->Gestion,
        ], 'Gestión activa obtenida.');
    }

    /**
     * Lista las llaves programadas anualmente para un indicador y la gestión activa,
     * incorporando las metas trimestrales.
     */
    public function listarProgramacion(string $idIndicadorEstrategico, string $idGestion): array
    {
        $data = ProgramacionIndicadorGestion::find()
            ->alias('P')
            ->select([
                'P.IdProgramacionIndicadorGestion',
                'T.IdProgramacionTrimestral',
                'L.IdLlavePresupuestaria',
                'CodigoCompuesto' => 'L.Llave',
                'Descripcion' => new Expression("CONCAT(Da.Da, '-', Un.Ue, ' ', Un.Descripcion)"),
                'P.MetaProgramada',
                'MetaPrimerTrimestre' => new Expression('ISNULL(T.MetaPrimerTrimestre, 0)'),
                'MetaSegundoTrimestre' => new Expression('ISNULL(T.MetaSegundoTrimestre, 0)'),
                'MetaTercerTrimestre' => new Expression('ISNULL(T.MetaTercerTrimestre, 0)'),
                'MetaCuartoTrimestre' => new Expression('ISNULL(T.MetaCuartoTrimestre, 0)'),
                'TotalTrimestral' => new Expression(
                    'ISNULL(T.MetaPrimerTrimestre, 0) + ISNULL(T.MetaSegundoTrimestre, 0)'
                    . ' + ISNULL(T.MetaTercerTrimestre, 0) + ISNULL(T.MetaCuartoTrimestre, 0)'
                ),
            ])
            ->innerJoin(['L' => 'LlavesPresupuestarias'], 'L.IdLlavePresupuestaria = P.IdLlavePresupuestaria')
            ->innerJoin(
                ['Un' => UnidadEjecutora::tableName()],
                'Un.IdUnidadEjecutora = L.IdUnidadEjecutora'
            )
            ->innerJoin(
                ['Da' => Da::tableName()],
                'Da.IdDa = Un.IdDa'
            )
            ->leftJoin(
                ['T' => ProgramacionIndicadorTrimestre::tableName()],
                'T.IdProgramacionIndicadorGestion = P.IdProgramacionIndicadorGestion'
            )
            ->where([
                'P.IdIndicadorEstrategico' => $idIndicadorEstrategico,
                'P.IdGestion' => $idGestion,
            ])
            ->orderBy(['L.Llave' => SORT_ASC])
            ->asArray()
            ->all();

        foreach ($data as &$fila) {
            $fila['TieneProgramacionTrimestral'] = !empty($fila['IdProgramacionTrimestral']) ? 1 : 0;
            $fila['ProgramacionCompleta'] = (int)$fila['TotalTrimestral'] >= (int)$fila['MetaProgramada'] ? 1 : 0;
        }
        unset($fila);

        return ResponseHelper::success($data, 'Programación trimestral obtenida.');
    }

    /**
     * Elimina la programación trimestral asociada a una programación anual.
     *
     * @throws ValidationException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function eliminar(string $idProgramacionIndicadorGestion): array
    {
        $programacionAnual = ProgramacionIndicadorGestion::findOne($idProgramacionIndicadorGestion);
        if ($programacionAnual === null) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],
                'No se encontró la programación anual.',
                404
            );
        }

        $modelo = ProgramacionIndicadorTrimestre::findOne([
            'IdProgramacionIndicadorGestion' => $idProgramacionIndicadorGestion,
        ]);

        if ($modelo === null) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],
                'No existe programación trimestral para reiniciar.',
                404
            );
        }

        if (!$modelo->delete()) {
            throw new ValidationException(
                Yii::$app->params['ERROR_EJECUCION_SQL'],
                'No fue posible reiniciar la programación trimestral.',
                500
            );
        }

        return ResponseHelper::success([
            'MetaPrimerTrimestre' => 0,
            'MetaSegundoTrimestre' => 0,
            'MetaTercerTrimestre' => 0,
            'MetaCuartoTrimestre' => 0,
            'TotalTrimestral' => 0,
            'MetaProgramada' => (int)$programacionAnual->MetaProgramada,
            'ProgramacionCompleta' => 0,
            'TieneProgramacionTrimestral' => 0,
            'IdProgramacionTrimestral' => null,
        ], 'Programación trimestral reiniciada.');
    }

    /**
     * Crea o actualiza una meta trimestral.
     */
    public function guardarMeta(string $idProgramacionIndicadorGestio, int $trimestre, int $meta): array
    {
        if ($trimestre < 1 || $trimestre > 4) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'El trimestre enviado no es válido.',
                400
            );
        }

        if ($meta < 0) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'La meta trimestral no puede ser negativa.',
                400
            );
        }

        $programacionAnual = ProgramacionIndicadorGestion::findOne($idProgramacionIndicadorGestio);
        if ($programacionAnual === null) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],
                'No se encontró la programación anual.',
                404
            );
        }

        $modelo = ProgramacionIndicadorTrimestre::findOne([
            'IdProgramacionIndicadorGestion' => $idProgramacionIndicadorGestio,
        ]);

        if ($modelo === null) {
            $modelo = new ProgramacionIndicadorTrimestre([
                'IdProgramacionIndicadorGestion' => $idProgramacionIndicadorGestio,
                'MetaPrimerTrimestre' => 0,
                'MetaSegundoTrimestre' => 0,
                'MetaTercerTrimestre' => 0,
                'MetaCuartoTrimestre' => 0,
                'CodigoUsuario' => Yii::$app->user->identity->CodigoUsuario,
            ]);
        }

        $atributo = match ($trimestre) {
            1 => 'MetaPrimerTrimestre',
            2 => 'MetaSegundoTrimestre',
            3 => 'MetaTercerTrimestre',
            4 => 'MetaCuartoTrimestre',
        };

        $modelo->{$atributo} = $meta;

        $totalPropuesto = (int)$modelo->MetaPrimerTrimestre
            + (int)$modelo->MetaSegundoTrimestre
            + (int)$modelo->MetaTercerTrimestre
            + (int)$modelo->MetaCuartoTrimestre;

        if ($totalPropuesto > (int)$programacionAnual->MetaProgramada) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                "La suma trimestral ($totalPropuesto) excede la meta anual programada ({$programacionAnual->MetaProgramada}).",
                400
            );
        }

        if (!$modelo->validate()) {
            throw new ValidationException(
                Yii::$app->params['ERROR_VALIDACION_MODELO'],
                $modelo->getErrors(),
                400
            );
        }

        if (!$modelo->save(false)) {
            throw new ValidationException(
                Yii::$app->params['ERROR_EJECUCION_SQL'],
                'No fue posible guardar la programación trimestral.',
                500
            );
        }

        $total = (int)$modelo->MetaPrimerTrimestre
            + (int)$modelo->MetaSegundoTrimestre
            + (int)$modelo->MetaTercerTrimestre
            + (int)$modelo->MetaCuartoTrimestre;

        return ResponseHelper::success([
            'IdProgramacionTrimestral' => $modelo->IdProgramacionTrimestral,
            'MetaPrimerTrimestre' => (int)$modelo->MetaPrimerTrimestre,
            'MetaSegundoTrimestre' => (int)$modelo->MetaSegundoTrimestre,
            'MetaTercerTrimestre' => (int)$modelo->MetaTercerTrimestre,
            'MetaCuartoTrimestre' => (int)$modelo->MetaCuartoTrimestre,
            'TotalTrimestral' => $total,
            'MetaProgramada' => (int)$programacionAnual->MetaProgramada,
            'ProgramacionCompleta' => $total >= (int)$programacionAnual->MetaProgramada ? 1 : 0,
            'TieneProgramacionTrimestral' => 1,
        ], 'Meta trimestral actualizada.');
    }

    private function obtenerGestionValidada(string $idGestion): PeiGestion
    {
        $gestion = PeiGestion::findOne($idGestion);
        if ($gestion === null) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],
                'No se encontró la gestión activa.',
                404
            );
        }

        return $gestion;
    }
}
