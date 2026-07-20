<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\models\IndicadorEstrategico;
use app\modules\Planificacion\models\ProgramacionIndicadorGestion;
use app\modules\Planificacion\models\ProgramacionIndicadorTrimestre;
use Throwable;
use Yii;
use yii\db\Expression;

class IndicadorEstrategicoProgramacionTrimestralService
{
    /**
     * Lista indicadores del objetivo que ya tienen su programación anual completa
     * en la gestión activa: SUM(MetaProgramada) >= Meta del indicador.
     */
    public function listarIndicadores(string $idObjEstrategico, string $idGestion): array
    {
        $data = IndicadorEstrategico::find()
            ->alias('I')
            ->select([
                'I.IdIndicadorEstrategico',
                'I.Codigo',
                'I.Descripcion',
                'I.Meta',

                'MetaProgramada' => new Expression('ISNULL(SUM(P.MetaProgramada), 0)'),
            ])
            ->innerJoin(
                ['P' => ProgramacionIndicadorGestion::tableName()],
                'P.IdIndicadorEstrategico = I.IdIndicadorEstrategico '
            )
            ->where(['I.IdObjEstrategico' => $idObjEstrategico])
            ->groupBy([
                'I.IdIndicadorEstrategico',
                'I.Codigo',
                'I.Descripcion',
                'I.Meta',

            ])
            ->having('ISNULL(SUM(P.MetaProgramada), 0) >= I.Meta')
            ->orderBy(['I.Codigo' => SORT_ASC])
            ->asArray()
            ->all();

        return ResponseHelper::success($data, 'Indicadores con programación anual completa obtenidos.');
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
     * incorporando las metas trimestrales. Si aún no existe el registro trimestral,
     * devuelve ceros sin crearlo hasta el primer guardado.
     */
    public function listarProgramacion(string $idIndicadorEstrategico, string $idGestion): array
    {
        $data = ProgramacionIndicadorGestion::find()
            ->alias('P')
            ->select([
                'P.IdProgramacionIndicadorGestio',
                'L.IdLlavePresupuestaria',
                'CodigoCompuesto' => 'L.Llave',
                'L.Descripcion',
                'P.MetaProgramada',
                'MetaPrimerTrimestre' => new Expression('ISNULL(T.MetaPrimerTrimestre, 0)'),
                'MetaSegundoTrimestre' => new Expression('ISNULL(T.MetaSegundoTrimestre, 0)'),
                'MetaTercerTrimestre' => new Expression('ISNULL(T.MetaTercerTrimestre, 0)'),
                'MetaCuartoTrimestre' => new Expression('ISNULL(T.MetaCuartoTrimestre, 0)'),
                'TotalTrimestral' => new Expression('ISNULL(T.MetaPrimerTrimestre, 0) + ISNULL(T.MetaSegundoTrimestre, 0) + ISNULL(T.MetaTercerTrimestre, 0) + ISNULL(T.MetaCuartoTrimestre, 0)'),
            ])
            ->innerJoin(['L' => 'LlavesPresupuestarias'], 'L.IdLlavePresupuestaria = P.IdLlavePresupuestaria')
            ->leftJoin(
                ['T' => ProgramacionIndicadorTrimestre::tableName()],
                'T.IdProgramacionIndicadorGestio = P.IdProgramacionIndicadorGestio'
            )
            ->where([
                'P.IdIndicadorEstrategico' => $idIndicadorEstrategico,
                'P.IdGestion' => $idGestion,
            ])
            ->orderBy(['L.Llave' => SORT_ASC])
            ->asArray()
            ->all();

        foreach ($data as &$fila) {
            $fila['ProgramacionCompleta'] = (int)$fila['TotalTrimestral'] >= (int)$fila['MetaProgramada'] ? 1 : 0;
        }
        unset($fila);

        return ResponseHelper::success($data, 'Programación trimestral obtenida.');
    }

    /**
     * Crea o actualiza una meta trimestral mediante upsert lógico.
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
            'IdProgramacionIndicadorGestio' => $idProgramacionIndicadorGestio,
        ]);

        if ($modelo === null) {
            $modelo = new ProgramacionIndicadorTrimestre([
                'IdProgramacionIndicadorGestio' => $idProgramacionIndicadorGestio,
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

        if (!$modelo->validate()) {
            throw new ValidationException(
                Yii::$app->params['ERROR_VALIDACION_MODELO'],
                $modelo->getErrors(),
                400
            );
        }

        if (!$modelo->save(false)) {
            throw new ValidationException(
                Yii::$app->params['ERROR_GUARDAR_REGISTRO'],
                'No fue posible guardar la programación trimestral.',
                500
            );
        }

        $total = (int)$modelo->MetaPrimerTrimestre
            + (int)$modelo->MetaSegundoTrimestre
            + (int)$modelo->MetaTercerTrimestre
            + (int)$modelo->MetaCuartoTrimestre;

        return ResponseHelper::success([
            'MetaPrimerTrimestre' => (int)$modelo->MetaPrimerTrimestre,
            'MetaSegundoTrimestre' => (int)$modelo->MetaSegundoTrimestre,
            'MetaTercerTrimestre' => (int)$modelo->MetaTercerTrimestre,
            'MetaCuartoTrimestre' => (int)$modelo->MetaCuartoTrimestre,
            'TotalTrimestral' => $total,
            'MetaProgramada' => (int)$programacionAnual->MetaProgramada,
            'ProgramacionCompleta' => $total >= (int)$programacionAnual->MetaProgramada ? 1 : 0,
        ], 'Meta trimestral actualizada.');
    }

    private function obtenerGestionValidada(string $idGestion): \app\modules\Planificacion\models\PeiGestion
    {
        $gestion = \app\modules\Planificacion\models\PeiGestion::findOne($idGestion);
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
