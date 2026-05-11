<?php
namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\formModels\DaForm;
use app\modules\Planificacion\dao\DaDao;
use app\modules\Planificacion\models\Da;
use yii\db\StaleObjectException;
use common\models\Estado;
use yii\db\Exception;
use Throwable;
use Yii;

class DaService
{
    /**
     * Lista un array de Das no eliminados
     *
     * @return array of das
     */
    public function listarTodo(): array
    {
        $data = Da::listAll()
            ->orderBy(['Da' => SORT_ASC])
            ->asArray()->all();

        return ResponseHelper::success($data,'Listado de Das obtenido.');
    }

    /**
     * lista un array de Das no eliminados
     * @param string $search
     * @return array of Das
     */
    public function listarDasS2(string $search): array
    {
        $data = Da::listAll($search)
            ->orderBy(['Da' => SORT_ASC])
            ->asArray()->all();

        return ResponseHelper::success($data, 'Listado de Das obtenido.');
    }

    /**
     * Obtiene un da en base a un código.
     *
     * @param string $id
     * @return Da|null
     */
    public  function listarUno(string $id): ?Da
    {
        return Da::listOne($id);
    }

    /**
     * Guarda un nuevo Da.
     *
     * @param DaForm $form
     * @return array ['message' => string, 'data' => string]
     * @throws Exception|ValidationException
     */
    public function guardar(DaForm $form): array
    {
        $modelo = new Da([
            'Da'          => trim($form->da),
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
     * @param DaForm $form
     * @return array
     * @throws Exception
     * @throws Throwable
     * @throws ValidationException
     * @throws StaleObjectException
     */
    public function actualizar(string $id, DaForm $form): array
    {
        $modelo = $this->obtenerModeloValidado($id);

        $modelo->Da = trim($form->da);
        $modelo->Descripcion = mb_strtoupper(trim($form->descripcion), 'UTF-8');

        return $this->validarProcesarModelo($modelo);
    }

    /**
     * Busca un Da por su código y alterna su estado.
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
            Yii::error("Error al guardar el cambio de estado del Da $modelo->Da", __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'],$modelo->getErrors(),500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $modelo->CodigoEstado,
        ];
    }

    /**
     * Busca un Da por su código y realiza un soft delete.
     *
     * @param string $id
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     */
    public function eliminar(string $id): array
    {
        $modelo = $this->obtenerModeloValidado($id);

        if (DaDao::enUso($modelo)){
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_EN_USO'],'El da se encuentra en uso',500);
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
            'data' => $modelo->getAttributes(array('IdDa', 'Da', 'Descripcion')),
        ];
    }

    /**
     * Obtiene el modelo según el código enviado y valida si existe.
     *
     * @param string $id
     * @return Da|null
     * @throws ValidationException
     */
    private function obtenerModeloValidado(string $id): ?Da
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
     * @param Da $modelo
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     */
    public function validarProcesarModelo(Da $modelo): array
    {
        if (!$modelo->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'], $modelo->getErrors(), 500);
        }

        if (!$modelo->save(false)) {
            Yii::error("Error al guardar el Da $modelo->Da", __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'], $modelo->getErrors(), 500);
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
     * @param string $codigo
     * @return bool
     */
    public function verificarCodigo(string $id, string $codigo): bool
    {
        return DaDao::verificarCodigo($id, $codigo);
    }

    /**
     *  Recibe un id y verifica si existe.
     *
     * @param string $id
     * @return bool
     */
    public function validarId(string $id): bool
    {
        return DaDao::validarId($id);
    }
}
