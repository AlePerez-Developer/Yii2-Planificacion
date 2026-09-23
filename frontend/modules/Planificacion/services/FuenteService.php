<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\dao\FuenteDao;
use app\modules\Planificacion\formModels\FuenteForm;
use app\modules\Planificacion\models\Fuente;
use common\models\Estado;
use Yii;

class FuenteService
{
    public function listarTodo(): array
    {
        return ResponseHelper::success(Fuente::listAll()->asArray()->all(), 'Listado de fuentes obtenido.');
    }

    public function listarS2(): array
    {
        $data = Fuente::find()
            ->select(['id' => 'IdFuente', 'text' => 'Descripcion'])
            ->where(['CodigoEstado' => Estado::ESTADO_VIGENTE])
            ->orderBy(['Descripcion' => SORT_ASC])
            ->asArray()
            ->all();

        return ResponseHelper::success($data);
    }

    public function guardar(FuenteForm $form): array
    {
        $modelo = new Fuente([
            'Descripcion' => mb_strtoupper(trim($form->descripcion), 'UTF-8'),
            'CodigoEstado' => Estado::ESTADO_VIGENTE,
            'CodigoUsuario' => Yii::$app->user->identity->CodigoUsuario,
        ]);
        return $this->procesar($modelo);
    }

    public function actualizar(string $id, FuenteForm $form): array
    {
        $modelo = $this->obtenerModeloValidado($id);
        $modelo->Descripcion = mb_strtoupper(trim($form->descripcion), 'UTF-8');
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
        if (FuenteDao::enUso($modelo)) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_EN_USO'],
                'La fuente se encuentra en uso y no puede ser eliminada.',
                500
            );
        }
        $modelo->eliminar();
        return $this->procesar($modelo);
    }

    public function obtenerModelo(string $id): array
    {
        $modelo = $this->obtenerModeloValidado($id);
        return ResponseHelper::success($modelo->getAttributes(['IdFuente', 'Descripcion']));
    }

    private function obtenerModeloValidado(string $id): Fuente
    {
        $modelo = Fuente::listOne($id);
        if ($modelo === null) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],
                'No se encontró la fuente solicitada.',
                404
            );
        }
        return $modelo;
    }

    private function procesar(Fuente $modelo): array
    {
        $this->guardarModelo($modelo);
        return ResponseHelper::success($modelo, 'Fuente procesada correctamente.');
    }

    private function guardarModelo(Fuente $modelo): void
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
