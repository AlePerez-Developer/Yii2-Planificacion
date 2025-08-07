<?php
namespace app\modules\Planificacion\services;

use app\modules\Planificacion\formModels\PeiForm;
use app\modules\Planificacion\dao\PeiDao;
use app\modules\Planificacion\models\Pei;
use yii\web\NotFoundHttpException;
use common\models\Estado;
use yii\db\Exception;
use Throwable;
use Yii;

class PeiService
{
    /**
     * lista un array de Peis no eliminados
     * @return array of peis
     */
    public  function listarPeis(): array
    {
        return Pei::listAll()
            ->asArray()
            ->all();
    }

    /**
     * obtiene un pei en base a un codigo.
     * @param int $codigoPei
     * @return Pei | null
     */
    public  function listarPei(int $codigoPei): ?Pei
    {
        return Pei::listOne($codigoPei);
    }

    /**
     * @throws NotFoundHttpException
     */
    private function obtenerPeiValidado(int $codigoPei): ?Pei
    {
        $pei = $this->listarPei($codigoPei);
        if (!$pei) {
            throw new NotFoundHttpException(Yii::$app->params['ERROR_REGISTRO_EXISTE'] ?? 'PEI no encontrado');
        }
        return $pei;
    }

    /**
     * Guarda un nuevo PEI.
     *
     * @param PeiForm $form
     * @return array ['success' => bool, 'mensaje' => string, 'estado' => int|null, 'errors' => array|null]
     * @throws Exception
     */
    public function guardarPei(PeiForm $form): array
    {
        $pei = new Pei([
            'CodigoPei'       => PeiDao::generarCodigoPei(),
            'DescripcionPei'  => mb_strtoupper(trim($form->descripcionPei), 'UTF-8'),
            'FechaAprobacion' => date("d/m/Y", strtotime($form->fechaAprobacion)),
            'GestionInicio'   => $form->gestionInicio,
            'GestionFin'      => $form->gestionFin,
            'CodigoEstado'    => Estado::ESTADO_VIGENTE,
            'CodigoUsuario'   => Yii::$app->user->identity->CodigoUsuario ?? null,
        ]);

        return $this->validarProcesarPei($pei);
    }

    /**
     * @throws Exception
     * @throws Throwable
     */
    public function actualizarPei(int $codigoPei, PeiForm $form): array
    {
        try {
            $pei = $this->obtenerPeiValidado($codigoPei);
        } catch (NotFoundHttpException $e) {
            return [
                'success' => false,
                'mensaje' => Yii::$app->params['ERROR_REGISTRO_EXISTE'],
                'estado' => null,
                'errors' => null,
            ];
        }

        if ($pei->GestionInicio < $form->gestionInicio ){
            if (!PeiDao::validarGestionInicio($pei->CodigoPei, $form->gestionInicio)) {
                return [
                    'success' => false,
                    'mensaje' => 'errorGestionInicio',
                    'estado' => null,
                    'errors' => 'Existen indicadores programados con meta que serian afectados por el cambio de fecha de inicio',
                ];
            }
        }

        if ($pei->GestionFin > $form->gestionFin ){
            if (!PeiDao::validarGestionFin($pei->CodigoPei, $form->gestionFin)) {
                return [
                    'success' => false,
                    'mensaje' => 'errorGestionFin',
                    'estado' => null,
                    'errors' => 'Existen indicadores programados con meta que serian afectados por el cambio de fecha de finalizacion',
                ];
            }
        }

        $pei->DescripcionPei = $form->descripcionPei;
        $pei->FechaAprobacion = $form->fechaAprobacion;
        $pei->GestionInicio = $form->gestionInicio;
        $pei->GestionFin = $form->gestionFin;

        $transaction = Pei::getDb()->beginTransaction();

        PeiDao::regularizarProgramacionIndicadoresInicio($pei->CodigoPei, $form->gestionInicio, $transaction);
        PeiDao::regularizarProgramacionIndicadoresFin($pei->CodigoPei, $form->gestionFin, $transaction);

        $resultado = $this->validarProcesarPei($pei);

        if (!$resultado['success']) {
            $transaction->rollBack();
            return $resultado;
        }

        $transaction->commit();
        return $resultado;
    }

    /**
     * Busca un PEI por su código y alterna su estado.
     *
     * @param int $codigoPei
     * @return array ['success' => bool, 'mensaje' => string, 'estado' => int|null, 'errors' => array|null]
     * @throws Exception
     */
    public function cambiarEstado(int $codigoPei): array
    {
        try {
            $pei = $this->obtenerPeiValidado($codigoPei);
        } catch (NotFoundHttpException $e) {
            return [
                'success' => false,
                'mensaje' => Yii::$app->params['ERROR_REGISTRO_EXISTE'],
                'estado' => null,
                'errors' => null,
            ];
        }

        $pei->cambiarEstado();

        if (!$pei->validate()) {
            return [
                'success' => false,
                'mensaje' => Yii::$app->params['ERROR_VALIDACION_MODELO'],
                'estado' => null,
                'errors' => $pei->getErrors(),
            ];
        }

        if (!$pei->save(false)) {
            Yii::error("Error al guardar el cambio de estado del PEI $pei->CodigoPei", __METHOD__);
            return [
                'success' => false,
                'mensaje' => Yii::$app->params['ERROR_EJECUCION_SQL'],
                'estado' => null,
                'errors' => $pei->getErrors(),
            ];
        }

        return [
            'success' => true,
            'mensaje' => Yii::$app->params['PROCESO_CORRECTO'],
            'estado' => $pei->CodigoEstado,
            'errors' => null,
        ];
    }

    /**
     * Busca un PEI por su código y y lo elimina.
     *
     * @param int $codigoPei
     * @return array ['success' => bool, 'mensaje' => string, 'estado' => int|null, 'errors' => array|null]
     * @throws Exception
     */
    public function eliminarPei(int $codigoPei): array
    {
        try {
            $pei = $this->obtenerPeiValidado($codigoPei);
        } catch (NotFoundHttpException $e) {
            return [
                'success' => false,
                'mensaje' => Yii::$app->params['ERROR_REGISTRO_EXISTE'],
                'estado' => null,
                'errors' => null,
            ];
        }

        if (PeiDao::enUso($pei->CodigoPei)){
            return [
                'success' => false,
                'mensaje' => Yii::$app->params['ERROR_REGISTRO_EN_USO'],
                'errors' => 'El PEI se encuentra asignado a un objetivo estrategico',
            ];
        }

        $pei->eliminarPei();

        if (!$pei->validate()) {
            return [
                'success' => false,
                'mensaje' => Yii::$app->params['ERROR_VALIDACION_MODELO'],
                'errors' => $pei->getErrors(),
            ];
        }

        if (!$pei->save(false)) {
            Yii::error("Error al eliminar del PEI $pei->CodigoPei", __METHOD__);
            return [
                'success' => false,
                'mensaje' => Yii::$app->params['ERROR_EJECUCION_SQL'],
                'errors' => $pei->getErrors(),
            ];
        }

        return [
            'success' => true,
            'mensaje' => Yii::$app->params['PROCESO_CORRECTO'],
            'errors' => null,
        ];
    }

    /**
     * @param Pei|null $pei
     * @return array
     * @throws Exception
     */
    public function validarProcesarPei(Pei $pei): array
    {
        if (!$pei->validate()) {
            return [
                'success' => false,
                'code' => 400,
                'mensaje' => Yii::$app->params['ERROR_VALIDACION_MODELO'],
                'errors' => $pei->getErrors(),
            ];
        }

        if (!$pei->save(false)) {
            Yii::error("Error al guardar el PEI", __METHOD__);
            return [
                'success' => false,
                'code' => 500,
                'mensaje' => Yii::$app->params['ERROR_EJECUCION_SQL'],
                'errors' => $pei->getErrors(),
            ];
        }

        return [
            'success' => true,
            'code' => 201,
            'mensaje' => Yii::$app->params['PROCESO_CORRECTO'],
            'errors' => null,
        ];
    }
}
