<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\dao\OrganismoDao;
use app\modules\Planificacion\formModels\OrganismoForm;
use app\modules\Planificacion\models\Organismo;
use common\models\Estado;
use Yii;

class OrganismoService
{
    public function listarTodo(): array
    {
        return ResponseHelper::success(Organismo::listAll()->asArray()->all(), 'Listado de organismos obtenido.');
    }

    public function listarS2(): array
    {
        $data = Organismo::find()
            ->select(['id' => 'IdOrganismo', 'text' => 'Descripcion'])
            ->where(['CodigoEstado' => Estado::ESTADO_VIGENTE])
            ->orderBy(['Descripcion' => SORT_ASC])
            ->asArray()
            ->all();

        return ResponseHelper::success($data);
    }

    public function guardar(OrganismoForm $form): array
    {
        $modelo = new Organismo([
            'Descripcion' => mb_strtoupper(trim($form->descripcion), 'UTF-8'),
            'CodigoEstado' => Estado::ESTADO_VIGENTE,
            'CodigoUsuario' => Yii::$app->user->identity->CodigoUsuario,
        ]);
        return $this->procesar($modelo);
    }

    public function actualizar(string $id, OrganismoForm $form): array
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
        if (OrganismoDao::enUso($modelo)) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_EN_USO'],
                'El organismo se encuentra en uso y no puede ser eliminado.',
                500
            );
        }
        $modelo->eliminar();
        return $this->procesar($modelo);
    }

    public function obtenerModelo(string $id): array
    {
        $modelo = $this->obtenerModeloValidado($id);
        return ResponseHelper::success($modelo->getAttributes(['IdOrganismo', 'Descripcion']));
    }

    private function obtenerModeloValidado(string $id): Organismo
    {
        $modelo = Organismo::listOne($id);
        if ($modelo === null) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],
                'No se encontró el organismo solicitado.',
                404
            );
        }
        return $modelo;
    }

    private function procesar(Organismo $modelo): array
    {
        $this->guardarModelo($modelo);
        return ResponseHelper::success($modelo, 'Organismo procesado correctamente.');
    }

    private function guardarModelo(Organismo $modelo): void
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
