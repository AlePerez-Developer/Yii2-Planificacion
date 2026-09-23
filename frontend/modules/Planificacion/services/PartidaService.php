<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\dao\PartidaDao;
use app\modules\Planificacion\formModels\PartidaForm;
use app\modules\Planificacion\models\Fuente;
use app\modules\Planificacion\models\Organismo;
use app\modules\Planificacion\models\Partida;
use common\models\Estado;
use Yii;

class PartidaService
{
    public function listarTodo(): array
    {
        return ResponseHelper::success(Partida::listAll()->asArray()->all(), 'Listado de partidas obtenido.');
    }

    public function guardar(PartidaForm $form): array
    {
        $this->validarRelaciones($form);
        $modelo = new Partida([
            'IdFuente' => $form->idFuente,
            'IdOrganismo' => $form->idOrganismo,
            'CodigoEstado' => Estado::ESTADO_VIGENTE,
            'CodigoUsuario' => Yii::$app->user->identity->CodigoUsuario,
        ]);
        return $this->procesar($modelo);
    }

    public function actualizar(string $id, PartidaForm $form): array
    {
        $this->validarRelaciones($form);
        $modelo = $this->obtenerModeloValidado($id);
        $modelo->IdFuente = $form->idFuente;
        $modelo->IdOrganismo = $form->idOrganismo;
        $modelo->CodigoUsuario = Yii::$app->user->identity->CodigoUsuario;
        return $this->procesar($modelo);
    }

    public function cambiarEstado(string $id): array
    {
        $modelo = $this->obtenerModeloValidado($id);
        $modelo->cambiarEstado();
        $this->guardarModelo($modelo);
        return ResponseHelper::success($modelo->CodigoEstado, 'Estado actualizado.');
    }

    public function eliminar(string $id): array
    {
        $modelo = $this->obtenerModeloValidado($id);
        if (PartidaDao::enUso($modelo)) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_EN_USO'],
                'La partida se encuentra en uso y no puede ser eliminada.',
                500
            );
        }
        $modelo->eliminar();
        return $this->procesar($modelo);
    }

    public function obtenerModelo(string $id): array
    {
        $modelo = $this->obtenerModeloValidado($id);
        return ResponseHelper::success($modelo->getAttributes([
            'IdPartida',
            'IdFuente',
            'IdOrganismo',
        ]));
    }

    public function verificarCombinacion(string $id, string $idFuente, string $idOrganismo): bool
    {
        return PartidaDao::verificarCombinacion($id, $idFuente, $idOrganismo);
    }

    private function validarRelaciones(PartidaForm $form): void
    {
        $fuenteValida = Fuente::find()
            ->where([
                'IdFuente' => $form->idFuente,
                'CodigoEstado' => Estado::ESTADO_VIGENTE,
            ])
            ->exists();
        $organismoValido = Organismo::find()
            ->where([
                'IdOrganismo' => $form->idOrganismo,
                'CodigoEstado' => Estado::ESTADO_VIGENTE,
            ])
            ->exists();

        if (!$fuenteValida || !$organismoValido) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'La fuente o el organismo no son válidos.',
                400
            );
        }
    }

    private function obtenerModeloValidado(string $id): Partida
    {
        $modelo = Partida::listOne($id);
        if ($modelo === null) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],
                'No se encontró la partida solicitada.',
                404
            );
        }
        return $modelo;
    }

    private function procesar(Partida $modelo): array
    {
        $this->guardarModelo($modelo);
        return ResponseHelper::success($modelo, 'Partida procesada correctamente.');
    }

    private function guardarModelo(Partida $modelo): void
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
