<?php
namespace app\modules\Planificacion\dao;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\models\IndicadorEstrategicoProgramacionGestion;
use app\modules\Planificacion\models\IndicadorPoa;
use app\modules\Planificacion\models\PeiGestion;
use app\modules\Planificacion\models\Pei;
use app\modules\Planificacion\models\ProgramacionIndicadorGestion;
use app\modules\Planificacion\models\ProgramacionIndicadorPoaGestion;
use common\models\Estado;
use yii\db\StaleObjectException;
use Throwable;
use Yii;

class PeiDao
{
    static function enUso(Pei $pei): bool
    {
        return $pei->getObjetivosEstrategicos()->exists();
    }

    public static function existeVigente(?string $excluirId = null): bool
    {
        $query = Pei::find()->where(['CodigoEstado' => Estado::ESTADO_VIGENTE]);
        if ($excluirId !== null && $excluirId !== '') {
            $query->andWhere(['<>', 'IdPei', $excluirId]);
        }

        return $query->exists();
    }

    public static function caducarVigentes(string $excluirId): int
    {
        return Pei::updateAll(
            ['CodigoEstado' => Estado::ESTADO_CADUCO],
            [
                'and',
                ['CodigoEstado' => Estado::ESTADO_VIGENTE],
                ['<>', 'IdPei', $excluirId],
            ]
        );
    }

    /**
     * @throws Exception|ValidationException
     */
    static function generarGestionesPei(Pei $pei): array
    {
        for ($i = $pei->GestionInicio; $i <= $pei->GestionFin; $i++) {
            self::crearGestion($pei, $i);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => '',
        ];
    }

    static function eliminarGestionesPei(Pei $pei): void
    {
        PeiGestion::deleteAll(['IdPei' => $pei->IdPei]);
    }

    /**
     * @throws ValidationException
     * @throws Throwable
     * @throws StaleObjectException
     */
    static function sincronizarGestionesPei(Pei $pei, int $gestionInicio, int $gestionFin): void
    {
        $existentes = PeiGestion::find()
            ->where(['IdPei' => $pei->IdPei])
            ->indexBy('Gestion')
            ->all();

        for ($anio = $gestionInicio; $anio <= $gestionFin; $anio++) {
            if (!isset($existentes[$anio])) {
                self::crearGestion($pei, $anio);
            }
        }

        foreach ($existentes as $anio => $gestion) {
            $anio = (int)$anio;
            if ($anio >= $gestionInicio && $anio <= $gestionFin) {
                continue;
            }
            if (!$gestion->delete()) {
                throw new ValidationException(
                    Yii::$app->params['ERROR_EJECUCION_SQL'],
                    $gestion->getErrors(),
                    500
                );
            }
        }
    }

    static function existenProgramacionesFueraDeRango(string $idPei, int $inicio, int $fin): bool
    {
        $idsGestion = PeiGestion::find()
            ->select('IdGestion')
            ->where(['IdPei' => $idPei])
            ->andWhere(['or', ['<', 'Gestion', $inicio], ['>', 'Gestion', $fin]])
            ->column();

        if ($idsGestion === []) {
            return false;
        }

        return ProgramacionIndicadorGestion::find()->where(['IdGestion' => $idsGestion])->exists()
            || IndicadorEstrategicoProgramacionGestion::find()->where(['IdGestion' => $idsGestion])->exists()
            || ProgramacionIndicadorPoaGestion::find()->where(['IdGestion' => $idsGestion])->exists()
            || IndicadorPoa::find()->where(['IdGestion' => $idsGestion])->exists();
    }

    /**
     * @throws ValidationException
     */
    private static function crearGestion(Pei $pei, int $gestion): void
    {
        $modelo = new PeiGestion();
        $modelo->IdPei = $pei->IdPei;
        $modelo->Gestion = $gestion;
        $modelo->CodigoUsuario = $pei->CodigoUsuario;

        if (!$modelo->validate()) {
            throw new ValidationException(
                Yii::$app->params['ERROR_VALIDACION_MODELO'],
                $modelo->getErrors(),
                500
            );
        }

        if (!$modelo->save(false)) {
            Yii::error("Error al guardar la gestión $gestion del PEI $pei->IdPei", __METHOD__);
            throw new ValidationException(
                Yii::$app->params['ERROR_EJECUCION_SQL'],
                $modelo->getErrors(),
                500
            );
        }
    }
}
