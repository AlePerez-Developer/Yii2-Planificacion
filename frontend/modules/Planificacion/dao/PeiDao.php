<?php
namespace app\modules\Planificacion\dao;

use app\modules\Planificacion\models\IndicadorEstrategicoGestion;
use app\modules\Planificacion\models\Pei;
use yii\db\StaleObjectException;
use yii\db\Exception;
use Throwable;

class PeiDao
{
    static function enUso(Pei $pei): bool
    {
        return $pei->getObjetivosEstrategicos()->exists();
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