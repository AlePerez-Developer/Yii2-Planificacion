<?php

namespace app\modules\Planificacion\services;

use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\models\ProgramacionIndicadorGestion;
use app\modules\Planificacion\common\helpers\ResponseHelper;
use app\modules\Planificacion\models\IndicadorEstrategico;
use app\modules\Planificacion\models\PeiGestion;
use yii\db\StaleObjectException;
use common\models\Estado;
use yii\db\Exception;
use Throwable;
use Yii;


/**
 *
 */
class IndicadorEstrategicoProgramacionAnualService
{
    private LlavePresupuestariaService $serviceLlavePresupuestaria;

    public function __construct(
        LlavePresupuestariaService $serviceLlavePresupuestaria
    )
    {
        $this->serviceLlavePresupuestaria = $serviceLlavePresupuestaria;
    }

    /**
     * Lista un array de Programaciones no eliminados
     *
     * @return array of peis
     */
    public  function listarTodo(string $idGestion): array
    {
        $data = ProgramacionIndicadorGestion::listAllbyGestion()
            ->andWhere(['IdGestion' => $idGestion])
            ->asArray()
            ->all();
        return ResponseHelper::success($data,'Listado de PEIs obtenido.');
    }

    /**
     * Obtiene un registro de programacion con base en un codigo.
     *
     * @param string $id
     * @return ProgramacionIndicadorGestion|null
     */
    public  function listarUno(string $id): ?ProgramacionIndicadorGestion
    {
        return ProgramacionIndicadorGestion::listOne($id);
    }


    /**
     * Lista un array de Indicadores Estrategicos no eliminados según un, Id Objetivo Estrategico
     *
     * @return array of Indicadores Estategicos segun
     */
    public function listarIndicadoresbyObjConProgramacion(string $id): array
    {
        $data = IndicadorEstrategico::listAll()
            ->addSelect(['isnull(sum(Ip.MetaProgramada),0) as MetaProgramada'])
            ->joinWith('indicadorEstrategicoProgramacionGestions Ip')
            ->andWhere(['I.IdObjEstrategico' => $id])
            ->orderBy(['Codigo' => SORT_ASC])
            ->asArray()->all();

        return ResponseHelper::success($data, 'Listado de Indicadores Estrategicos por Objetivo obtenido.');
    }


    /**
     * Lista de gestiones de un pei en específico
     *
     * @param string $id
     * @param string $idIndicadorEstrategico
     * @return array of peiGestion
     */
    public function listarGestionesbyPei(string $id, string $idIndicadorEstrategico): array
    {
        $data = PeiGestion::listAll()
            ->addSelect(['isnull(sum(Ip.MetaProgramada),0) as MetaProgramada'])
            ->joinWith([
                'gestionProgramacion Ip' => function ($query) use ($idIndicadorEstrategico) {
                    $query->andOnCondition([
                        'Ip.IdIndicadorEstrategico' => $idIndicadorEstrategico,
                    ]);
                },
            ])
            ->andWhere(['G.IdPei' => $id])
            ->orderBy(['G.Gestion' => SORT_ASC])
            ->asArray()->all();

        return ResponseHelper::success($data, 'Listado de gestiones obtenido.');
    }


    /**
     * Lista de programaciones de una gestion y un indicador
     *
     * @param int $codigoIndicador
     * @param int $gestion
     * @param string $pei
     * @return array
     * @throws ValidationException
     */
    public function listarProgramacionbyGestion(int $codigoIndicador, int $gestion, string $pei): array
    {
        $idIndicador = $this->obtenerIdIndicador($codigoIndicador, $pei);
        $idGestion = $this->obtenerIdGestion($gestion, $pei);

        $data = ProgramacionIndicadorGestion::listAllbyGestion()
            ->andWhere(['I.IdIndicadorEstrategico' => $idIndicador])
            ->andWhere(['P.IdGestion' => $idGestion])
            ->asArray()->all();

        return ResponseHelper::success($data, 'Listado de programaciones obtenido.');
    }

    /**
     * Lista de llaves presupuestarias
     *
     * @param int $codigoIndicador
     * @param int $gestion
     * @param string $pei
     * @return array
     * @throws ValidationException
     */
    public function listarLlavesPresupuestarias(int $codigoIndicador, int $gestion, string $pei): array
    {
        $idIndicador = $this->obtenerIdIndicador($codigoIndicador, $pei);
        $idGestion = $this->obtenerIdGestion($gestion, $pei);

        $data = $this->serviceLlavePresupuestaria->listAllbyProgramacion($idIndicador, $idGestion);
        return ResponseHelper::success($data, 'Listado de llaves presupuestarias obtenido.');
    }


    /**
     * agrega o elimina la relacion de llavepresupuestaria
     *
     * @param $idLlavePresupuestaria
     * @param $codigoIndicador
     * @param $gestion
     * @param $pei
     * @return array
     * @throws Exception
     * @throws StaleObjectException
     * @throws ValidationException
     * @throws Throwable
     */
    public function cambiarEstado($idLlavePresupuestaria, $codigoIndicador, $gestion, $pei): array
    {
        $idIndicadorEstrategico = $this->obtenerIdIndicador($codigoIndicador, $pei);
        $idGestion = $this->obtenerIdGestion($gestion, $pei);
        $data = 0;

        $modelo = $this->obtenerModelo(
            $idIndicadorEstrategico,
            $idGestion,
            $idLlavePresupuestaria
        );

        if ($modelo !== null) {
            $modelo->delete();
        } else {
            $modelo = new ProgramacionIndicadorGestion([
                'IdIndicadorEstrategico' => $idIndicadorEstrategico,
                'IdGestion' => $idGestion,
                'IdLlavePresupuestaria' => $idLlavePresupuestaria,
                'MetaProgramada' => 0,
                'CodigoUsuario' => Yii::$app->user->identity->CodigoUsuario ?? null,
            ]);
            $data = 1;
            $modelo->save();
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $data,
        ];
    }

    /**
     * Quita una programacion del modelo
     * @param string $idProgramacion
     * @return array
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function quitarProgramacion(string $idProgramacion): array
    {
        $modelo = ProgramacionIndicadorGestion::findOne($idProgramacion);

        $modelo?->delete();

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => '',
        ];
    }

    /**
     * registra un nuevo valor de meta
     *
     * @param string $idProgramacion
     * @param int $meta
     * @return array
     * @throws Exception
     * @throws ValidationException
     */
    public function guardarMeta(string $idProgramacion, int $meta): array
    {
        $modelo = $this->obtenerModeloValidado($idProgramacion);

        $metaProgramada = filter_var($meta, FILTER_VALIDATE_INT);
        if ($metaProgramada === false || $metaProgramada < 0) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'Meta programada invalida.', 400);
        }

        $modelo->MetaProgramada = $metaProgramada;

        return $this->validarProcesarModelo($modelo);
    }

    /**
     * @param string $idIndicadorEstrategico
     * @return array
     */
    public function calcularMeta(string $idIndicadorEstrategico): array
    {
        $total = ProgramacionIndicadorGestion::find()
            ->where([
                'IdIndicadorEstrategico' => $idIndicadorEstrategico
            ])
            ->sum('MetaProgramada') ?? 0;

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $total,
        ];
    }

    /**
     * @param string $idIndicadorEstrategico
     * @param int $gestion
     * @param string $pei
     * @return array
     * @throws ValidationException
     */
    public function calcularMetaGestion(string $idIndicadorEstrategico, int $gestion, string $pei): array
    {
        $idGestion = $this->obtenerIdGestion($gestion, $pei);
        $total = ProgramacionIndicadorGestion::find()
            ->where([
                'IdIndicadorEstrategico' => $idIndicadorEstrategico,
                'IdGestion' => $idGestion
            ])
            ->sum('MetaProgramada') ?? 0;

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => $total,
        ];
    }

    private function obtenerModelo(
        string $idIndicadorEstrategico,
        string $idGestion,
        string $idLlavePresupuestaria
    ): ?ProgramacionIndicadorGestion
    {
        return ProgramacionIndicadorGestion::findOne([
            'IdIndicadorEstrategico' => $idIndicadorEstrategico,
            'IdGestion' => $idGestion,
            'IdLlavePresupuestaria' => $idLlavePresupuestaria,
        ]);
    }

    /**
     * Obtiene el modelo según el codigo enviado y válida si existe.
     *
     * @param string $id
     * @return ProgramacionIndicadorGestion|null
     * @throws ValidationException
     */
    private function obtenerModeloValidado(string $id): ?ProgramacionIndicadorGestion
    {
        $model = $this->listarUno($id);
        if (!$model) {
            throw new ValidationException(Yii::$app->params['ERROR_REGISTRO_NO_ENCONTRADO'],'No se encontro el registro buscado',404);
        }
        return $model;
    }

    /**
     *  Recibe un modelo lo valida y realiza el guardado del mismo.
     *
     * @param ProgramacionIndicadorGestion $model
     * @return array ['message' => string, 'data' => string]
     * @throws Exception
     * @throws ValidationException
     */
    public function validarProcesarModelo(ProgramacionIndicadorGestion $model): array
    {
        if (!$model->validate()) {
            throw new ValidationException(Yii::$app->params['ERROR_VALIDACION_MODELO'],$model->getErrors(),500);
        }

        if (!$model->save(false)) {
            Yii::error("Error al guardar el cambio de estado de la programacion  $model->IdProgramacionIndicadorGestio", __METHOD__);
            throw new ValidationException(Yii::$app->params['ERROR_EJECUCION_SQL'],$model->getErrors(),500);
        }

        return [
            'message' => Yii::$app->params['PROCESO_CORRECTO'],
            'data' => '',
        ];
    }

    /**
     * Válida que el indicador exista y pertenezca al PEI actual.
     * @param int $codigo
     * @param string $pei
     * @return string
     * @throws ValidationException
     */
    private function obtenerIdIndicador(int $codigo, string $pei): string
    {
        $modelo = IndicadorEstrategico::listAllSimple()
            ->addSelect(['O.IdObjEstrategico'])
            ->joinWith('objetivosEstrategicos O', true, 'INNER JOIN')
            ->andWhere(['!=', 'O.CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->andWhere(['I.Codigo' => $codigo])
            ->andWhere(['O.IdPei' => $pei])
            ->addGroupBy(['O.IdObjEstrategico'])
            ->one();

        if (!$modelo) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'El codigo de Indicador estrategico no es valido en el contexto', 500);
        }

        return $modelo->IdIndicadorEstrategico;
    }

    /**
     * Válida que la gestion exista y pertenezca al PEI actual.
     * @param int $gestion
     * @param string $pei
     * @return string
     * @throws ValidationException
     */
    private function obtenerIdGestion(int $gestion, string $pei): string
    {
        $modelo = PeiGestion::listAll()
            ->andWhere(['G.Gestion' => $gestion])
            ->andWhere(['G.IdPei' => $pei])
            ->one();

        if (!$modelo) {
            throw new ValidationException(Yii::$app->params['ERROR_ENVIO_DATOS'], 'Gestion invalida para el PEI actual.', 400);
        }

        return $modelo->IdGestion;
    }

}