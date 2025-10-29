<?php
namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\formModels\ObjetivoEstrategicoForm;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\models\ObjetivoEstrategico;
use app\modules\Planificacion\dao\ObjEstrategicoDao;
use yii\db\StaleObjectException;
use common\models\Estado;
use yii\db\Exception;
use Throwable;
use Yii;

class ObjetivoEstrategicoService
{
    /**
     * lista un array de Objetivos Estrategicos no eliminados
     *
     * @return array of Objetivos
     */
    public function listarTodo(): array
    {
        $data = ObjetivoEstrategico::listAll()
            ->orderBy(['Codigo' => SORT_ASC])
            ->asArray()->all();

        return ResponseHelper::success($data,'Listado de Objetivos Estrategicos obtenido.');
    }

    /**
     * obtiene un Objetivo Estrategico en base a un codigo.
     *
     * @param string $id
     * @return ObjetivoEstrategico|null
     */
    public  function listarUno(string $id): ?ObjetivoEstrategico
    {
        return ObjetivoEstrategico::listOne($id);
    }

    /**
     * Guarda un nuevo REGISTRO.
     *
     * @param ObjetivoEstrategicoForm $form
     * @return array ['message' => string, 'data' => string]
     * @throws Exception|ValidationException
     */
    public function guardar(ObjetivoEstrategicoForm $form): array
    {
        $modelo = new ObjetivoEstrategico([
            'IdPei' => $form->idPei,
            'IdAreaEstrategica' => $form->idAreaEstrategica,
            'IdPoliticaEstrategica' => $form->idPoliticaEstrategica,
            'Codigo'  => $form->codigo,
            'Objetivo'  => mb_strtoupper(trim($form->objetivo), 'UTF-8'),
            'Producto'  => mb_strtoupper(trim($form->producto), 'UTF-8'),
            'Indicador_Descripcion'  => mb_strtoupper(trim($form->descripcion), 'UTF-8'),
            'Indicador_Formula'  => mb_strtoupper(trim($form->formula), 'UTF-8'),
            'CodigoEstado'    => Estado::ESTADO_VIGENTE,
            'CodigoUsuario'   => Yii::$app->user->identity->CodigoUsuario ?? null,
        ]);

        return $this->validarProcesarModelo($modelo);
    }

    /**
     * Actualiza la informacion de un registro en el modelo
     *
     * @param string $id
     * @param ObjetivoEstrategicoForm $form
     * @return array
     * @throws Exception
     * @throws Throwable
     * @throws ValidationException
     * @throws StaleObjectException
     */
    public function actualizar(string $id, ObjetivoEstrategicoForm $form): array
    {
        $modelo = $this->obtenerModeloValidado($id);

        $modelo->IdPei = $form->idPei;
        $modelo->IdAreaEstrategica = $form->idAreaEstrategica;
        $modelo->IdPoliticaEstrategica = $form->idPoliticaEstrategica;
        $modelo->Codigo = $form->codigo;
        $modelo->Objetivo = mb_strtoupper(trim($form->objetivo), 'UTF-8');
        $modelo->Producto = mb_strtoupper(trim($form->producto), 'UTF-8');
        $modelo->Indicador_Descripcion = mb_strtoupper(trim($form->descripcion), 'UTF-8');
        $modelo->Indicador_Formula = mb_strtoupper(trim($form->formula), 'UTF-8');

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
            Yii::error("Error al guardar el cambio de estado del Objetivo Estrategico $modelo->Codigo", __METHOD__);
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

        if (ObjEstrategicoDao::enUso($modelo)){
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_EN_USO'],'El Objetivo se encuentra asignado a un objetivo institucional',500);
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
            'data' => $modelo->getAttributes(array('IdAreaEstrategica', 'IdPoliticaEstrategica', 'IdObjEstrategico', 'Codigo', 'Objetivo', 'Producto', 'Indicador_Descripcion', 'Indicador_Formula', 'IdPei')),
        ];
    }


    /**
     * Obtiene el modelo segun el codigo enviado y valida si existe.
     *
     * @param string $id
     * @return ObjetivoEstrategico|null
     * @throws ValidationException
     */
    private function obtenerModeloValidado(string $id): ?ObjetivoEstrategico
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
     * @param ObjetivoEstrategico $modelo
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     */
    public function validarProcesarModelo(ObjetivoEstrategico $modelo): array
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
     *  Recibe un codigo y verifica si esta en uso.
     *
     * @param string $id
     * @param string $idAreaEstrategica
     * @param string $idPoliticaEstrategica
     * @param int $codigo
     * @return bool
     */
    public function verificarCodigo(string $id, string $idAreaEstrategica, string $idPoliticaEstrategica, int $codigo): bool
    {
        return ObjEstrategicoDao::verificarCodigo($id, $idAreaEstrategica, $idPoliticaEstrategica, $codigo);
    }
}