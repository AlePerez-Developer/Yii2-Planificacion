<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\models\CatTipoResultado;

class CatTipoResultadoService
{
    /**
     * lista un array de Catalogo de tipo resultado no eliminados
     *
     * @return array of CatTipoResultado
     */
    public function listarTodo(): array
    {
        $data = CatTipoResultado::listAll()
            ->orderBy(['Codigo' => SORT_ASC])
            ->asArray()->all();

        return ResponseHelper::success($data, 'Listado de catalogo de tipo resultado obtenido.');
    }

    /**
     * lista un array de catalogo de tipos resultados no eliminados
     * @param string $search
     * @return array of CatTipoResultado
     */
    public function listarTodoS2(string $search): array
    {
        $data = CatTipoResultado::listAll($search)
            ->orderBy(['Codigo' => SORT_ASC])
            ->asArray()->all();

        return ResponseHelper::success($data, 'Listado de catalogo de tipo resultado obtenido.');
    }

    /**
     * obtiene un registro de CatTipoResultado en base a un codigo.
     *
     * @param string $id
     * @return CatTipoResultado|null
     */
    public function listarUno(string $id): ?CatTipoResultado
    {
        return CatTipoResultado::listOne($id);
    }
}