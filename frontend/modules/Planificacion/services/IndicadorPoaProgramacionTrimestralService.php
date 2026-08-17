<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\models\Indicador;
use app\modules\Planificacion\models\IndicadorPoa;
use app\modules\Planificacion\models\LlavePresupuestaria;
use app\modules\Planificacion\models\ObjetivoEspecifico;
use app\modules\Planificacion\models\PeiGestion;
use app\modules\Planificacion\models\ProgramacionIndicadorPoaGestion;
use app\modules\Planificacion\models\ProgramacionIndicadorPoaTrimestre;
use common\models\Estado;
use Throwable;
use Yii;

class IndicadorPoaProgramacionTrimestralService
{
    private const CAMPOS_TRIMESTRE = [
        1 => 'MetaPrimerTrimestre',
        2 => 'MetaSegundoTrimestre',
        3 => 'MetaTercerTrimestre',
        4 => 'MetaCuartoTrimestre',
    ];

    public function listarProgramacion(
        string $idObjEspecifico,
        string $idUnidadEjecutora,
        string $idGestion
    ): array {
        $this->validarObjetivo($idObjEspecifico, $idUnidadEjecutora, $idGestion);

        $data = ProgramacionIndicadorPoaGestion::find()->alias('PG')
            ->select([
                'PG.IdProgramacionIndicadorPoaGestion',
                'PG.IdLlavePresupuestaria',
                'PG.MetaProgramada',
                'Gestion' => 'G.Gestion',
                'Llave' => 'LP.Llave',
                'LlaveDescripcion' => 'LP.Descripcion',
                'IndicadorCodigo' => 'P.Codigo',
                'IndicadorDescripcion' => 'I.Descripcion',
                'MetaPrimerTrimestre' => 'COALESCE(PT.MetaPrimerTrimestre, 0)',
                'MetaSegundoTrimestre' => 'COALESCE(PT.MetaSegundoTrimestre, 0)',
                'MetaTercerTrimestre' => 'COALESCE(PT.MetaTercerTrimestre, 0)',
                'MetaCuartoTrimestre' => 'COALESCE(PT.MetaCuartoTrimestre, 0)',
            ])
            ->innerJoin(['G' => PeiGestion::tableName()], 'G.IdGestion = PG.IdGestion')
            ->innerJoin(['LP' => LlavePresupuestaria::tableName()], 'LP.IdLlavePresupuestaria = PG.IdLlavePresupuestaria')
            ->innerJoin(['P' => IndicadorPoa::tableName()], 'P.IdIndicador = PG.IdIndicadorPoa')
            ->innerJoin(['I' => Indicador::tableName()], 'I.IdIndicador = P.IdIndicador')
            ->leftJoin(
                ['PT' => ProgramacionIndicadorPoaTrimestre::tableName()],
                'PT.IdProgramacionIndicadorPoaGestion = PG.IdProgramacionIndicadorPoaGestion'
            )
            ->where([
                'PG.IdObjEspecifico' => $idObjEspecifico,
                'PG.IdGestion' => $idGestion,
                'LP.IdUnidadEjecutora' => $idUnidadEjecutora,
            ])
            ->orderBy(['LP.Llave' => SORT_ASC, 'P.Codigo' => SORT_ASC])
            ->asArray()
            ->all();

        foreach ($data as &$row) {
            $row = array_merge($row, $this->calcularTotales($row));
        }
        unset($row);

        return ResponseHelper::success($data, 'Programación trimestral obtenida.');
    }

    public function guardarMeta(
        string $idProgramacion,
        string $idObjEspecifico,
        int $trimestre,
        int $meta,
        string $idUnidadEjecutora,
        string $idGestion
    ): array {
        if (!isset(self::CAMPOS_TRIMESTRE[$trimestre]) || $meta < 0) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'El trimestre o la meta no son válidos.',
                400
            );
        }

        $this->validarObjetivo($idObjEspecifico, $idUnidadEjecutora, $idGestion);
        $programacion = ProgramacionIndicadorPoaGestion::find()->alias('PG')
            ->innerJoin(['LP' => LlavePresupuestaria::tableName()], 'LP.IdLlavePresupuestaria = PG.IdLlavePresupuestaria')
            ->where([
                'PG.IdProgramacionIndicadorPoaGestion' => $idProgramacion,
                'PG.IdObjEspecifico' => $idObjEspecifico,
                'PG.IdGestion' => $idGestion,
                'LP.IdUnidadEjecutora' => $idUnidadEjecutora,
            ])
            ->one();

        if ($programacion === null) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],
                'No se encontró la programación anual seleccionada.',
                404
            );
        }

        $modelo = ProgramacionIndicadorPoaTrimestre::findOne([
            'IdProgramacionIndicadorPoaGestion' => $idProgramacion,
        ]);

        if ($modelo === null) {
            $modelo = new ProgramacionIndicadorPoaTrimestre([
                'IdProgramacionIndicadorPoaGestion' => $idProgramacion,
                'MetaPrimerTrimestre' => 0,
                'MetaSegundoTrimestre' => 0,
                'MetaTercerTrimestre' => 0,
                'MetaCuartoTrimestre' => 0,
                'CodigoUsuario' => Yii::$app->user->identity->CodigoUsuario,
            ]);
        }

        $campo = self::CAMPOS_TRIMESTRE[$trimestre];
        $modelo->$campo = $meta;
        $modelo->CodigoUsuario = Yii::$app->user->identity->CodigoUsuario;

        $data = [
            'MetaPrimerTrimestre' => (int)$modelo->MetaPrimerTrimestre,
            'MetaSegundoTrimestre' => (int)$modelo->MetaSegundoTrimestre,
            'MetaTercerTrimestre' => (int)$modelo->MetaTercerTrimestre,
            'MetaCuartoTrimestre' => (int)$modelo->MetaCuartoTrimestre,
        ];
        $totalTrimestral = array_sum($data);

        $programacion->MetaProgramada = $totalTrimestral;
        $programacion->CodigoUsuario = Yii::$app->user->identity->CodigoUsuario;

        if (!$modelo->validate() || !$programacion->validate()) {
            throw new ValidationException(
                Yii::$app->params['ERROR_VALIDACION_MODELO'],
                array_merge($modelo->getErrors(), $programacion->getErrors()),
                422
            );
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$modelo->save(false) || !$programacion->save(false)) {
                throw new ValidationException(
                    Yii::$app->params['ERROR_EJECUCION_SQL'],
                    array_merge($modelo->getErrors(), $programacion->getErrors()),
                    500
                );
            }
            $transaction->commit();
        } catch (Throwable $exception) {
            $transaction->rollBack();
            throw $exception;
        }

        return ResponseHelper::success(
            array_merge($data, [
                'MetaProgramada' => $totalTrimestral,
                'TotalTrimestral' => $totalTrimestral,
                'ProgramacionCompleta' => 1,
            ]),
            'Meta trimestral actualizada.'
        );
    }

    private function calcularTotales(array $row): array
    {
        $total = (int)$row['MetaPrimerTrimestre']
            + (int)$row['MetaSegundoTrimestre']
            + (int)$row['MetaTercerTrimestre']
            + (int)$row['MetaCuartoTrimestre'];

        return [
            'TotalTrimestral' => $total,
            'ProgramacionCompleta' => $total >= (int)$row['MetaProgramada'] ? 1 : 0,
        ];
    }

    private function validarObjetivo(
        string $idObjEspecifico,
        string $idUnidadEjecutora,
        string $idGestion
    ): void {
        $valido = ObjetivoEspecifico::find()
            ->where([
                'IdObjEspecifico' => $idObjEspecifico,
                'IdUnidadEjecutora' => $idUnidadEjecutora,
                'IdGestion' => $idGestion,
            ])
            ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->exists();

        if (!$valido) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'El objetivo específico no pertenece al contexto activo.',
                400
            );
        }
    }
}
