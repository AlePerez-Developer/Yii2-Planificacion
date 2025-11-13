<?php
namespace app\modules\Planificacion\dao;

use app\modules\Planificacion\models\IndicadorEstrategico;
use common\models\Estado;

class IndicadorEstrategicoDao
{
    static function enUso(IndicadorEstrategico $modelo): bool
    {
        return $modelo->getIndicadorEstrategicoProgramacionGestions()->exists();
    }

    static function verificarCodigo(string $id,  int $codigo): bool
    {
        $modelo = IndicadorEstrategico::find()
            ->where(['Codigo' => $codigo, 'CodigoEstado' => Estado::ESTADO_VIGENTE])
            ->andWhere(['!=','IdIndicadorEstrategico',$id])
            ->one();

        return !$modelo;
    }

    static function validarId(string $id, string $idObjEstrategico): bool
    {
        return IndicadorEstrategico::find()->where(['IdObjEstrategico'=> $idObjEstrategico ,'IdIndicadorEstrategico' => $id, 'CodigoEstado' => Estado::ESTADO_VIGENTE])->exists();
    }
}