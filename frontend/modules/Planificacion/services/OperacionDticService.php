<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\dao\OperacionDticDao;
use app\modules\Planificacion\formModels\OperacionDticForm;
use app\modules\Planificacion\models\IndicadorEstrategico;
use app\modules\Planificacion\models\IndicadorPoa;
use app\modules\Planificacion\models\ObjetivoEspecifico;
use app\modules\Planificacion\models\OperacionDtic;
use common\models\Estado;
use Yii;

class OperacionDticService
{
    public function listarTodo(string $idLlave, string $idGestion, string $idEstadoPoa): array
    {
        $data = OperacionDtic::listAll($idLlave, $idGestion, $idEstadoPoa)
            ->orderBy(['Op.Codigo' => SORT_ASC])
            ->asArray()
            ->all();

        return ResponseHelper::success($data, 'Listado de operaciones obtenido.');
    }

    public function guardar(
        OperacionDticForm $form,
        string $idLlave,
        string $idGestion,
        string $idEstadoPoa
    ): array {
        $this->validarRelaciones($form, $idLlave, $idGestion);
        $codigoCompuesto = $this->generarCodigoCompuesto($form->idObjEspecifico, $form->codigo);

        $modelo = new OperacionDtic([
            'IdLlavePresupuestaria' => $idLlave,
            'IdObjEspecifico' => $form->idObjEspecifico,
            'IdIndicadorEstrategico' => $form->idIndicadorEstrategico ?: null,
            'IdIndicadorPoa' => $form->idIndicadorPoa ?: null,
            'IdGestion' => $idGestion,
            'IdEstadoPoa' => $idEstadoPoa,
            'Descripcion' => mb_strtoupper($form->descripcion, 'UTF-8'),
            'PrimerTrimestre' => $form->primerTrimestre,
            'SegundoTrimestre' => $form->segundoTrimestre,
            'TercerTrimestre' => $form->tercerTrimestre,
            'CuartoTrimestre' => $form->cuartoTrimestre,
            'Codigo' => $form->codigo,
            'CodigoCompuesto' => $codigoCompuesto,
            'CodigoEstado' => Estado::ESTADO_VIGENTE,
            'CodigoUsuario' => Yii::$app->user->identity->CodigoUsuario,
        ]);

        return $this->procesar($modelo);
    }

    public function actualizar(
        string $id,
        OperacionDticForm $form,
        string $idLlave,
        string $idGestion,
        string $idEstadoPoa
    ): array {
        $this->validarRelaciones($form, $idLlave, $idGestion);
        $modelo = $this->obtenerModeloValidado($id, $idLlave, $idGestion, $idEstadoPoa);
        $codigoCompuesto = $this->generarCodigoCompuesto($form->idObjEspecifico, $form->codigo);

        $modelo->setAttributes([
            'IdObjEspecifico' => $form->idObjEspecifico,
            'IdIndicadorEstrategico' => $form->idIndicadorEstrategico ?: null,
            'IdIndicadorPoa' => $form->idIndicadorPoa ?: null,
            'Descripcion' => mb_strtoupper($form->descripcion, 'UTF-8'),
            'PrimerTrimestre' => $form->primerTrimestre,
            'SegundoTrimestre' => $form->segundoTrimestre,
            'TercerTrimestre' => $form->tercerTrimestre,
            'CuartoTrimestre' => $form->cuartoTrimestre,
            'Codigo' => $form->codigo,
            'CodigoCompuesto' => $codigoCompuesto,
        ]);

        return $this->procesar($modelo);
    }

    public function obtenerModelo(
        string $id,
        string $idLlave,
        string $idGestion,
        string $idEstadoPoa
    ): array {
        $modelo = $this->obtenerModeloValidado($id, $idLlave, $idGestion, $idEstadoPoa);

        return ResponseHelper::success($modelo->getAttributes([
            'IdOperacion', 'IdObjEspecifico', 'IdIndicadorEstrategico', 'IdIndicadorPoa',
            'Descripcion', 'PrimerTrimestre', 'SegundoTrimestre', 'TercerTrimestre',
            'CuartoTrimestre', 'Codigo',
        ]));
    }

    public function cambiarEstado(
        string $id,
        string $idLlave,
        string $idGestion,
        string $idEstadoPoa
    ): array {
        $modelo = $this->obtenerModeloValidado($id, $idLlave, $idGestion, $idEstadoPoa);
        $modelo->cambiarEstado();
        $this->guardarModelo($modelo);

        return ['message' => Yii::$app->params['PROCESO_CORRECTO'], 'data' => $modelo->CodigoEstado];
    }

    public function eliminar(
        string $id,
        string $idLlave,
        string $idGestion,
        string $idEstadoPoa
    ): array {
        $modelo = $this->obtenerModeloValidado($id, $idLlave, $idGestion, $idEstadoPoa);
        $modelo->eliminar();

        return $this->procesar($modelo);
    }

    public function verificarCodigo(
        string $id,
        string $idLlave,
        string $idGestion,
        string $idEstadoPoa,
        string $codigo
    ): bool {
        return OperacionDticDao::verificarCodigo($id, $idLlave, $idGestion, $idEstadoPoa, $codigo);
    }

    private function validarRelaciones(
        OperacionDticForm $form,
        string $idLlave,
        string $idGestion
    ): void {
        $objetivoValido = ObjetivoEspecifico::find()
            ->where([
                'IdObjEspecifico' => $form->idObjEspecifico,
                'IdLlavePresupuestaria' => $idLlave,
                'IdGestion' => $idGestion,
            ])
            ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->exists();

        if (!$objetivoValido) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                ['idObjEspecifico' => ['El objetivo específico no pertenece al contexto activo.']],
                400
            );
        }

        if ($form->idIndicadorEstrategico !== null && !IndicadorEstrategico::find()
            ->where(['IdIndicador' => $form->idIndicadorEstrategico])
            ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->exists()) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                ['idIndicadorEstrategico' => ['El indicador estratégico seleccionado no es válido.']],
                400
            );
        }

        if ($form->idIndicadorPoa !== null && !IndicadorPoa::find()
            ->where([
                'IdIndicador' => $form->idIndicadorPoa,
                'IdGestion' => $idGestion,
            ])
            ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->exists()) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                ['idIndicadorPoa' => ['El indicador POA no pertenece a la gestión activa.']],
                400
            );
        }
    }

    private function obtenerModeloValidado(
        string $id,
        string $idLlave,
        string $idGestion,
        string $idEstadoPoa
    ): OperacionDtic {
        $modelo = OperacionDtic::listAll($idLlave, $idGestion, $idEstadoPoa)
            ->andWhere(['Op.IdOperacion' => $id])
            ->one();

        if ($modelo === null) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],
                'No se encontró la operación solicitada.',
                404
            );
        }

        return $modelo;
    }

    private function generarCodigoCompuesto(string $idObjEspecifico, string $codigoOperacion): string
    {
        $codigos = ObjetivoEspecifico::find()->alias('Oe')
            ->select([
                'codigoArea' => 'A.Codigo',
                'codigoPolitica' => 'P.Codigo',
                'codigoEstrategico' => 'Oes.Codigo',
                'codigoInstitucional' => 'Oi.Codigo',
                'codigoEspecifico' => 'Oe.Codigo',
            ])
            ->joinWith('objetivosInstitucionales Oi', false, 'INNER JOIN')
            ->joinWith('objetivosInstitucionales.objetivosEstrategicos Oes', false, 'INNER JOIN')
            ->joinWith('objetivosInstitucionales.objetivosEstrategicos.areaEstrategica A', false, 'INNER JOIN')
            ->joinWith('objetivosInstitucionales.objetivosEstrategicos.politicaEstrategica P', false, 'INNER JOIN')
            ->where(['Oe.IdObjEspecifico' => $idObjEspecifico])
            ->asArray()
            ->one();

        if ($codigos === null) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],
                'No se encontraron los códigos del objetivo específico.',
                404
            );
        }

        $codigoCompuesto = trim((string)$codigos['codigoArea'])
            . trim((string)$codigos['codigoPolitica'])
            . trim((string)$codigos['codigoEstrategico'])
            . trim((string)$codigos['codigoInstitucional'])
            . trim((string)$codigos['codigoEspecifico'])
            . trim($codigoOperacion);

        if (strlen($codigoCompuesto) > 9) {
            throw new ValidationException(
                Yii::$app->params['ERROR_VALIDACION_MODELO'],
                ['codigo' => ['El código compuesto supera los 9 caracteres permitidos.']],
                422
            );
        }

        return $codigoCompuesto;
    }

    private function procesar(OperacionDtic $modelo): array
    {
        $this->guardarModelo($modelo);
        return ['message' => Yii::$app->params['PROCESO_CORRECTO'], 'data' => ''];
    }

    private function guardarModelo(OperacionDtic $modelo): void
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
