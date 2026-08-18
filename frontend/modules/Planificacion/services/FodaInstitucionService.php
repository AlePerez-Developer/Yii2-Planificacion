<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\formModels\FodaInstitucionForm;
use app\modules\Planificacion\models\FODAInstitucion;
use common\models\Estado;
use Yii;

class FodaInstitucionService
{
    public function listarTodo(string $idGestion): array
    {
        $data = FODAInstitucion::listAll($idGestion)
            ->orderBy(['F.FechaHoraRegistro' => SORT_DESC])
            ->asArray()
            ->all();

        return ResponseHelper::success($data, 'Registros FODA obtenidos.');
    }

    public function listarParaReporte(string $idGestion): array
    {
        $registros = FODAInstitucion::listAll($idGestion)
            ->andWhere(['F.CodigoEstado' => Estado::ESTADO_VIGENTE])
            ->orderBy(['F.FechaHoraRegistro' => SORT_ASC])
            ->asArray()
            ->all();

        $agrupados = [];
        foreach (FODAInstitucion::tipos() as $tipo) {
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

    public function guardar(FodaInstitucionForm $form, string $idGestion): array
    {
        $modelo = new FODAInstitucion([
            'IdGestion' => $idGestion,
            'Descripcion' => $this->normalizarDescripcion($form->descripcion),
            'Tipo' => $form->tipo,
            'CodigoEstado' => Estado::ESTADO_VIGENTE,
            'CodigoUsuario' => Yii::$app->user->identity->CodigoUsuario,
        ]);

        return $this->procesar($modelo);
    }

    public function actualizar(string $id, FodaInstitucionForm $form, string $idGestion): array
    {
        $modelo = $this->obtenerModelo($id, $idGestion);
        $modelo->Descripcion = $this->normalizarDescripcion($form->descripcion);
        $modelo->Tipo = $form->tipo;
        $modelo->CodigoUsuario = Yii::$app->user->identity->CodigoUsuario;

        return $this->procesar($modelo);
    }

    public function obtener(string $id, string $idGestion): array
    {
        $modelo = $this->obtenerModelo($id, $idGestion);

        return ResponseHelper::success([
            'IdFoda' => $modelo->IdFoda,
            'Descripcion' => $modelo->Descripcion,
            'Tipo' => $modelo->Tipo,
        ]);
    }

    public function cambiarEstado(string $id, string $idGestion): array
    {
        $modelo = $this->obtenerModelo($id, $idGestion);
        $modelo->cambiarEstado();
        $modelo->CodigoUsuario = Yii::$app->user->identity->CodigoUsuario;

        return $this->procesar($modelo);
    }

    public function eliminar(string $id, string $idGestion): array
    {
        $modelo = $this->obtenerModelo($id, $idGestion);
        $modelo->eliminar();
        $modelo->CodigoUsuario = Yii::$app->user->identity->CodigoUsuario;

        return $this->procesar($modelo);
    }

    private function obtenerModelo(string $id, string $idGestion): FODAInstitucion
    {
        $modelo = FODAInstitucion::listAll($idGestion)
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

    private function procesar(FODAInstitucion $modelo): array
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
