<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\dao\PresupuestoConsumoDao;
use app\modules\Planificacion\formModels\ItemCatalogadoForm;
use app\modules\Planificacion\models\CatalogoSigma;
use app\modules\Planificacion\models\Fuente;
use app\modules\Planificacion\models\ItemCatalogado;
use app\modules\Planificacion\models\Operacion;
use app\modules\Planificacion\models\Organismo;
use app\modules\Planificacion\models\TechoUnidad;
use common\models\Estado;
use Yii;
use yii\db\Expression;

class ItemCatalogadoService
{
    public function listarOperaciones(
        string $idUnidad,
        string $idGestion,
        string $idEstadoPoa,
        int $codigoEstadoPoa,
        int $formulario
    ): array {
        $operaciones = Operacion::listAll($idUnidad, $idGestion, $codigoEstadoPoa)
            ->andWhere(['O.CodigoEstado' => Estado::ESTADO_VIGENTE])
            ->andWhere(['OE.IdGestion' => $idGestion])
            ->orderBy(['O.Codigo' => SORT_ASC])
            ->asArray()
            ->all();

        $totales = ItemCatalogado::find()->alias('IC')
            ->select([
                'IC.IdOperacion',
                'TotalProgramado' => new Expression(
                    'SUM(CAST(IC.cantidad AS decimal(18,2)) * CAST(IC.Precio AS decimal(18,2)))'
                ),
            ])
            ->where([
                'IC.IdGestion' => $idGestion,
                'IC.IdEstadoPoa' => $idEstadoPoa,
                'IC.formulario' => $formulario,
                'IC.CodigoEstado' => Estado::ESTADO_VIGENTE,
            ])
            ->groupBy(['IC.IdOperacion'])
            ->indexBy('IdOperacion')
            ->asArray()
            ->all();

        foreach ($operaciones as &$operacion) {
            $operacion['TotalProgramado'] = (float)($totales[$operacion['IdOperacion']]['TotalProgramado'] ?? 0);
        }
        unset($operacion);

        return ResponseHelper::success($operaciones);
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
        $data = ItemCatalogado::find()->alias('IC')
            ->select([
                'IC.*',
                'Clase' => 'CS.Clase',
                'DescripcionSigma' => 'CS.Descripcion',
                'RamaComercial' => 'CS.RamaComercial',
                'Especificacion' => 'CS.Especificacion',
                'PrecioReferencial' => 'CS.PrecioReferencial',
                'FuenteDescripcion' => 'F.Descripcion',
                'OrganismoDescripcion' => 'ORG.Descripcion',
            ])
            ->innerJoin(['CS' => CatalogoSigma::tableName()], 'CS.IdSigma = IC.IdSigma')
            ->innerJoin(['F' => Fuente::tableName()], 'F.IdFuente = IC.IdFuente')
            ->innerJoin(['ORG' => Organismo::tableName()], 'ORG.IdFuente = IC.IdFuente AND ORG.IdOrganismo = IC.IdOrganismo')
            ->where([
                'IC.IdOperacion' => $idOperacion,
                'IC.IdGestion' => $idGestion,
                'IC.IdEstadoPoa' => $idEstadoPoa,
                'IC.formulario' => $formulario,
            ])
            ->andWhere(['<>', 'IC.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->orderBy(['IC.FechaHoraRegistro' => SORT_DESC])
            ->asArray()
            ->all();

        return ResponseHelper::success($data);
    }

    public function listarSigma(): array
    {
        return ResponseHelper::success(CatalogoSigma::find()
            ->select([
                'id' => 'IdSigma',
                'text' => 'Descripcion',
                'Clase',
                'Descripcion',
                'RamaComercial',
                'Especificacion',
                'IdGasto',
                'PrecioReferencial',
            ])
            ->where(['CodigoEstado' => Estado::ESTADO_VIGENTE])
            ->orderBy(['Descripcion' => SORT_ASC])
            ->asArray()
            ->all());
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

    public function guardar(
        ItemCatalogadoForm $form,
        ?string $id,
        string $idUnidad,
        string $idGestion,
        string $idEstadoPoa,
        int $codigoEstadoPoa
    ): array {
        $operacion = $this->obtenerOperacion($form->idOperacion, $idUnidad, $idGestion, $codigoEstadoPoa);
        $sigma = CatalogoSigma::findOne($form->idSigma);
        if ($sigma === null || $sigma->CodigoEstado !== Estado::ESTADO_VIGENTE) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'El ítem SIGMA no es válido.', 422);
        }

        $modelo = $id
            ? $this->obtenerItem($id, $form->idOperacion, $form->formulario, $idGestion, $idEstadoPoa)
            : new ItemCatalogado();
        $costoNuevo = $form->cantidad * $form->precio;
        $usado = PresupuestoConsumoDao::totalPorLlave(
            $operacion->IdLlavePresupuestaria,
            $idGestion,
            $idEstadoPoa,
            $id
        );
        $this->validarTecho($operacion->IdLlavePresupuestaria, $idGestion, $usado + $costoNuevo);

        $modelo->setAttributes([
            'IdOperacion' => $form->idOperacion,
            'IdSigma' => $form->idSigma,
            'IdEstadoPoa' => $idEstadoPoa,
            'IdGestion' => $idGestion,
            'IdFuente' => $form->idFuente,
            'IdOrganismo' => $form->idOrganismo,
            'cantidad' => $form->cantidad,
            'Precio' => $form->precio,
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

    protected function obtenerOperacion(
        string $id,
        string $idUnidad,
        string $idGestion,
        int $idEstadoPoa
    ): Operacion
    {
        $modelo = Operacion::find()->alias('O')
            ->innerJoin(['OE' => \app\modules\Planificacion\models\ObjetivoEspecifico::tableName()], 'OE.IdObjEspecifico = O.IdObjEspecifico')
            ->where([
                'O.IdOperacion' => $id,
                'O.IdUnidadEjecutora' => $idUnidad,
                'O.IdGestion' => $idGestion,
                'O.IdEstadoPoa' => $idEstadoPoa,
                'OE.IdGestion' => $idGestion,
            ])
            ->one();
        if ($modelo === null || $modelo->CodigoEstado === Estado::ESTADO_ELIMINADO) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'La operación no pertenece al contexto activo.', 404);
        }
        return $modelo;
    }

    private function obtenerItem(
        string $id,
        string $idOperacion,
        int $formulario,
        string $idGestion,
        string $idEstadoPoa
    ): ItemCatalogado {
        $modelo = ItemCatalogado::findOne([
            'IdItemCatalogado' => $id,
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

    protected function validarTecho(string $idLlave, string $idGestion, float $monto): void
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
}
