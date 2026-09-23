<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\PoaEdicionHelper;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\formModels\ItemDescatalogadoForm;
use app\modules\Planificacion\models\Fuente;
use app\modules\Planificacion\models\FuenteUniversitaria;
use app\modules\Planificacion\models\Gasto;
use app\modules\Planificacion\models\Item;
use app\modules\Planificacion\models\ItemDescatalogado;
use app\modules\Planificacion\models\ItemGestion;
use app\modules\Planificacion\models\ItemGestionOperacion;
use app\modules\Planificacion\models\Operacion;
use app\modules\Planificacion\models\Organismo;
use app\modules\Planificacion\models\Partida;
use app\modules\Planificacion\models\TechoUnidad;
use app\modules\Planificacion\models\UnidadMedida;
use app\modules\Planificacion\dao\PresupuestoConsumoDao;
use common\models\Estado;
use Yii;
use yii\db\Expression;

class ItemsDescatalogadosService
{
    public function listarTodo(
        int $formulario,
        string $idUnidad,
        string $idGestion,
        string $idEstadoPoa
    ): array {
        $items = Item::find()->alias('IT')
            ->select([
                'IT.IdItem',
                'IT.Descripcion',
                'IT.Formulario',
                'IT.IdFuente',
                'IT.IdOrganismo',
                'IT.IdFuenteUniversitaria',
                'IT.IdUnidadMedida',
                'IT.PrecioReferencial',
                'Precio' => 'IG.PrecioUnitario',
                'IdGasto' => 'ID.IdGasto',
                'GastoCodigo' => 'G.CodigoGasto',
                'GastoDescripcion' => 'G.Descripcion',
                'EntidadTransferencia' => 'G.EntidadTransferencia',
                'FuenteDescripcion' => 'F.Descripcion',
                'OrganismoDescripcion' => 'ORG.Descripcion',
                'FuenteUniversitariaDescripcion' => 'FU.Descripcion',
                'UnidadMedidaDescripcion' => 'UM.Descripcion',
                'UnidadMedidaSimbolo' => 'UM.Simbolo',
                'CantidadTotal' => new Expression('ISNULL(SUM(IGO.Cantidad), 0)'),
                'TotalItem' => new Expression(
                    'ISNULL(SUM(CAST(IGO.Cantidad AS decimal(18,2)) * CAST(IG.PrecioUnitario AS decimal(18,2))), 0)'
                ),
            ])
            ->innerJoin(['ID' => ItemDescatalogado::tableName()], 'ID.IdItem = IT.IdItem')
            ->innerJoin(['G' => Gasto::tableName()], 'G.IdGasto = ID.IdGasto')
            ->innerJoin(['IG' => ItemGestion::tableName()], 'IG.IdItem = IT.IdItem')
            ->innerJoin(['IGO' => ItemGestionOperacion::tableName()], 'IGO.IdItem_Gestion = IG.IdItem_Gestion')
            ->innerJoin(['O' => Operacion::tableName()], 'O.IdOperacion = IGO.IdOperacion')
            ->innerJoin(['F' => Fuente::tableName()], 'F.IdFuente = IT.IdFuente')
            ->innerJoin(['ORG' => Organismo::tableName()], 'ORG.IdOrganismo = IT.IdOrganismo')
            ->innerJoin(['FU' => FuenteUniversitaria::tableName()], 'FU.IdFuenteUniversitaria = IT.IdFuenteUniversitaria')
            ->innerJoin(['UM' => UnidadMedida::tableName()], 'UM.IdUnidadMedida = IT.IdUnidadMedida')
            ->where([
                'IT.Formulario' => $formulario,
                'IG.IdGestion' => $idGestion,
                'IGO.IdEstadoPoa' => $idEstadoPoa,
                'O.IdUnidadEjecutora' => $idUnidad,
                'O.IdGestion' => $idGestion,
            ])
            ->andWhere(['<>', 'IT.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['<>', 'IG.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['<>', 'IGO.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['<>', 'O.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->groupBy([
                'IT.IdItem',
                'IT.Descripcion',
                'IT.Formulario',
                'IT.IdFuente',
                'IT.IdOrganismo',
                'IT.IdFuenteUniversitaria',
                'IT.IdUnidadMedida',
                'IT.PrecioReferencial',
                'IG.PrecioUnitario',
                'ID.IdGasto',
                'G.CodigoGasto',
                'G.Descripcion',
                'G.EntidadTransferencia',
                'F.Descripcion',
                'ORG.Descripcion',
                'FU.Descripcion',
                'UM.Descripcion',
                'UM.Simbolo',
            ])
            ->orderBy(['IT.Descripcion' => SORT_ASC])
            ->asArray()
            ->all();

        $operacionesPorItem = $this->operacionesPorItems(
            array_column($items, 'IdItem'),
            $idUnidad,
            $idGestion,
            $idEstadoPoa
        );

        $totalFormulario = 0.0;
        foreach ($items as &$item) {
            $item['CantidadTotal'] = (float)$item['CantidadTotal'];
            $item['Precio'] = (float)$item['Precio'];
            $item['TotalItem'] = (float)$item['TotalItem'];
            $item['operaciones'] = $operacionesPorItem[$item['IdItem']] ?? [];
            $totalFormulario += $item['TotalItem'];
        }
        unset($item);

        return ResponseHelper::success([
            'items' => $items,
            'totalFormulario' => round($totalFormulario, 2),
        ]);
    }

    public function listarGastos(int $formulario): array
    {
        $data = Gasto::find()
            ->select([
                'id' => 'IdGasto',
                'text' => new Expression("CONCAT(CodigoGasto, ' - ', Descripcion)"),
                'codigo' => 'CodigoGasto',
                'descripcion' => 'Descripcion',
                'entidadTransferencia' => 'EntidadTransferencia',
            ])
            ->where(['CodigoEstado' => Estado::ESTADO_VIGENTE])
            ->andWhere(['like', 'CodigoGasto', $formulario . '%', false])
            ->orderBy(['CodigoGasto' => SORT_ASC])
            ->asArray()
            ->all();

        return ResponseHelper::success($data);
    }

    public function listarOperaciones(string $idUnidad, string $idGestion, string $idEstadoPoa): array
    {
        $data = Operacion::listAll($idUnidad, $idGestion, $idEstadoPoa)
            ->andWhere(['O.CodigoEstado' => Estado::ESTADO_VIGENTE])
            ->orderBy(['O.Codigo' => SORT_ASC])
            ->asArray()
            ->all();

        return ResponseHelper::success($data);
    }

    public function listarOrganismos(string $idFuente): array
    {
        $data = Partida::find()->alias('P')
            ->select(['id' => 'O.IdOrganismo', 'text' => 'O.Descripcion'])
            ->innerJoin(['O' => Organismo::tableName()], 'O.IdOrganismo = P.IdOrganismo')
            ->where([
                'P.IdFuente' => $idFuente,
                'P.CodigoEstado' => Estado::ESTADO_VIGENTE,
                'O.CodigoEstado' => Estado::ESTADO_VIGENTE,
            ])
            ->orderBy(['O.Descripcion' => SORT_ASC])
            ->asArray()
            ->all();

        return ResponseHelper::success($data);
    }

    public function listarFuentesUniversitarias(string $idFuente, string $idOrganismo): array
    {
        $data = FuenteUniversitaria::find()->alias('FU')
            ->select([
                'id' => 'FU.IdFuenteUniversitaria',
                'text' => new Expression("CONCAT(FU.Codigo, ' - ', FU.Descripcion)"),
            ])
            ->innerJoin(['P' => Partida::tableName()], 'P.IdPartida = FU.IdPartida')
            ->where([
                'P.IdFuente' => $idFuente,
                'P.IdOrganismo' => $idOrganismo,
                'P.CodigoEstado' => Estado::ESTADO_VIGENTE,
                'FU.CodigoEstado' => Estado::ESTADO_VIGENTE,
            ])
            ->orderBy(['FU.Codigo' => SORT_ASC])
            ->asArray()
            ->all();

        return ResponseHelper::success($data);
    }

    public function listarUnidadesMedida(): array
    {
        $data = UnidadMedida::find()
            ->select([
                'id' => 'IdUnidadMedida',
                'text' => new Expression("CONCAT(Descripcion, ' (', Simbolo, ')')"),
            ])
            ->where(['CodigoEstado' => Estado::ESTADO_VIGENTE])
            ->orderBy(['Descripcion' => SORT_ASC])
            ->asArray()
            ->all();

        return ResponseHelper::success($data);
    }

    public function guardar(
        ItemDescatalogadoForm $form,
        string $idUnidad,
        string $idGestion,
        string $idEstadoPoa
    ): array {
        PoaEdicionHelper::asegurarEdicion($idUnidad);
        $this->validarGastoYRelaciones($form);
        $asignaciones = $this->normalizarAsignaciones($form->asignaciones);
        $operaciones = $this->obtenerOperacionesValidas(
            array_keys($asignaciones),
            $idUnidad,
            $idGestion,
            $idEstadoPoa
        );
        $this->validarTechos($asignaciones, $operaciones, (float)$form->precio, $idGestion, $idEstadoPoa, null);

        $usuario = Yii::$app->user->identity->CodigoUsuario;
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $item = new Item([
                'IdFuente' => $form->idFuente,
                'IdOrganismo' => $form->idOrganismo,
                'IdFuenteUniversitaria' => $form->idFuenteUniversitaria,
                'Formulario' => $form->formulario,
                'Descripcion' => mb_strtoupper(trim($form->descripcion), 'UTF-8'),
                'IdUnidadMedida' => $form->idUnidadMedida,
                'PrecioReferencial' => (float)$form->precio,
                'CodigoEstado' => Estado::ESTADO_VIGENTE,
                'CodigoUsuario' => $usuario,
            ]);
            $this->guardarModelo($item);
            $item->refresh();

            $descatalogado = new ItemDescatalogado([
                'IdItem' => $item->IdItem,
                'IdGasto' => $form->idGasto,
            ]);
            $this->guardarModelo($descatalogado);

            $gestion = new ItemGestion([
                'IdItem' => $item->IdItem,
                'IdGestion' => $idGestion,
                'PrecioUnitario' => (float)$form->precio,
                'CodigoEstado' => Estado::ESTADO_VIGENTE,
                'CodigoUsuario' => $usuario,
            ]);
            $this->guardarModelo($gestion);
            $gestion->refresh();

            foreach ($asignaciones as $idOperacion => $cantidad) {
                $igo = new ItemGestionOperacion([
                    'IdItem_Gestion' => $gestion->IdItem_Gestion,
                    'IdOperacion' => $idOperacion,
                    'IdEstadoPoa' => $idEstadoPoa,
                    'Cantidad' => $cantidad,
                    'CodigoEstado' => Estado::ESTADO_VIGENTE,
                    'CodigoUsuario' => $usuario,
                ]);
                $this->guardarModelo($igo);
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        return ResponseHelper::success($item, 'Ítem descatalogado guardado correctamente.');
    }

    public function actualizar(
        string $idItem,
        ItemDescatalogadoForm $form,
        string $idUnidad,
        string $idGestion,
        string $idEstadoPoa
    ): array {
        PoaEdicionHelper::asegurarEdicion($idUnidad);
        $item = $this->obtenerItemValidado($idItem, $form->formulario);
        $this->validarGastoYRelaciones($form);
        $asignaciones = $this->normalizarAsignaciones($form->asignaciones);
        $operaciones = $this->obtenerOperacionesValidas(
            array_keys($asignaciones),
            $idUnidad,
            $idGestion,
            $idEstadoPoa
        );
        $this->validarTechos($asignaciones, $operaciones, (float)$form->precio, $idGestion, $idEstadoPoa, $idItem);

        $usuario = Yii::$app->user->identity->CodigoUsuario;
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $item->IdFuente = $form->idFuente;
            $item->IdOrganismo = $form->idOrganismo;
            $item->IdFuenteUniversitaria = $form->idFuenteUniversitaria;
            $item->Descripcion = mb_strtoupper(trim($form->descripcion), 'UTF-8');
            $item->IdUnidadMedida = $form->idUnidadMedida;
            $item->PrecioReferencial = (float)$form->precio;
            $item->CodigoUsuario = $usuario;
            $this->guardarModelo($item);

            $descatalogado = ItemDescatalogado::findOne(['IdItem' => $item->IdItem]);
            if ($descatalogado === null) {
                $descatalogado = new ItemDescatalogado(['IdItem' => $item->IdItem]);
            }
            $descatalogado->IdGasto = $form->idGasto;
            $this->guardarModelo($descatalogado);

            $gestion = ItemGestion::find()
                ->where(['IdItem' => $item->IdItem, 'IdGestion' => $idGestion])
                ->one();
            if ($gestion === null) {
                $gestion = new ItemGestion([
                    'IdItem' => $item->IdItem,
                    'IdGestion' => $idGestion,
                    'CodigoEstado' => Estado::ESTADO_VIGENTE,
                ]);
            }
            $gestion->PrecioUnitario = (float)$form->precio;
            $gestion->CodigoUsuario = $usuario;
            $gestion->CodigoEstado = Estado::ESTADO_VIGENTE;
            $this->guardarModelo($gestion);
            $gestion->refresh();

            $existentes = ItemGestionOperacion::find()
                ->where([
                    'IdItem_Gestion' => $gestion->IdItem_Gestion,
                    'IdEstadoPoa' => $idEstadoPoa,
                ])
                ->indexBy('IdOperacion')
                ->all();

            foreach ($existentes as $idOperacion => $igo) {
                if (!isset($asignaciones[$idOperacion]) && $igo->CodigoEstado !== Estado::ESTADO_ELIMINADO) {
                    $igo->eliminar();
                    $igo->CodigoUsuario = $usuario;
                    $this->guardarModelo($igo);
                }
            }

            foreach ($asignaciones as $idOperacion => $cantidad) {
                $igo = $existentes[$idOperacion] ?? new ItemGestionOperacion([
                    'IdItem_Gestion' => $gestion->IdItem_Gestion,
                    'IdOperacion' => $idOperacion,
                    'IdEstadoPoa' => $idEstadoPoa,
                    'CodigoEstado' => Estado::ESTADO_VIGENTE,
                ]);
                $igo->Cantidad = $cantidad;
                $igo->CodigoEstado = Estado::ESTADO_VIGENTE;
                $igo->CodigoUsuario = $usuario;
                $this->guardarModelo($igo);
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        return ResponseHelper::success($item, 'Ítem descatalogado actualizado correctamente.');
    }

    public function obtenerModelo(
        string $idItem,
        int $formulario,
        string $idUnidad,
        string $idGestion,
        string $idEstadoPoa
    ): array {
        $item = $this->obtenerItemValidado($idItem, $formulario);
        $descatalogado = ItemDescatalogado::findOne(['IdItem' => $item->IdItem]);
        $gasto = $descatalogado ? Gasto::findOne($descatalogado->IdGasto) : null;
        $gestion = ItemGestion::find()
            ->where(['IdItem' => $item->IdItem, 'IdGestion' => $idGestion])
            ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->one();

        $asignaciones = [];
        if ($gestion) {
            $asignaciones = ItemGestionOperacion::find()->alias('IGO')
                ->select(['IGO.IdOperacion', 'IGO.Cantidad'])
                ->innerJoin(['O' => Operacion::tableName()], 'O.IdOperacion = IGO.IdOperacion')
                ->where([
                    'IGO.IdItem_Gestion' => $gestion->IdItem_Gestion,
                    'IGO.IdEstadoPoa' => $idEstadoPoa,
                    'O.IdUnidadEjecutora' => $idUnidad,
                ])
                ->andWhere(['<>', 'IGO.CodigoEstado', Estado::ESTADO_ELIMINADO])
                ->asArray()
                ->all();
        }

        return ResponseHelper::success([
            'IdItem' => $item->IdItem,
            'IdGasto' => $descatalogado?->IdGasto,
            'IdFuente' => $item->IdFuente,
            'IdOrganismo' => $item->IdOrganismo,
            'IdFuenteUniversitaria' => $item->IdFuenteUniversitaria,
            'IdUnidadMedida' => $item->IdUnidadMedida,
            'Descripcion' => $item->Descripcion,
            'Precio' => $gestion?->PrecioUnitario ?? $item->PrecioReferencial,
            'Gasto' => $gasto?->getAttributes([
                'IdGasto',
                'CodigoGasto',
                'Descripcion',
                'EntidadTransferencia',
            ]),
            'asignaciones' => $asignaciones,
        ]);
    }

    public function eliminar(
        string $idItem,
        int $formulario,
        string $idGestion
    ): array {
        PoaEdicionHelper::asegurarEdicion();
        $item = $this->obtenerItemValidado($idItem, $formulario);
        $usuario = Yii::$app->user->identity->CodigoUsuario;
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $gestiones = ItemGestion::find()
                ->where(['IdItem' => $item->IdItem, 'IdGestion' => $idGestion])
                ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
                ->all();

            foreach ($gestiones as $gestion) {
                $asignaciones = ItemGestionOperacion::find()
                    ->where(['IdItem_Gestion' => $gestion->IdItem_Gestion])
                    ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
                    ->all();
                foreach ($asignaciones as $igo) {
                    $igo->eliminar();
                    $igo->CodigoUsuario = $usuario;
                    $this->guardarModelo($igo);
                }
                $gestion->eliminar();
                $gestion->CodigoUsuario = $usuario;
                $this->guardarModelo($gestion);
            }

            $item->eliminar();
            $item->CodigoUsuario = $usuario;
            $this->guardarModelo($item);
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        return ResponseHelper::success([], 'Ítem eliminado correctamente.');
    }

    private function validarGastoYRelaciones(ItemDescatalogadoForm $form): void
    {
        $gasto = Gasto::findOne($form->idGasto);
        if ($gasto === null || $gasto->CodigoEstado !== Estado::ESTADO_VIGENTE) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'El gasto no es válido.', 422);
        }
        if (!str_starts_with((string)$gasto->CodigoGasto, (string)$form->formulario)) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'El gasto no corresponde al formulario seleccionado.',
                422
            );
        }

        $partida = Partida::find()
            ->where([
                'IdFuente' => $form->idFuente,
                'IdOrganismo' => $form->idOrganismo,
                'CodigoEstado' => Estado::ESTADO_VIGENTE,
            ])
            ->one();
        if ($partida === null) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'No existe una partida vigente para la fuente y organismo seleccionados.',
                422
            );
        }

        $fuenteUniversitaria = FuenteUniversitaria::find()
            ->where([
                'IdFuenteUniversitaria' => $form->idFuenteUniversitaria,
                'IdPartida' => $partida->IdPartida,
                'CodigoEstado' => Estado::ESTADO_VIGENTE,
            ])
            ->one();
        if ($fuenteUniversitaria === null) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'La fuente universitaria no corresponde a la partida seleccionada.',
                422
            );
        }

        $unidad = UnidadMedida::findOne($form->idUnidadMedida);
        if ($unidad === null || $unidad->CodigoEstado !== Estado::ESTADO_VIGENTE) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'La unidad de medida no es válida.',
                422
            );
        }
    }

    private function operacionesPorItems(
        array $ids,
        string $idUnidad,
        string $idGestion,
        string $idEstadoPoa
    ): array {
        if ($ids === []) {
            return [];
        }

        $filas = ItemGestionOperacion::find()->alias('IGO')
            ->select([
                'IG.IdItem',
                'O.IdOperacion',
                'O.Codigo',
                'O.Descripcion',
                'IGO.Cantidad',
            ])
            ->innerJoin(['IG' => ItemGestion::tableName()], 'IG.IdItem_Gestion = IGO.IdItem_Gestion')
            ->innerJoin(['O' => Operacion::tableName()], 'O.IdOperacion = IGO.IdOperacion')
            ->where([
                'IG.IdItem' => $ids,
                'IG.IdGestion' => $idGestion,
                'IGO.IdEstadoPoa' => $idEstadoPoa,
                'O.IdUnidadEjecutora' => $idUnidad,
            ])
            ->andWhere(['<>', 'IGO.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['<>', 'IG.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['<>', 'O.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->orderBy(['O.Codigo' => SORT_ASC])
            ->asArray()
            ->all();

        $mapa = [];
        foreach ($filas as $fila) {
            $mapa[$fila['IdItem']][] = [
                'IdOperacion' => $fila['IdOperacion'],
                'Codigo' => $fila['Codigo'],
                'Descripcion' => $fila['Descripcion'],
                'Cantidad' => (float)$fila['Cantidad'],
            ];
        }
        return $mapa;
    }

    private function normalizarAsignaciones(array $asignaciones): array
    {
        $normalizadas = [];
        foreach ($asignaciones as $asignacion) {
            $idOperacion = trim((string)($asignacion['idOperacion'] ?? ''));
            $cantidad = round((float)($asignacion['cantidad'] ?? 0), 2);
            if ($idOperacion === '' || $cantidad <= 0) {
                continue;
            }
            $normalizadas[$idOperacion] = $cantidad;
        }
        if ($normalizadas === []) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'Debe ingresar cantidad en al menos una operación.',
                400
            );
        }
        return $normalizadas;
    }

    /**
     * @return Operacion[]
     */
    private function obtenerOperacionesValidas(
        array $ids,
        string $idUnidad,
        string $idGestion,
        string $idEstadoPoa
    ): array {
        $operaciones = Operacion::find()
            ->where([
                'IdOperacion' => $ids,
                'IdUnidadEjecutora' => $idUnidad,
                'IdGestion' => $idGestion,
                'IdEstadoPoa' => $idEstadoPoa,
                'CodigoEstado' => Estado::ESTADO_VIGENTE,
            ])
            ->indexBy('IdOperacion')
            ->all();

        if (count($operaciones) !== count($ids)) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'Una o más operaciones no pertenecen al contexto activo.',
                422
            );
        }
        return $operaciones;
    }

    /**
     * @param array<string, float> $asignaciones
     * @param Operacion[] $operaciones
     */
    private function validarTechos(
        array $asignaciones,
        array $operaciones,
        float $precio,
        string $idGestion,
        string $idEstadoPoa,
        ?string $excluirItem
    ): void {
        $montosPorLlave = [];
        foreach ($asignaciones as $idOperacion => $cantidad) {
            $llave = $operaciones[$idOperacion]->IdLlavePresupuestaria;
            $montosPorLlave[$llave] = ($montosPorLlave[$llave] ?? 0) + ($cantidad * $precio);
        }

        foreach ($montosPorLlave as $idLlave => $montoNuevo) {
            $techo = TechoUnidad::find()
                ->where([
                    'IdLlavePresupuestaria' => $idLlave,
                    'IdGestion' => $idGestion,
                    'CodigoEstado' => Estado::ESTADO_VIGENTE,
                ])
                ->one();
            if ($techo === null) {
                throw new ValidationException(
                    Yii::$app->params['ERROR_ENVIO_DATOS'],
                    'Una llave presupuestaria no tiene techo asignado.',
                    422
                );
            }
            $usado = PresupuestoConsumoDao::totalPorLlave(
                $idLlave,
                $idGestion,
                $idEstadoPoa,
                $excluirItem
            );
            if (($usado + $montoNuevo) > (float)$techo->Techo) {
                throw new ValidationException(
                    Yii::$app->params['ERROR_ENVIO_DATOS'],
                    'El monto total de ítems supera el techo asignado a la llave presupuestaria.',
                    422
                );
            }
        }
    }

    private function obtenerItemValidado(string $idItem, int $formulario): Item
    {
        $item = Item::listOne($idItem);
        if ($item === null || (int)$item->Formulario !== $formulario) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],
                'No se encontró el ítem solicitado.',
                404
            );
        }
        return $item;
    }

    private function guardarModelo($modelo): void
    {
        if (!$modelo->validate()) {
            throw new ValidationException(
                Yii::$app->params['ERROR_VALIDACION_MODELO'],
                $modelo->getErrors(),
                422
            );
        }
        if (!$modelo->save(false)) {
            throw new ValidationException(
                Yii::$app->params['ERROR_EJECUCION_SQL'],
                $modelo->getErrors(),
                500
            );
        }
    }
}

