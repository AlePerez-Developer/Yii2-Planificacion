<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\dao\CatUnidadIndicadorDao;
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
            ->orderBy(['IdUnidadIndicador' => SORT_ASC])
            ->asArray()->all();

        return ResponseHelper::success($data, 'Listado de catalogo de unidades de indicador obtenido.');
    }

    /**
     * lista un array de catalogo de unidades indicador no eliminados
     * @param string $search
     * @return array of CatUnidadIndicador
     */
    public function listarTodoS2(string $search): array
    {
        $data = CatUnidadIndicador::listAll($search)
            ->orderBy(['IdUnidadIndicador' => SORT_ASC])
            ->asArray()->all();

        return ResponseHelper::success($data, 'Listado de catalogo de unidades de indicador obtenido.');
    }

    /**
     * obtiene un Objetivo Estrategico en base a un codigo.
     *
     * @param string $id
     * @return CatUnidadIndicador|null
     * @noinspection PhpUnused
     */
    public function listarUno(string $id): ?CatUnidadIndicador
    {
        return CatUnidadIndicador::listOne($id);
    }

    /**
     *  Recibe un id y verifica si existe.
     *
     * @param string $id
     * @return bool
     */
    public function validarId(string $id): bool
    {
        return CatUnidadIndicadorDao::validarId($id);
    }
}