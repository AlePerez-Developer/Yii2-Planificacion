<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\formModels\AccionEstrategicaForm;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\dao\AccionEstrategicaDao;
use app\modules\Planificacion\models\AccionEstrategica;
use common\models\Estado;
use yii\db\Exception;
use Yii;

class AccionEstrategicaService
{
    /**
     * Lista un array de Acciones Estrategicas no eliminadas
     *
     * @return array
     */
    public function listarTodo(): array
    {
        $data = AccionEstrategica::listAll()
            ->orderBy(['Codigo' => SORT_ASC])
            ->asArray()->all();

        return ResponseHelper::success($data, 'Listado de Acciones Estratégicas obtenido.');
    }

    /**
     * Lista acciones para Select2.
     *
     * @param string $search
     * @return array
     */
    public function listarAccionesS2(string $search): array
    {
        $data = AccionEstrategica::listAll($search)
            ->orderBy(['Codigo' => SORT_ASC])
            ->asArray()->all();

        return ResponseHelper::success($data, 'Listado de Acciones Estratégicas obtenido.');
    }

    /**
     * @param string $id
     * @return AccionEstrategica|null
     */
    public function listarUno(string $id): ?AccionEstrategica
    {
        return AccionEstrategica::listOne($id);
    }

    /**
     * @param AccionEstrategicaForm $form
     * @return array
     * @throws ValidationException|Exception
     */
    public function guardar(AccionEstrategicaForm $form): array
    {
        $model = new AccionEstrategica([
            'Codigo' => $form->codigo,
            'Descripcion' => mb_strtoupper(trim($form->descripcion), 'UTF-8'),
            'CodigoEstado' => Estado::ESTADO_VIGENTE,
            'CodigoUsuario' => Yii::$app->user->identity->CodigoUsuario ?? null,
        ]);

        return $this->validarProcesarModelo($model);
    }

    /**
     * @param string $id
     * @param AccionEstrategicaForm $form
     * @return array
     * @throws Exception
     * @throws ValidationException
     */
    public function actualizar(string $id, AccionEstrategicaForm $form): array
    {
        $modelo = $this->obtenerModeloValidado($id);

        $modelo->Codigo = $form->codigo;
        $modelo->Descripcion = mb_strtoupper(trim($form->descripcion), 'UTF-8');

        return $this->validarProcesarModelo($modelo);
    }

    /**
     * @param string $id
     * @param string|null $codigoEstado
     * @return array
     * @throws Exception
     * @throws ValidationException
     */
    public function cambiarEstado(string $id, ?string $codigoEstado = null): array
    {
        $modelo = $this->obtenerModeloValidado($id);

        $modelo->cambiarEstado($codigoEstado);

        if (!$modelo->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'], $modelo->getErrors(), 500);
        }

        if (!$modelo->save(false)) {
            Yii::error("Error al guardar el cambio de estado de la Accion Estrategica $modelo->Descripcion", __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'], $modelo->getErrors(), 500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $modelo->CodigoEstado,
        ];
    }

    /**
     * @param string $id
     * @return array
     * @throws Exception
     * @throws ValidationException
     */
    public function eliminar(string $id): array
    {
        $modelo = $this->obtenerModeloValidado($id);

        if (AccionEstrategicaDao::enUso($modelo)) {
            throw new ValidationException(
                Yii::$app->params['ERROR_REGISTRO_EN_USO'],
                'La acción estratégica se encuentra asignada a un indicador estratégico',
                500
            );
        }

        $modelo->eliminar();
        return $this->validarProcesarModelo($modelo);
    }

    /**
     * @param string $id
     * @return array
     * @throws ValidationException
     */
    public function obtenerModelo(string $id): array
    {
        $modelo = $this->listarUno($id);

        if (!$modelo) {
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'], 'Registro no encontrado', 404);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $modelo->getAttributes(['IdAccionEstrategica', 'Codigo', 'Descripcion']),
        ];
    }

    /**
     * @param string $id
     * @return AccionEstrategica|null
     * @throws ValidationException
     */
    private function obtenerModeloValidado(string $id): ?AccionEstrategica
    {
        $modelo = $this->listarUno($id);
        if (!$modelo) {
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'], 'No se encontro el registro buscado', 404);
        }
        return $modelo;
    }

    /**
     * @param AccionEstrategica $modelo
     * @return array
     * @throws Exception
     * @throws ValidationException
     */
    public function validarProcesarModelo(AccionEstrategica $modelo): array
    {
        if (!$modelo->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'], $modelo->getErrors(), 500);
        }

        if (!$modelo->save(false)) {
            Yii::error("Error al guardar los datos de la accion estrategica $modelo->Descripcion", __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'], $modelo->getErrors(), 500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => '',
        ];
    }

    /**
     * @param string $id
     * @return bool
     */
    public function validarId(string $id): bool
    {
        return AccionEstrategicaDao::validarId($id);
    }
}

