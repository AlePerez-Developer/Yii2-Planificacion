<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\models\IndicadorEstrategico;

class IndicadorEstrategicoProgramacionService
{
    /**
     * Lista un array de Indicadores Estrategicos no eliminados según un, Id Objetivo Estrategico
     *
     * @return array of Indicadores Estategicos segun
     */
    public function listarTodobyObjConProgramacion(string $id): array
    {
        $data = IndicadorEstrategico::listAll()
            ->addSelect(['isnull(sum(Ip.MetaProgramada),0) as MetaProgramada'])
            ->joinWith('indicadorEstrategicoProgramacionGestions Ip', true, 'LEFT JOIN')
            ->andWhere(['I.IdObjEstrategico' => $id])
            ->orderBy(['Codigo' => SORT_ASC])
            ->asArray()->all();

        return ResponseHelper::success($data,'Listado de Indicadores Estrategicos por Objetivo obtenido.');
    }

}