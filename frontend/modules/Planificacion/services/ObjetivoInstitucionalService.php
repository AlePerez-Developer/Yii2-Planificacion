<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\dao\ObjInstitucionalDao;
use app\modules\Planificacion\formModels\ObjetivoInstitucionalForm;
use app\modules\Planificacion\models\ObjetivoInstitucional;
use common\models\Estado;
use Yii;
use yii\db\Exception;

class ObjetivoInstitucionalService
{
    public function listarTodo(): array
    {
        $data = ObjetivoInstitucional::listAll()
            ->orderBy(['Compuesto' => SORT_ASC])
            ->asArray()
            ->all();

        return ResponseHelper::success($data, 'Listado de objetivos institucionales obtenido.');
    }

    public function listarUno(string $id): ?ObjetivoInstitucional
    {
        return ObjetivoInstitucional::listOne($id);
    }

    /**
     * @throws ValidationException
     * @throws Exception
     */
    public function guardar(ObjetivoInstitucionalForm $form): array
    {
        $modelo = new ObjetivoInstitucional([
            'IdObjEstrategico' => $form->idObjEstrategico,
            'Codigo' => $form->codigo,
            'Objetivo' => mb_strtoupper($form->objetivo, 'UTF-8'),
            'Producto' => mb_strtoupper($form->producto, 'UTF-8'),
            'Gestion' => $form->gestion,
            'CodigoEstado' => Estado::ESTADO_VIGENTE,
            'CodigoUsuario' => Yii::$app->user->identity->CodigoUsuario,
        ]);

        return $this->procesarModelo($modelo);
    }

    /**
     * @throws ValidationException
     * @throws Exception
     */
    public function actualizar(string $id, ObjetivoInstitucionalForm $form): array
    {
        $modelo = $this->obtenerModeloValidado($id);

        $modelo->setAttributes([
            'IdObjEstrategico' => $form->idObjEstrategico,
            'Codigo' => $form->codigo,
            'Objetivo' => mb_strtoupper($form->objetivo, 'UTF-8'),
            'Producto' => mb_strtoupper($form->producto, 'UTF-8'),
            'Gestion' => $form->gestion,
        ]);

        return $this->procesarModelo($modelo);
    }

    /**
     * @throws ValidationException
     * @throws Exception
     */
    public function cambiarEstado(string $id): array
    {
        $modelo = $this->obtenerModeloValidado($id);
        $modelo->cambiarEstado();
        $this->guardarModelo($modelo);

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $modelo->CodigoEstado,
        ];
    }

    /**
     * @throws ValidationException
     * @throws Exception
     */
    public function eliminar(string $id): array
    {
        $modelo = $this->obtenerModeloValidado($id);

        if (ObjInstitucionalDao::enUso($modelo)) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_EN_USO'],
                'El objetivo institucional se encuentra en uso.',
                409
            );
        }

        $modelo->eliminar();
        return $this->procesarModelo($modelo);
    }

    /**
     * @throws ValidationException
     */
    public function obtenerModelo(string $id): array
    {
        $modelo = $this->obtenerModeloValidado($id);

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $modelo->getAttributes([
                'IdObjInstitucional',
                'IdObjEstrategico',
                'Codigo',
                'Objetivo',
                'Producto',
                'Gestion',
            ]),
        ];
    }

    public function verificarCodigo(string $id, string $idObjEstrategico, string $codigo): bool
    {
        return ObjInstitucionalDao::verificarCodigo($id, $idObjEstrategico, $codigo);
    }

    /**
     * @throws ValidationException
     */
    private function obtenerModeloValidado(string $id): ObjetivoInstitucional
    {
        $modelo = $this->listarUno($id);

        if ($modelo === null) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],
                'No se encontró el objetivo institucional solicitado.',
                404
            );
        }

        return $modelo;
    }

    /**
     * @throws ValidationException
     * @throws Exception
     */
    private function procesarModelo(ObjetivoInstitucional $modelo): array
    {
        $this->guardarModelo($modelo);

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => '',
        ];
    }

    /**
     * @throws ValidationException
     * @throws Exception
     */
    private function guardarModelo(ObjetivoInstitucional $modelo): void
    {
        if (!$modelo->validate()) {
            throw new ValidationException(
                Yii::$app->params['ERROR_VALIDACION_MODELO'],
                $modelo->getErrors(),
                422
            );
        }

        if (!$modelo->save(false)) {
            Yii::error("Error al guardar el objetivo institucional {$modelo->Codigo}", __METHOD__);
            throw new ValidationException(
                Yii::$app->params['ERROR_EJECUCION_SQL'],
                $modelo->getErrors(),
                500
            );
        }
    }
}
