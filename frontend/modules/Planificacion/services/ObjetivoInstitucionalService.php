<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\formModels\ObjetivoInstitucionalForm;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\models\ObjetivoInstitucional;
use app\modules\Planificacion\dao\ObjInstitucionalDao;
use yii\db\StaleObjectException;
use common\models\Estado;
use yii\db\Exception;
use Throwable;
use Yii;

class ObjetivoInstitucionalService
{
    private ObjetivoEstrategicoService $serviceObjEstrategico;
    public function __construct(
        ObjetivoEstrategicoService $serviceObjEstrategico
    )
    {
        $this->serviceObjEstrategico = $serviceObjEstrategico;
    }

    /**
     * Lista un array de Objetivos Estrategicos no eliminados
     *
     * @return array of Objetivos
     */
    public function listarTodo(): array
    {
        $data = ObjetivoInstitucional::listAll()
            ->orderBy(['Compuesto' => SORT_ASC])
            ->asArray()->all();

        return ResponseHelper::success($data,'Listado de Objetivos institucionales obtenido.');
    }

    /**
     * Lista un array de Áreas Estrategicas no eliminados
     * @param string $search
     * @return array of ObjInstitucionales
     */
    public function listarObjInstitucionalesS2(string $search): array
    {
        $data = ObjetivoInstitucional::listAll($search)
            ->orderBy(['Compuesto' => SORT_ASC])
            ->asArray()->all();

        return ResponseHelper::success($data, 'Listado de Objetivos institucionales obtenido.');
    }

    /**
     * Obtiene un Objetivo Estrategico con base en un codigo.
     *
     * @param string $id
     * @return ObjetivoInstitucional|null
     */
    public  function listarUno(string $id): ?ObjetivoInstitucional
    {
        return ObjetivoInstitucional::listOne($id);
    }

    /**
     * @param ObjetivoInstitucionalForm $form
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     */
    public function validarGuardar(ObjetivoInstitucionalForm $form): array
    {
        $this->validarEntidades($form);

        return $this->guardar($form);
    }

    /**
     * @throws Exception
     * @throws Throwable
     * @throws ValidationException
     * @throws StaleObjectException
     */
    public function validarActualizar(string $id, ObjetivoInstitucionalForm $form): array
    {
        $this->validarEntidades($form);

        return $this->actualizar($id, $form);
    }

    /**
     * Guarda un nuevo REGISTRO.
     *
     * @param ObjetivoInstitucionalForm $form
     * @return array ['message' => string, 'data' => string]
     * @throws Exception|ValidationException
     */
    public function guardar(ObjetivoInstitucionalForm $form): array
    {
        $modelo = new ObjetivoInstitucional([
            'IdObjEstrategico' => $form->idObjEstrategico,
            'Codigo'  => $form->codigo,
            'Objetivo'  => mb_strtoupper(trim($form->objetivo), 'UTF-8'),
            'Producto'  => mb_strtoupper(trim($form->producto), 'UTF-8'),
            'IdGestion'  => $form->idGestion,
            'CodigoEstado'    => Estado::ESTADO_VIGENTE,
            'CodigoUsuario'   => Yii::$app->user->identity->CodigoUsuario ?? null,
        ]);

        return $this->validarProcesarModelo($modelo);
    }

    /**
     * Actualiza la informacion de un registro en el modelo
     *
     * @param string $id
     * @param ObjetivoInstitucionalForm $form
     * @return array
     * @throws Exception
     * @throws Throwable
     * @throws ValidationException
     * @throws StaleObjectException
     */
    public function actualizar(string $id, ObjetivoInstitucionalForm $form): array
    {
        $modelo = $this->obtenerModeloValidado($id);

        $modelo->IdObjEstrategico = $form->idObjEstrategico;
        $modelo->Codigo = $form->codigo;
        $modelo->Objetivo = mb_strtoupper(trim($form->objetivo), 'UTF-8');
        $modelo->Producto = mb_strtoupper(trim($form->producto), 'UTF-8');
        $modelo->IdGestion = $form->idGestion;

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
            Yii::error("Error al guardar el cambio de estado del Objetivo Institucional $modelo->Codigo", __METHOD__);
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

        if (ObjInstitucionalDao::enUso($modelo)){
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_EN_USO'],'El Objetivo se encuentra asignado a un objetivo Especifico',500);
        }

        $modelo->eliminar();

        return $this->validarProcesarModelo($modelo);
    }

    /**
     * Obtiene el modelo según el codigo enviado.
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
            'data' => $modelo->getAttributes(array('IdObjInstitucional', 'IdObjEstrategico', 'Codigo', 'Objetivo', 'Producto', 'IdGestion')),
        ];
    }

    /**
     * Obtiene el modelo según el codigo enviado y válida si existe.
     *
     * @param string $id
     * @return ObjetivoInstitucional|null
     * @throws ValidationException
     */
    private function obtenerModeloValidado(string $id): ?ObjetivoInstitucional
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
     * @param ObjetivoInstitucional $modelo
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     */
    public function validarProcesarModelo(ObjetivoInstitucional $modelo): array
    {
        if (!$modelo->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'],$modelo->getErrors(),500);
        }

        if (!$modelo->save(false)) {
            Yii::error("Error al guardar los datos del objetivo $modelo->Codigo", __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'],$modelo->getErrors(),500);
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
     * @param string $idObjEstrategico
     * @param string $codigo
     * @return bool
     */
    public function verificarCodigo(string $id, string $idObjEstrategico, string $codigo): bool
    {
        return ObjInstitucionalDao::verificarCodigo($id, $idObjEstrategico, $codigo);
    }

    /**
     *  Recibe un id y verifica si existe.
     *
     * @param string $id
     * @return bool
     */
    public function validarId(string $id): bool
    {
        return ObjInstitucionalDao::validarId($id);
    }

    /**
     * @throws ValidationException
     */
    private function validarEntidades(ObjetivoInstitucionalForm $form): void
    {
        $validaciones = [
            'objEstrategico' => $this->serviceObjEstrategico->validarId($form->idObjEstrategico),
        ];

        foreach ($validaciones as $nombre => $valido) {
            if (!$valido) {
                throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], "$nombre inválido", 400);
            }
        }
    }
}
