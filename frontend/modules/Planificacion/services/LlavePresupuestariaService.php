<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\formModels\LlavePresupuestariaForm;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\dao\LlavePresupuestariaDao;
use app\modules\Planificacion\models\LlavePresupuestaria;
use yii\db\StaleObjectException;
use common\models\Estado;
use yii\db\ActiveRecord;
use yii\db\Exception;
use Throwable;
use Yii;

class LlavePresupuestariaService
{
    private DaService $serviceDa;
    private UeService $serviceUe;
    private ProgramaService $servicePrograma;
    private ProyectoService $serviceProyecto;
    private ActividadService $serviceActividad;

    public function __construct(
        DaService        $serviceDa,
        UeService        $serviceUe,
        ProgramaService  $servicePrograma,
        ProyectoService  $serviceProyecto,
        ActividadService $serviceActividad,
    )
    {
        $this->serviceDa = $serviceDa;
        $this->serviceUe = $serviceUe;
        $this->servicePrograma = $servicePrograma;
        $this->serviceProyecto = $serviceProyecto;
        $this->serviceActividad = $serviceActividad;
    }

    /**
     * Lista un array de Objetivos Estrategicos no eliminados
     *
     * @return array of Objetivos
     */
    public function listarTodo(): array
    {
        $data = LlavePresupuestaria::listAll()
            ->asArray()->all();

        return ResponseHelper::success($data, 'Listado de Llaves presupuestarias obtenido.');
    }

    /**
     * Obtiene un Objetivo Estrategico con base en un codigo.
     *
     * @param string $id
     * @return LlavePresupuestaria|null
     */
    public function listarUno(string $id): ?LlavePresupuestaria
    {
        return LlavePresupuestaria::listOne($id);
    }

    /**
     * Obtiene un Objetivo Estrategico con base en un codigo.
     *
     * @param string $id
     * @return array|ActiveRecord
     */
    public function listarUnoCompleto(string $id): array|ActiveRecord
    {
        return LlavePresupuestaria::listOneComplete($id);
    }

    /**
     * @param string $idIndicadorEstrategico
     * @param string $idGestion
     * @return array
     */
    public function listAllbyProgramacion(string $idIndicadorEstrategico, string $idGestion): array
    {
        return LlavePresupuestaria::listAllbyProgramacion($idIndicadorEstrategico, $idGestion);
    }

    /**
     * @param LlavePresupuestariaForm $form
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     */
    public function validarGuardar(LlavePresupuestariaForm $form): array
    {
        $pr1 = $this->validarPrograma($form);

        $this->validarEntidades($form);

        $form->fechaInicio = $this->validarFecha($form->fechaInicio);

        $form->llave = $this->generarLlave(
            $form->idDa,
            $form->idUe,
            $pr1,
            $form->idProyecto,
            $form->idActividad
        );

        return $this->guardar($form);
    }

    /**
     * @throws Exception
     * @throws Throwable
     * @throws ValidationException
     * @throws StaleObjectException
     */
    public function validarActualizar(string $id, LlavePresupuestariaForm $form): array
    {

        $pr1 = $this->validarPrograma($form);

        $this->validarEntidades($form);

        $form->llave = $this->generarLlave(
            $form->idDa,
            $form->idUe,
            $pr1,
            $form->idProyecto,
            $form->idActividad
        );

        $form->fechaInicio = $this->validarFecha($form->fechaInicio);

        return $this->actualizar($id, $form);
    }

    /**
     * Guarda un nuevo REGISTRO.
     *
     * @param LlavePresupuestariaForm $form
     * @return array ['message' => string, 'data' => string]
     * @throws Exception|ValidationException
     */
    public function guardar(LlavePresupuestariaForm $form): array
    {
        $modelo = new LlavePresupuestaria([
            'IdDa' => $form->idDa,
            'IdUe' => $form->idUe,
            'IdProyecto' => $form->idProyecto,
            'IdActividad' => $form->idActividad,
            'Llave' => $form->llave,
            'Descripcion' => mb_strtoupper(trim($form->descripcion), 'UTF-8'),
            'esOrganizacional' => $form->esOrganizacional,
            'FechaInicio' => $form->fechaInicio,
            'CodigoEstado' => Estado::ESTADO_VIGENTE,
            'CodigoUsuario' => Yii::$app->user->identity->CodigoUsuario ?? null,
        ]);
        return $this->validarProcesarModelo($modelo);
    }

    /**
     * Actualiza la informacion de un registro en el modelo
     *
     * @param string $id
     * @param LlavePresupuestariaForm $form
     * @return array
     * @throws Exception
     * @throws Throwable
     * @throws ValidationException
     * @throws StaleObjectException
     */
    public function actualizar(string $id, LlavePresupuestariaForm $form): array
    {
        $modelo = $this->obtenerModeloValidado($id);

        $modelo->IdDa = $form->idDa;
        $modelo->IdUe = $form->idUe;
        $modelo->IdProyecto = $form->idProyecto;
        $modelo->IdActividad = $form->idActividad;
        $modelo->Llave = $form->llave;
        $modelo->Descripcion = mb_strtoupper(trim($form->descripcion), 'UTF-8');
        $modelo->esOrganizacional = $form->esOrganizacional;
        $modelo->FechaInicio = $form->fechaInicio;

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
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'], $modelo->getErrors(), 500);
        }

        if (!$modelo->save(false)) {
            Yii::error("Error al guardar el cambio de estado de la llave presupuestaria $modelo->Descripcion", __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'], $modelo->getErrors(), 500);
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

        if (LlavePresupuestariaDao::enUso($modelo)) {
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_EN_USO'], 'El Objetivo se encuentra asignado a un objetivo institucional', 500);
        }

        $modelo->eliminar();
        return $this->validarProcesarModelo($modelo);
    }

    /**
     * @throws Exception
     * @throws ValidationException
     */
    public function finalizar(string $id): array
    {
        $modelo = $this->obtenerModeloValidado($id);

        $modelo->finalizar();

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
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'], 'Registro no encontrado', 404);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $modelo,
        ];
    }

    /**
     * Obtiene el modelo según el codigo enviado.
     *
     * @param string $id
     * @return array
     * @throws ValidationException
     */
    public function obtenerModeloCompleto(string $id): array
    {
        $modelo = $this->listarUnoCompleto($id);

        if (!$modelo) {
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'], 'Registro no encontrado', 404);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $modelo,
        ];
    }


    /**
     * Obtiene el modelo según el codigo enviado y válida si existe.
     *
     * @param string $id
     * @return LlavePresupuestaria|null
     * @throws ValidationException
     */
    private function obtenerModeloValidado(string $id): ?LlavePresupuestaria
    {
        $modelo = $this->listarUno($id);
        if (!$modelo) {
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'], 'No se encontro el registro buscado', 404);
        }
        return $modelo;
    }

    /**
     * @throws Exception
     * @throws ValidationException
     */
    private function validarProcesarModelo(LlavePresupuestaria $llave): array
    {
        if (!$llave->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'], $llave->getErrors(), 500);
        }

        if (!$llave->save(false)) {
            Yii::error('Error al guardar la Llave Presupuestaria', __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'], $llave->getErrors(), 500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => '',
        ];
    }

    /**
     * @param string $id
     * @param string $idDa
     * @param string $idUe
     * @param string $idProyecto
     * @param string $idActividad
     * @return bool
     */
    public function VerificarLlave(string $id, string $idDa, string $idUe, string $idProyecto, string $idActividad): bool
    {
        $pr1 = $this->serviceProyecto->getIdPrograma($idProyecto);
        $pr2 = $this->serviceActividad->getIdPrograma($idActividad);

        if ($pr1 !== $pr2 || empty($pr1)) {
            return false;
        }

        return LlavePresupuestariaDao::verificarCodigo($id, $idDa, $idUe, $idProyecto, $idActividad);
    }


    /**
     * @throws ValidationException
     */
    private function validarEntidades(LlavePresupuestariaForm $form): void
    {
        $validaciones = [
            'DA' => $this->serviceDa->validarId($form->idDa),
            'UE' => $this->serviceUe->validarId($form->idUe),
            'Proyecto' => $this->serviceProyecto->validarId($form->idProyecto),
            'Actividad' => $this->serviceActividad->validarId($form->idActividad),
        ];

        foreach ($validaciones as $nombre => $valido) {
            if (!$valido) {
                throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], "$nombre inválido", 400);
            }
        }
    }

    /**
     * @param LlavePresupuestariaForm $form
     * @return string
     * @throws ValidationException
     */
    private function validarPrograma(llavePresupuestariaForm $form): string
    {
        $pr1 = $this->serviceProyecto->getIdPrograma($form->idProyecto);
        $pr2 = $this->serviceActividad->getIdPrograma($form->idActividad);

        if ($pr1 !== $pr2 || empty($pr1)) {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'Proyecto y Actividad no pertenecen al mismo programa',
                400
            );
        }

        return $pr1;
    }


    private function generarLlave(
        string $idDa,
        string $idUe,
        string $idPrograma,
        string $idProyecto,
        string $idActividad
    ): string
    {

        return implode('-', [
            $this->serviceDa->getDa($idDa),
            $this->serviceUe->getUe($idUe),
            $this->servicePrograma->getCodigo($idPrograma),
            $this->serviceProyecto->getCodigo($idProyecto),
            $this->serviceActividad->getCodigo($idActividad),
        ]);
    }

    /**
     * @param string $fechaInicio
     * @return string
     * @throws ValidationException
     */
    private function validarFecha(string $fechaInicio): string
    {
        $timestamp = strtotime($fechaInicio);

        if(!$timestamp){
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], "Fecha de inicio invalida", 400);
        }

        return  date("d/m/Y", $timestamp);
    }
}
