<?php
namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\formModels\UeForm;
use app\modules\Planificacion\dao\UeDao;
use app\modules\Planificacion\models\Ue;
use yii\db\StaleObjectException;
use common\models\Estado;
use yii\db\Exception;
use Throwable;
use Yii;

class UeService
{
    /**
     * Lista un array de Ues no eliminados
     *
     * @return array of ues
     */
    public function listarTodo(): array
    {
        $data = Ue::listAll()
            ->orderBy(['Ue' => SORT_ASC])
            ->asArray()->all();

        return ResponseHelper::success($data,'Listado de Ues obtenido.');
    }

    /**
     * Lista un array de Ues no eliminados
     * @param string $search
     * @return array of Ues
     */
    public function listarUesS2(string $search): array
    {
        $data = Ue::listAll($search)
            ->orderBy(['Ue' => SORT_ASC])
            ->asArray()->all();

        return ResponseHelper::success($data, 'Listado de Ues obtenido.');
    }

    /**
     * Obtiene un ue con base en un código.
     *
     * @param string $id
     * @return Ue|null
     */
    public  function listarUno(string $id): ?Ue
    {
        return Ue::listOne($id);
    }

    /**
     * Obtiene un ue con base en un código.
     *
     * @param string $id
     * @return string
     */
    public function getUe(string $id): string
    {
        return Ue::getUe($id);
    }

    /**
     * Guarda un nuevo Ue.
     *
     * @param UeForm $form
     * @return array ['message' => string, 'data' => string]
     * @throws Exception|ValidationException
     */
    public function guardar(UeForm $form): array
    {
        $modelo = new Ue([
            'Ue'          => trim($form->ue),
            'Descripcion'     => mb_strtoupper(trim($form->descripcion), 'UTF-8'),
            'CodigoEstado'    => Estado::ESTADO_VIGENTE,
            'CodigoUsuario'   => Yii::$app->user->identity->CodigoUsuario ?? null,
        ]);

        return $this->validarProcesarModelo($modelo);
    }

    /**
     * Actualiza la información de un registro en el modelo
     *
     * @param string $id
     * @param UeForm $form
     * @return array
     * @throws Exception
     * @throws Throwable
     * @throws ValidationException
     * @throws StaleObjectException
     */
    public function actualizar(string $id, UeForm $form): array
    {
        $modelo = $this->obtenerModeloValidado($id);

        $modelo->Ue = trim($form->ue);
        $modelo->Descripcion = mb_strtoupper(trim($form->descripcion), 'UTF-8');

        return $this->validarProcesarModelo($modelo);
    }

    /**
     * Busca un Ue por su código y alterna su estado.
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
            Yii::error("Error al guardar el cambio de estado del Ue $modelo->Ue", __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'],$modelo->getErrors(),500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $modelo->CodigoEstado,
        ];
    }

    /**
     * Busca un Ue por su código y realiza un soft delete.
     *
     * @param string $id
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     */
    public function eliminar(string $id): array
    {
        $modelo = $this->obtenerModeloValidado($id);

        if (UeDao::enUso($modelo)){
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_EN_USO'],'El ue se encuentra en uso',500);
        }

        $modelo->eliminar();
        return $this->validarProcesarModelo($modelo);
    }

    /**
     * Obtiene el modelo según el código enviado.
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
            'data' => $modelo->getAttributes(array('IdUe', 'Ue', 'Descripcion')),
        ];
    }

    /**
     * Obtiene el modelo según el código enviado y válida si existe.
     *
     * @param string $id
     * @return Ue|null
     * @throws ValidationException
     */
    private function obtenerModeloValidado(string $id): ?Ue
    {
        $modelo = $this->listarUno($id);
        if (!$modelo) {
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],'No se encontro el registro buscado',404);
        }
        return $modelo;
    }

    /**
     * Recibe un modelo lo valida y realiza el guardado del mismo.
     *
     * @param Ue $modelo
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     */
    public function validarProcesarModelo(Ue $modelo): array
    {
        if (!$modelo->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'], $modelo->getErrors(), 500);
        }

        if (!$modelo->save(false)) {
            Yii::error("Error al guardar el Ue $modelo->Ue", __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'], $modelo->getErrors(), 500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => '',
        ];
    }

    /**
     *  Recibe un codigo y verifica si está en uso.
     *
     * @param string $id
     * @param string $codigo
     * @return bool
     */
    public function verificarCodigo(string $id, string $codigo): bool
    {
        return UeDao::verificarCodigo($id, $codigo);
    }

    /**
     *  Recibe un id y verifica si existe.
     *
     * @param string $id
     * @return bool
     */
    public function validarId(string $id): bool
    {
        return UeDao::validarId($id);
    }
}
