<?php
namespace app\modules\Planificacion\dao;

use app\modules\Planificacion\models\IndicadorEstrategicoGestion;
use app\modules\Planificacion\models\ObjetivoEstrategico;
use app\modules\Planificacion\models\Pei;
use yii\db\StaleObjectException;
use yii\db\Transaction;
use yii\db\Query;
use Throwable;

class PeiDao
{
    /*=============================================
     Genera un nuevo codigo de pei
     =============================================*/
    static public function generarCodigoPei()
    {
        $consulta = new Query();
        $codigo = $consulta->select('max(CodigoPei) as CodigoPei')
            ->from('PEIs')
            ->one();
        if ($codigo['CodigoPei']){
            return  $codigo['CodigoPei'] + 1;
        } else {
            return 1;
        }
    }

    static function enUso(int $codigoPei): bool
    {
        return ObjetivoEstrategico::find()->where(['CodigoPei' => $codigoPei])->exists();
    }

    static function validarGestionInicio(int $codigoPei, $inicioNuevo): bool
    {
        $ind = Pei::find()->alias('p')->select(['*'])
            ->join('INNER JOIN','ObjetivosEstrategicos o', 'o.CodigoPei = p.CodigoPei')
            ->join('INNER JOIN','IndicadoresEstrategicos i', 'i.ObjetivoEstrategico = o.CodigoObjEstrategico')
            ->join('INNER JOIN','IndicadoresEstrategicosGestiones ig', 'ig.IndicadorEstrategico = i.CodigoIndicador')
            ->where('(p.CodigoPei = :pei) and (ig.Gestion < :Gestion) and (ig.Meta > 0) ',[':pei'=>$codigoPei,':Gestion'=>$inicioNuevo])
            ->one();
        if (empty($ind)) {
            return true;
        } else {
            return false;
        }
    }

    static function validarGestionFin(int $codigoPei, $finNuevo): bool
    {
        $ind = Pei::find()->alias('p')->select(['*'])
            ->join('INNER JOIN','ObjetivosEstrategicos o', 'o.CodigoPei = p.CodigoPei')
            ->join('INNER JOIN','IndicadoresEstrategicos i', 'i.ObjetivoEstrategico = o.CodigoObjEstrategico')
            ->join('INNER JOIN','IndicadoresEstrategicosGestiones ig', 'ig.IndicadorEstrategico = i.CodigoIndicador')
            ->where('(p.CodigoPei = :pei) and (ig.Gestion > :Gestion) and (ig.Meta > 0)',[':pei'=>$codigoPei,':Gestion'=>$finNuevo])
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
    static function regularizarProgramacionIndicadoresFin(int $codigoPei, int $gestionFin, Transaction $transaction)
    {
        $programaciones = IndicadorEstrategicoGestion::find()->select('*')->alias('ig')
            ->join('INNER JOIN','IndicadoresEstrategicos i', 'ig.IndicadorEstrategico = i.CodigoIndicador')
            ->join('INNER JOIN','ObjetivosEstrategicos o', 'i.ObjetivoEstrategico = o.CodigoObjEstrategico')
            ->where('(o.CodigoPei = :pei) and (ig.Gestion > :Gestion)',[':pei'=>$codigoPei,':Gestion'=>$gestionFin])
            ->all();
        if ($programaciones){
            foreach ($programaciones as $programacion){
                if (!$programacion->delete()){
                    $transaction->rollBack();
                    break;
                }
            }
        }
    }

    /**
     * @throws Throwable
     * @throws StaleObjectException
     */
    static function regularizarProgramacionIndicadoresInicio(int $codigoPei, int $gestionInicio, Transaction $transaction)
    {
        $programaciones = IndicadorEstrategicoGestion::find()->select('*')->alias('ig')
            ->join('INNER JOIN','IndicadoresEstrategicos i', 'ig.IndicadorEstrategico = i.CodigoIndicador')
            ->join('INNER JOIN','ObjetivosEstrategicos o', 'i.ObjetivoEstrategico = o.CodigoObjEstrategico')
            ->where('(o.CodigoPei = :pei) and (ig.Gestion < :Gestion)',[':pei'=>$codigoPei,':Gestion'=>$gestionInicio])
            ->all();
        if ($programaciones){
            foreach ($programaciones as $programacion){
                if (!$programacion->delete()){
                    $transaction->rollBack();
                    break;
                }
            }
        }
    }
}