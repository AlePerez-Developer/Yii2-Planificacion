<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\models\CatUnidadIndicador;

class CatUnidadIndicadorService
{
    /**
     * lista un array de Catalogo de unidades indicador no eliminados
     *
     * @return array of CatUnidadIndicador
     */
    public function listarTodo(): array
    {
        $data = CatUnidadIndicador::listAll()
            ->orderBy(['Codigo' => SORT_ASC])
            ->asArray()->all();

        return ResponseHelper::success($data, 'Listado de catalogo de unidades de indicador obtenido.');
    }

    /**
     * lista un array de catalogo de unidades indicador no eliminados
     * @param string $search
     * @return array of CatUnidadIndicador
     */
    public function listarAreasS2(string $search): array
    {
        $data = CatUnidadIndicador::listAll($search)
            ->orderBy(['Codigo' => SORT_ASC])
            ->asArray()->all();

        return ResponseHelper::success($data, 'Listado de catalogo de unidades de indicador obtenido.');
    }

    /**
     * obtiene un Objetivo Estrategico en base a un codigo.
     *
     * @param string $id
     * @return CatUnidadIndicador|null
     */
    public function listarUno(string $id): ?CatUnidadIndicador
    {
        return CatUnidadIndicador::listOne($id);
    }

}