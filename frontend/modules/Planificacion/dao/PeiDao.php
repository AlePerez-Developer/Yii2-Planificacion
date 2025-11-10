<?php
namespace app\modules\Planificacion\dao;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\models\IndicadorEstrategicoGestion;
use app\modules\Planificacion\models\PeiGestion;
use app\modules\Planificacion\models\Pei;
use yii\db\StaleObjectException;
use yii\db\Exception;
use Throwable;
use Yii;

class PeiDao
{
    static function enUso(Pei $pei): bool
    {
        return $pei->getObjetivosEstrategicos()->exists();
    }

    /**
     * @throws Exception|ValidationException
     */
    static function generarGestionesPei(Pei $pei): array
    {
        for ($i = $pei->GestionInicio; $i <= $pei->GestionFin; $i++) {
            $gestion = new PeiGestion();
            $gestion->IdPei = $pei->IdPei;
            $gestion->Gestion  = $i;
            $gestion->CodigoUsuario = $pei->CodigoUsuario;
            $gestion->save();

            if (!$gestion->validate()) {
                throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'],$gestion->getErrors(),500);
            }

            if (!$gestion->save(false)) {
                Yii::error("Error al guardar el cambio de estado del PEI $gestion->IdPei", __METHOD__);
                throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'],$gestion->getErrors(),500);
            }
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

    static function validarGestionInicio(string $idPei, $inicioNuevo): bool
    {
        $model = Pei::find()->alias('p')->select(['*'])
            ->join('INNER JOIN','ObjetivosEstrategicos o', 'o.IdPei = p.IdPei')
            ->join('INNER JOIN','IndicadoresEstrategicos i', 'i.IdObjEstrategico = o.IdObjEstrategico')
            ->join('INNER JOIN','IndicadoresEstrategicosGestiones ig', 'ig.IdIndicadorEstrategico = i.IdIndicadorEstrategico')
            ->where('(p.IdPei = :pei) and (ig.Gestion < :Gestion) and (ig.Meta > 0) ',[':pei'=>$idPei,':Gestion'=>$inicioNuevo])
            ->one();
        if (empty($model)) {
            return true;
        } else {
            return false;
        }
    }

    static function validarGestionFin(string $idPei, $finNuevo): bool
    {
        $model = Pei::find()->alias('p')->select(['*'])
            ->join('INNER JOIN','ObjetivosEstrategicos o', 'o.IdPei = p.IdPei')
            ->join('INNER JOIN','IndicadoresEstrategicos i', 'i.IdObjEstrategico = o.IdObjEstrategico')
            ->join('INNER JOIN','IndicadoresEstrategicosGestiones ig', 'ig.IdIndicadorEstrategico = i.IdIndicadorEstrategico')
            ->where('(p.IdPei = :pei) and (ig.Gestion > :Gestion) and (ig.Meta > 0)',[':pei'=>$idPei,':Gestion'=>$finNuevo])
            ->one();
        if (empty($ind)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @throws Throwable
     * @throws StaleObjectException
     */
    static function regularizarProgramacionIndicadoresFin(string $idPei, int $gestionFin): void
    {
        $model = IndicadorEstrategicoGestion::find()->select('*')->alias('ig')
            ->join('INNER JOIN','IndicadoresEstrategicos i', 'ig.IdIndicadorEstrategico = i.IdIndicadorEstrategico')
            ->join('INNER JOIN','ObjetivosEstrategicos o', 'i.IdObjEstrategico = o.IdObjEstrategico')
            ->where('(o.IdPei = :pei) and (ig.Gestion > :Gestion)',[':pei'=>$idPei,':Gestion'=>$gestionFin])
            ->all();
        foreach ($model as $programacion){
            if (!$programacion->delete()){
                throw new Exception("No se pudo eliminar la programación", 500);
            }
        }
    }

    /**
     * @throws Throwable
     * @throws StaleObjectException
     */
    static function regularizarProgramacionIndicadoresInicio(string $idPei, int $gestionInicio): void
    {
        $model = IndicadorEstrategicoGestion::find()->select('*')->alias('ig')
            ->join('INNER JOIN','IndicadoresEstrategicos i', 'ig.IdIndicadorEstrategico = i.IdIndicadorEstrategico')
            ->join('INNER JOIN','ObjetivosEstrategicos o', 'i.IdObjEstrategico = o.IdObjEstrategico')
            ->where('(o.CodigoPei = :pei) and (ig.Gestion < :Gestion)',[':pei'=>$idPei,':Gestion'=>$gestionInicio])
            ->all();
        foreach ($model as $programacion){
            if (!$programacion->delete()){
                throw new Exception("No se pudo eliminar la programación", 500);
            }
        }
    }
}