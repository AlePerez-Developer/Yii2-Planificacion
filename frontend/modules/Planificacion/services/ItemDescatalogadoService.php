<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\dao\PresupuestoConsumoDao;
use app\modules\Planificacion\formModels\ItemDescatalogadoForm;
use app\modules\Planificacion\models\Fuente;
use app\modules\Planificacion\models\Gasto;
use app\modules\Planificacion\models\ItemDescatalogado;
use app\modules\Planificacion\models\Operacion;
use app\modules\Planificacion\models\Organismo;
use app\modules\Planificacion\models\TechoUnidad;
use common\models\Estado;
use Yii;
use yii\db\Expression;

class ItemDescatalogadoService
{
    public function listarOperaciones(
        string $idUnidad,
        string $idGestion,
        string $idEstadoPoa,
        int $codigoEstadoPoa,
        int $formulario
    ): array {
        $operaciones = Operacion::listAll($idUnidad, $codigoEstadoPoa)
            ->andWhere(['O.CodigoEstado' => Estado::ESTADO_VIGENTE])
            ->andWhere(['OE.IdGestion' => $idGestion])
            ->orderBy(['O.Codigo' => SORT_ASC])
            ->asArray()
            ->all();

        $totales = ItemDescatalogado::find()->alias('ID')
            ->select([
                'ID.IdOperacion',
                'TotalProgramado' => new Expression(
                    'SUM(CAST(ID.cantidad AS decimal(18,2)) * CAST(ID.Precio AS decimal(18,2)))'
                ),
            ])
            ->where([
                'ID.IdGestion' => $idGestion,
                'ID.IdEstadoPoa' => $idEstadoPoa,
                'ID.formulario' => $formulario,
                'ID.CodigoEstado' => Estado::ESTADO_VIGENTE,
            ])
            ->groupBy(['ID.IdOperacion'])
            ->indexBy('IdOperacion')
            ->asArray()
            ->all();

        foreach ($operaciones as &$operacion) {
            $operacion['TotalProgramado'] = (float)($totales[$operacion['IdOperacion']]['TotalProgramado'] ?? 0);
        }
        unset($operacion);

        return ResponseHelper::success($operaciones);
    }

    public function listarFuentes(): array
    {
        return ResponseHelper::success(Fuente::find()
            ->select(['id' => 'IdFuente', 'text' => 'Descripcion'])
            ->orderBy(['IdFuente' => SORT_ASC])
            ->asArray()->all());
    }

    public function listarOrganismos(string $idFuente): array
    {
        return ResponseHelper::success(Organismo::find()
            ->select(['id' => 'IdOrganismo', 'text' => 'Descripcion'])
            ->where(['IdFuente' => $idFuente])
            ->orderBy(['IdOrganismo' => SORT_ASC])
            ->asArray()->all());
    }

    public function listarItems(
        string $idOperacion,
        int $formulario,
        string $idUnidad,
        string $idGestion,
        string $idEstadoPoa,
        int $codigoEstadoPoa
    ): array {
        $this->obtenerOperacion($idOperacion, $idUnidad, $idGestion, $codigoEstadoPoa);
        $data = ItemDescatalogado::find()->alias('ID')
            ->select([
                'ID.*',
                'GastoDescripcion' => 'G.Descripcion',
                'EntidadTransferencia' => 'G.EntidadTransferencia',
                'FuenteDescripcion' => 'F.Descripcion',
                'OrganismoDescripcion' => 'ORG.Descripcion',
            ])
            ->innerJoin(['G' => Gasto::tableName()], 'G.CodigoGasto = ID.IdGasto')
            ->innerJoin(['F' => Fuente::tableName()], 'F.IdFuente = ID.IdFuente')
            ->innerJoin(['ORG' => Organismo::tableName()], 'ORG.IdFuente = ID.IdFuente AND ORG.IdOrganismo = ID.IdOrganismo')
            ->where([
                'ID.IdOperacion' => $idOperacion,
                'ID.IdGestion' => $idGestion,
                'ID.IdEstadoPoa' => $idEstadoPoa,
                'ID.formulario' => $formulario,
            ])
            ->andWhere(['<>', 'ID.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->orderBy(['ID.FechaHoraRegistro' => SORT_DESC])
            ->asArray()->all();

        return ResponseHelper::success($data);
    }

    public function listarGastos(): array
    {
        return ResponseHelper::success(Gasto::find()
            ->select([
                'id' => 'CodigoGasto',
                'text' => 'Descripcion',
                'EntidadTransferencia',
            ])
            ->where(['CodigoEstado' => Estado::ESTADO_VIGENTE])
            ->orderBy(['CodigoGasto' => SORT_ASC])
            ->asArray()->all());
    }

    public function guardar(
        ItemDescatalogadoForm $form,
        ?string $id,
        string $idUnidad,
        string $idGestion,
        string $idEstadoPoa,
        int $codigoEstadoPoa
    ): array {
        $operacion = $this->obtenerOperacion($form->idOperacion, $idUnidad, $idGestion, $codigoEstadoPoa);
        $modelo = $id
            ? $this->obtenerItem($id, $form->idOperacion, $form->formulario, $idGestion, $idEstadoPoa)
            : new ItemDescatalogado();
        $usado = PresupuestoConsumoDao::totalPorLlave(
            $operacion->IdLlavePresupuestaria,
            $idGestion,
            $idEstadoPoa,
            null,
            $id
        );
        $this->validarTecho(
            $operacion->IdLlavePresupuestaria,
            $idGestion,
            $usado + ($form->cantidad * $form->precio)
        );

        $modelo->setAttributes([
            'IdOperacion' => $form->idOperacion,
            'IdEstadoPoa' => $idEstadoPoa,
            'IdGestion' => $idGestion,
            'IdGasto' => $form->idGasto,
            'IdFuente' => $form->idFuente,
            'IdOrganismo' => $form->idOrganismo,
            'cantidad' => $form->cantidad,
            'Precio' => $form->precio,
            'Descripcion' => $this->normalizarDescripcion($form->descripcion),
            'formulario' => $form->formulario,
            'CodigoEstado' => Estado::ESTADO_VIGENTE,
            'CodigoUsuario' => Yii::$app->user->identity->CodigoUsuario,
        ]);

        if (!$modelo->save()) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], json_encode($modelo->errors), 422);
        }
        return ResponseHelper::success([], 'Ítem guardado correctamente.');
    }

    public function eliminar(
        string $id,
        string $idOperacion,
        int $formulario,
        string $idGestion,
        string $idEstadoPoa
    ): array {
        $modelo = $this->obtenerItem($id, $idOperacion, $formulario, $idGestion, $idEstadoPoa);
        $modelo->CodigoEstado = Estado::ESTADO_ELIMINADO;
        $modelo->CodigoUsuario = Yii::$app->user->identity->CodigoUsuario;
        if (!$modelo->save(false)) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'No se pudo eliminar el ítem.', 422);
        }
        return ResponseHelper::success([], 'Ítem eliminado.');
    }

    private function obtenerOperacion(
        string $id,
        string $idUnidad,
        string $idGestion,
        int $idEstadoPoa
    ): Operacion {
        $modelo = Operacion::find()->alias('O')
            ->innerJoin(['OE' => \app\modules\Planificacion\models\ObjetivoEspecifico::tableName()], 'OE.IdObjEspecifico = O.IdObjEspecifico')
            ->where([
                'O.IdOperacion' => $id,
                'O.IdUnidadEjecutora' => $idUnidad,
                'O.IdEstadoPoa' => $idEstadoPoa,
                'OE.IdGestion' => $idGestion,
            ])
            ->one();
        if ($modelo === null || $modelo->CodigoEstado === Estado::ESTADO_ELIMINADO) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'La operación no pertenece al contexto activo.', 404);
        }
        return $modelo;
    }

    private function validarTecho(string $idLlave, string $idGestion, float $monto): void
    {
        $techo = TechoUnidad::find()
            ->where([
                'IdLlavePresupuestaria' => $idLlave,
                'IdGestion' => $idGestion,
                'CodigoEstado' => Estado::ESTADO_VIGENTE,
            ])
            ->one();
        if ($techo === null) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'La llave presupuestaria no tiene techo asignado.', 422);
        }
        if ($monto > (float)$techo->Techo) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'El monto total de ítems supera el techo asignado a la llave presupuestaria.',
                422
            );
        }
    }

    private function normalizarDescripcion(?string $descripcion): ?string
    {
        $descripcion = trim((string)$descripcion);
        return $descripcion === '' ? null : $descripcion;
    }

    private function obtenerItem(
        string $id,
        string $idOperacion,
        int $formulario,
        string $idGestion,
        string $idEstadoPoa
    ): ItemDescatalogado {
        $modelo = ItemDescatalogado::findOne([
            'IdItemDescatalogado' => $id,
            'IdOperacion' => $idOperacion,
            'IdGestion' => $idGestion,
            'IdEstadoPoa' => $idEstadoPoa,
            'formulario' => $formulario,
        ]);
        if ($modelo === null || $modelo->CodigoEstado === Estado::ESTADO_ELIMINADO) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'Ítem no encontrado.', 404);
        }
        return $modelo;
    }
}
