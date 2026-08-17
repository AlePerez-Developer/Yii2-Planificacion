<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\dao\ObjEspecificoDao;
use app\modules\Planificacion\formModels\ObjetivoEspecificoForm;
use app\modules\Planificacion\models\ObjetivoEspecifico;
use common\models\Estado;
use Yii;

class ObjetivoEspecificoService
{
    public function listarTodo(): array
    {
        $data = ObjetivoEspecifico::listAll()
            ->orderBy(['Compuesto' => SORT_ASC])
            ->asArray()
            ->all();

        return ResponseHelper::success($data, 'Listado de objetivos específicos obtenido.');
    }

    /**
     * Lista un array de Áreas Estrategicas no eliminados
     * @param string $search
     * @return array of ObjInstitucionales
     */
    public function listarObjEspecificosS2(string $search): array
    {
        $data = ObjetivoEspecifico::listAll()
            ->orderBy(['Compuesto' => SORT_ASC])
            ->asArray()->all();

        return ResponseHelper::success($data, 'Listado de Objetivos especificos obtenido.');
    }

    public function guardar(
        ObjetivoEspecificoForm $form,
        string $idUnidadEjecutora,
        string $idGestion
    ): array {
        $modelo = new ObjetivoEspecifico([
            'IdObjInstitucional' => $form->idObjInstitucional,
            'IdUnidadEjecutora' => $idUnidadEjecutora,
            'Codigo' => $form->codigo,
            'Objetivo' => mb_strtoupper($form->objetivo, 'UTF-8'),
            'IdGestion' => $idGestion,
            'CodigoEstado' => Estado::ESTADO_VIGENTE,
            'CodigoUsuario' => Yii::$app->user->identity->CodigoUsuario,
        ]);

        return $this->procesar($modelo);
    }

    public function actualizar(
        string $id,
        ObjetivoEspecificoForm $form,
        string $idUnidadEjecutora,
        string $idGestion
    ): array {
        $modelo = $this->obtenerModeloValidado($id, $idUnidadEjecutora, $idGestion);
        $modelo->setAttributes([
            'IdObjInstitucional' => $form->idObjInstitucional,
            'IdUnidadEjecutora' => $idUnidadEjecutora,
            'IdGestion' => $idGestion,
            'Codigo' => $form->codigo,
            'Objetivo' => mb_strtoupper($form->objetivo, 'UTF-8'),
        ]);

        return $this->procesar($modelo);
    }

    public function cambiarEstado(string $id, string $idUnidadEjecutora, string $idGestion): array
    {
        $modelo = $this->obtenerModeloValidado($id, $idUnidadEjecutora, $idGestion);
        $modelo->cambiarEstado();
        $this->guardarModelo($modelo);

        return ['message' => Yii::$app->params['PROCESO_CORRECTO'], 'data' => $modelo->CodigoEstado];
    }

    public function eliminar(string $id, string $idUnidadEjecutora, string $idGestion): array
    {
        $modelo = $this->obtenerModeloValidado($id, $idUnidadEjecutora, $idGestion);

        if (ObjEspecificoDao::enUso($modelo)) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_EN_USO'],
                'El objetivo específico se encuentra en uso.',
                409
            );
        }

        $modelo->eliminar();
        return $this->procesar($modelo);
    }

    public function obtenerModelo(string $id, string $idUnidadEjecutora, string $idGestion): array
    {
        $modelo = $this->obtenerModeloValidado($id, $idUnidadEjecutora, $idGestion);

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $modelo->getAttributes([
                'IdObjEspecifico', 'IdObjInstitucional', 'Codigo', 'Objetivo',
            ]),
        ];
    }

    public function verificarCodigo(
        string $id,
        string $idObjInstitucional,
        string $idUnidadEjecutora,
        string $idGestion,
        string $codigo
    ): bool {
        return ObjEspecificoDao::verificarCodigo(
            $id, $idObjInstitucional, $idUnidadEjecutora, $idGestion, $codigo
        );
    }

    private function obtenerModeloValidado(
        string $id,
        string $idUnidadEjecutora,
        string $idGestion
    ): ObjetivoEspecifico {
        $modelo = ObjetivoEspecifico::listAll()
            ->andWhere([
                'Oe.IdObjEspecifico' => $id,
                'Oe.IdUnidadEjecutora' => $idUnidadEjecutora,
                'Oe.IdGestion' => $idGestion,
            ])
            ->one();

        if ($modelo === null) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],
                'No se encontró el objetivo específico solicitado.',
                404
            );
        }

        return $modelo;
    }

    private function procesar(ObjetivoEspecifico $modelo): array
    {
        $this->guardarModelo($modelo);
        return ['message' => Yii::$app->params['PROCESO_CORRECTO'], 'data' => ''];
    }

    private function guardarModelo(ObjetivoEspecifico $modelo): void
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
