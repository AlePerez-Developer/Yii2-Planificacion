<?php
namespace app\modules\Planificacion\services;

use app\modules\Planificacion\formModels\PeiForm;
use app\modules\Planificacion\dao\PeiDao;
use app\modules\Planificacion\models\Pei;
use yii\web\NotFoundHttpException;
use common\models\Estado;
use yii\db\Exception;
use Yii;
class PeiService
{
    public  function listarPeis(): array
    {
        return Pei::find()
            ->select([
                'CodigoPei',
                'DescripcionPei',
                'FechaAprobacion',
                'GestionInicio',
                'GestionFin',
                'CodigoEstado',
                'CodigoUsuario'
            ])
            ->where(['!=', 'CodigoEstado', Estado::ESTADO_ELIMINADO])
            ->orderBy(['CodigoPei' => SORT_ASC])
            ->asArray()
            ->all();
    }

    /**
     * Guarda un nuevo PEI.
     *
     * @param PeiForm $form
     * @return array
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

    /**
     * Busca un PEI por su código y alterna su estado.
     *
     * @param string|int $codigoPei
     * @return array ['success' => bool, 'mensaje' => string, 'estado' => int|null, 'errors' => array|null]
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function cambiarEstado($codigoPei): array
    {
        $pei = Pei::listOne($codigoPei);

        if (!$pei) {
            throw new NotFoundHttpException('PEI no encontrado.');
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
}
