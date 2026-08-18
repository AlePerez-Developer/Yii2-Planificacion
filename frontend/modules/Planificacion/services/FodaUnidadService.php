<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\formModels\FodaUnidadForm;
use app\modules\Planificacion\models\FODAUnidad;
use app\modules\Planificacion\models\UnidadEjecutora;
use common\models\Estado;
use Yii;

class FodaUnidadService
{
    public function listarTodo(string $idDa, string $idGestion): array
    {
        $data = FODAUnidad::listAll($idDa, $idGestion)
            ->orderBy(['F.FechaHoraRegistro' => SORT_DESC])
            ->asArray()
            ->all();

        return ResponseHelper::success($data, 'Registros FODA obtenidos.');
    }

    public function listarParaReporte(string $idDa, string $idGestion): array
    {
        $registros = FODAUnidad::listAll($idDa, $idGestion)
            ->andWhere(['F.CodigoEstado' => Estado::ESTADO_VIGENTE])
            ->orderBy(['F.FechaHoraRegistro' => SORT_ASC])
            ->asArray()
            ->all();

        $agrupados = [];
        foreach (FODAUnidad::tipos() as $tipo) {
            $agrupados[$tipo] = [];
        }

        foreach ($registros as $registro) {
            $tipo = (string)($registro['Tipo'] ?? '');
            if (!isset($agrupados[$tipo])) {
                continue;
            }
            $agrupados[$tipo][] = $registro;
        }

        return $agrupados;
    }

    public function guardar(FodaUnidadForm $form, string $idDa, string $idGestion): array
    {
        $modelo = new FODAUnidad([
            'IdDa' => $idDa,
            'IdGestion' => $idGestion,
            'Descripcion' => $this->normalizarDescripcion($form->descripcion),
            'Tipo' => $form->tipo,
            'CodigoEstado' => Estado::ESTADO_VIGENTE,
            'CodigoUsuario' => Yii::$app->user->identity->CodigoUsuario,
        ]);

        return $this->procesar($modelo);
    }

    public function actualizar(
        string $id,
        FodaUnidadForm $form,
        string $idDa,
        string $idGestion
    ): array {
        $modelo = $this->obtenerModelo($id, $idDa, $idGestion);
        $modelo->Descripcion = $this->normalizarDescripcion($form->descripcion);
        $modelo->Tipo = $form->tipo;
        $modelo->CodigoUsuario = Yii::$app->user->identity->CodigoUsuario;

        return $this->procesar($modelo);
    }

    public function obtener(string $id, string $idDa, string $idGestion): array
    {
        $modelo = $this->obtenerModelo($id, $idDa, $idGestion);

        return ResponseHelper::success([
            'IdFoda' => $modelo->IdFoda,
            'Descripcion' => $modelo->Descripcion,
            'Tipo' => $modelo->Tipo,
        ]);
    }

    public function cambiarEstado(string $id, string $idDa, string $idGestion): array
    {
        $modelo = $this->obtenerModelo($id, $idDa, $idGestion);
        $modelo->cambiarEstado();
        $modelo->CodigoUsuario = Yii::$app->user->identity->CodigoUsuario;

        return $this->procesar($modelo);
    }

    public function eliminar(string $id, string $idDa, string $idGestion): array
    {
        $modelo = $this->obtenerModelo($id, $idDa, $idGestion);
        $modelo->eliminar();
        $modelo->CodigoUsuario = Yii::$app->user->identity->CodigoUsuario;

        return $this->procesar($modelo);
    }

    public function obtenerIdDaDesdeUnidad(string $idUnidadEjecutora): string
    {
        $unidad = UnidadEjecutora::find()
            ->where(['IdUnidadEjecutora' => $idUnidadEjecutora])
            ->andWhere(['<>', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->one();

        $idDa = (string)($unidad?->IdDa ?? '');
        if ($idDa === '') {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'No se pudo obtener la Dirección Administrativa de la unidad ejecutora activa.',
                400
            );
        }

        return $idDa;
    }

    private function obtenerModelo(string $id, string $idDa, string $idGestion): FODAUnidad
    {
        $modelo = FODAUnidad::listAll($idDa, $idGestion)
            ->andWhere(['F.IdFoda' => $id])
            ->one();

        if ($modelo === null) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],
                'No se encontró el registro FODA solicitado.',
                404
            );
        }

        return $modelo;
    }

    private function procesar(FODAUnidad $modelo): array
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

        return ResponseHelper::success($modelo, 'Registro FODA procesado correctamente.');
    }

    private function normalizarDescripcion(string $descripcion): string
    {
        return mb_strtoupper(trim($descripcion), 'UTF-8');
    }
}
