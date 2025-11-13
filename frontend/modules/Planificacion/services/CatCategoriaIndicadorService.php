<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\dao\CatCategoriaIndicadorDao;
use app\modules\Planificacion\models\CatCategoriaIndicador;

class CatCategoriaIndicadorService
{
    /**
     * lista un array de Catalogo de categoria indicador no eliminados
     *
     * @return array of CatTipoResultado
     */
    public function listarTodo(): array
    {
        $data = CatCategoriaIndicador::listAll()
            ->orderBy(['Codigo' => SORT_ASC])
            ->asArray()->all();

        return ResponseHelper::success($data, 'Listado de catalogo de tipo resultado obtenido.');
    }

    /**
     * lista un array de catalogo de categoria indicador no eliminados
     * @param string $search
     * @return array of CatCategoriaIndicador
     */
    public function listarTodoS2(string $search): array
    {
        $data = CatCategoriaIndicador::listAll($search)
            ->orderBy(['Codigo' => SORT_ASC])
            ->asArray()->all();

        return ResponseHelper::success($data, 'Listado de categoria indicador obtenido.');
    }

    /**
     * obtiene un registro de CatTipoResultado en base a un codigo.
     *
     * @param string $id
     * @return CatCategoriaIndicador|null
     * @noinspection PhpUnused
     */
    public function listarUno(string $id): ?CatCategoriaIndicador
    {
        return CatCategoriaIndicador::listOne($id);
    }

    /**
     *  Recibe un id y verifica si existe.
     *
     * @param string $id
     * @return bool
     */
    public function validarId(string $id): bool
    {
        return CatCategoriaIndicadorDao::validarId($id);
    }
}