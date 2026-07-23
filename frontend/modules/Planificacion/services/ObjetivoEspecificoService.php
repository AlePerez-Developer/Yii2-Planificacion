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
        string $idLlavePresupuestaria,
        string $idGestion
    ): array {
        $modelo = new ObjetivoEspecifico([
            'IdObjInstitucional' => $form->idObjInstitucional,
            'IdLlavePresupuestaria' => $idLlavePresupuestaria,
            'Codigo' => $form->codigo,
            'Objetivo' => mb_strtoupper($form->objetivo, 'UTF-8'),
            'Producto' => mb_strtoupper($form->producto, 'UTF-8'),
            'Indicador_Formula' => mb_strtoupper($form->formula, 'UTF-8'),
            'Indicador_Descripcion' => mb_strtoupper($form->descripcion, 'UTF-8'),
            'IdGestion' => $idGestion,
            'CodigoEstado' => Estado::ESTADO_VIGENTE,
            'CodigoUsuario' => Yii::$app->user->identity->CodigoUsuario,
        ]);

        return $this->procesar($modelo);
    }

    public function actualizar(
        string $id,
        ObjetivoEspecificoForm $form,
        string $idLlavePresupuestaria,
        string $gestion
    ): array {
        $modelo = $this->obtenerModeloValidado($id, $idLlavePresupuestaria, $gestion);
        $modelo->setAttributes([
            'IdObjInstitucional' => $form->idObjInstitucional,
            'IdLlavePresupuestaria' => $idLlavePresupuestaria,
            'IdGestion' => $gestion,
            'Codigo' => $form->codigo,
            'Objetivo' => mb_strtoupper($form->objetivo, 'UTF-8'),
            'Producto' => mb_strtoupper($form->producto, 'UTF-8'),
            'Indicador_Formula' => mb_strtoupper($form->formula, 'UTF-8'),
            'Indicador_Descripcion' => mb_strtoupper($form->descripcion, 'UTF-8'),
        ]);

        return $this->procesar($modelo);
    }

    public function cambiarEstado(string $id, string $idLlavePresupuestaria, string $gestion): array
    {
        $modelo = $this->obtenerModeloValidado($id, $idLlavePresupuestaria, $gestion);
        $modelo->cambiarEstado();
        $this->guardarModelo($modelo);

        return ['message' => Yii::$app->params['PROCESO_CORRECTO'], 'data' => $modelo->CodigoEstado];
    }

    public function eliminar(string $id, string $idLlavePresupuestaria, string  $gestion): array
    {
        $modelo = $this->obtenerModeloValidado($id, $idLlavePresupuestaria, $gestion);

        if (ObjEspecificoDao::enUso($modelo)) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_EN_USO'],
                'El objetivo específico tiene indicadores POA relacionados.',
                409
            );
        }

        $modelo->eliminar();
        return $this->procesar($modelo);
    }

    public function obtenerModelo(string $id, string $idLlavePresupuestaria, string $idGestion): array
    {
        $modelo = $this->obtenerModeloValidado($id, $idLlavePresupuestaria, $idGestion);

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $modelo->getAttributes([
                'IdObjEspecifico', 'IdObjInstitucional', 'Codigo', 'Objetivo', 'Producto', 'Indicador_Formula', 'Indicador_Descripcion',
            ]),
        ];
    }

    public function verificarCodigo(
        string $id,
        string $idObjInstitucional,
        string $idLlavePresupuestaria,
        int $gestion,
        string $codigo
    ): bool {
        return ObjEspecificoDao::verificarCodigo(
            $id, $idObjInstitucional, $idLlavePresupuestaria, $gestion, $codigo
        );
    }

    private function obtenerModeloValidado(
        string $id,
        string $idLlavePresupuestaria,
        string $idGestion
    ): ObjetivoEspecifico {
        $modelo = ObjetivoEspecifico::listAll()
            ->andWhere(['OE.IdObjEspecifico' => $id])
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
