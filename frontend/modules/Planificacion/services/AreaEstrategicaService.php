<?php
namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\formModels\AreaEstrategicaForm;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\dao\AreaEstrategicaDao;
use app\modules\Planificacion\models\AreaEstrategica;
use common\models\Estado;
use yii\db\Exception;
use Yii;

class AreaEstrategicaService
{
    /**
     * lista un array de Areas Estrategicas no eliminados
     *
     * @return array of Areas
     */
    public function listarTodo(): array
    {
        $data = AreaEstrategica::listAll()
            ->orderBy(['Codigo' => SORT_ASC])
            ->asArray()->all();

        return ResponseHelper::success($data, 'Listado de Áreas Estratégicas obtenido.');
    }

    /**
     * lista un array de Areas Estrategicas no eliminados
     * @param string $search
     * @return array of Areas
     */
    public function listarAreasS2(string $search): array
    {
        $data = AreaEstrategica::listAll($search)
            ->orderBy(['Codigo' => SORT_ASC])
            ->asArray()->all();

        return ResponseHelper::success($data, 'Listado de Áreas Estratégicas obtenido.');
    }

    /**
     * obtiene un Objetivo Estrategico en base a un codigo.
     *
     * @param string $id
     * @return AreaEstrategica|null
     */
    public function listarUno(string $id): ?AreaEstrategica
    {
        return AreaEstrategica::listOne($id);
    }

    /**
     * Guarda un nuevo REGISTRO.
     *
     * @param AreaEstrategicaForm $form
     * @return array ['message' => string, 'data' => string]
     * @throws ValidationException|Exception
     */
    public function guardar(AreaEstrategicaForm $form): array
    {
        $model = new AreaEstrategica([
            'IdPei' => $form->idPei,
            'Codigo' => $form->codigo,
            'Descripcion' => mb_strtoupper(trim($form->descripcion), 'UTF-8'),
            'CodigoEstado'    => Estado::ESTADO_VIGENTE,
            'CodigoUsuario'   => Yii::$app->user->identity->CodigoUsuario ?? null,
        ]);

        return $this->validarProcesarModelo($model);
    }

    /**
     * Actualiza la informacion de un registro en el modelo
     *
     * @param string $id
     * @param AreaEstrategicaForm $form
     * @return array
     * @throws Exception
     * @throws ValidationException
     */
    public function actualizar(string $id, AreaEstrategicaForm $form): array
    {
        $modelo = $this->obtenerModeloValidado($id);

        $modelo->IdPei = $form->idPei;
        $modelo->Codigo = $form->codigo;
        $modelo->Descripcion = mb_strtoupper(trim($form->descripcion), 'UTF-8');

        return $this->validarProcesarModelo($modelo);
    }

    /**
     * Busca un Objetivo por su código y alterna su estado.
     *
     * @param string $id
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     */
    public function cambiarEstado(string $id): array
    {
        $modelo = $this->obtenerModeloValidado($id);

        $modelo->cambiarEstado();

        if (!$modelo->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'],$modelo->getErrors(),500);
        }

        if (!$modelo->save(false)) {
            Yii::error("Error al guardar el cambio de estado del Area Estrategica $modelo->Descripcion", __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'],$modelo->getErrors(),500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $modelo->CodigoEstado,
        ];
    }

    /**
     * Busca un Objetivo por su código y realiza un soft delete.
     *
     * @param string $id
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     */
    public function eliminar(string $id): array
    {
        $modelo = $this->obtenerModeloValidado($id);

        if (AreaEstrategicaDao::enUso($modelo)){
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_EN_USO'],'El Area estrategica se encuentra asignada a una Politica estrategica',500);
        }

        $modelo->eliminar();
        return $this->validarProcesarModelo($modelo);
    }

    /**
     * Obtiene el modelo segun el codigo enviado.
     *
     * @param string $id
     * @return array
     * @throws ValidationException
     */
    public function obtenerModelo(string $id): array
    {
        $modelo = $this->listarUno($id);

        if (!$modelo) {
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],'Registro no encontrado',404);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $modelo->getAttributes(array('IdAreaEstrategica', 'IdPei', 'Codigo', 'Descripcion')),
        ];
    }

    /**
     * Obtiene el modelo segun el codigo enviado y valida si existe.
     *
     * @param string $id
     * @return AreaEstrategica|null
     * @throws ValidationException
     */
    private function obtenerModeloValidado(string $id): ?AreaEstrategica
    {
        $modelo = $this->listarUno($id);
        if (!$modelo) {
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],'No se encontro el registro buscado',404);
        }
        return $modelo;
    }

    /**
     *  Recibe un modelo lo valida y realiza el guardado del mismo.
     *
     * @param AreaEstrategica $modelo
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     */
    public function validarProcesarModelo(AreaEstrategica $modelo): array
    {
        if (!$modelo->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'],$modelo->getErrors(),500);
        }

        if (!$modelo->save(false)) {
            Yii::error("Error al guardar los datos del area estrategica $modelo->Descripcion", __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'],$modelo->getErrors(),500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => '',
        ];
    }

    /**
     *  Recibe un codigo y verifica si esta en uso.
     *
     * @param string $id
     * @param int $codigo
     * @return bool
     */
    public function verificarCodigo(string $id, int $codigo): bool
    {
        return AreaEstrategicaDao::verificarCodigo($id, $codigo);
    }

    /**
     *  Recibe un id y verifica si existe.
     *
     * @param string $id
     * @return bool
     */
    public function validarId(string $id): bool
    {
        return AreaEstrategicaDao::validarId($id);
    }
}