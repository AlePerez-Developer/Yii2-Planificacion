<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\dao\CatTipoResultadoDao;
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
            ->orderBy(['IdTipoResultado' => SORT_ASC])
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
            ->orderBy(['IdTipoResultado' => SORT_ASC])
            ->asArray()->all();

        return ResponseHelper::success($data, 'Listado de catalogo de tipo resultado obtenido.');
    }

    /**
     * obtiene un registro de CatTipoResultado en base a un codigo.
     *
     * @param string $id
     * @return CatTipoResultado|null
     * @noinspection PhpUnused
     */
    public function listarUno(string $id): ?CatTipoResultado
    {
        return CatTipoResultado::listOne($id);
    }

    /**
     *  Recibe un id y verifica si existe.
     *
     * @param string $id
     * @return bool
     */
    public function validarId(string $id): bool
    {
        return CatTipoResultadoDao::validarId($id);
    }
}