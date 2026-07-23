<?php

namespace app\modules\Planificacion\controllers;

use app\controllers\BaseController;
use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\models\ObjetivoEspecifico;
use app\modules\Planificacion\services\IndicadorPoaProgramacionTrimestralService;
use Yii;
use yii\filters\VerbFilter;

class IndicadorPoaProgramacionTrimestralController extends BaseController
{
    public function __construct($id, $module, private IndicadorPoaProgramacionTrimestralService $service, $config = [])
    {
        parent::__construct($id, $module, $config);
    }

    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'listar-objetivos-especificos-s2' => ['POST'],
                    'listar-indicadores' => ['POST'],
                    'listar-programacion' => ['POST'],
                    'guardar-meta' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $this->obtenerContextoActivo();
        return $this->render('index');
    }

    public function actionListarObjetivosEspecificosS2(): array
    {
        [$idLlave, $gestion] = $this->obtenerContextoActivo();

        $data = ObjetivoEspecifico::listAll($idLlave, $gestion)
            ->select([
                'id' => 'OE.IdObjEspecifico',
                'text' => 'OE.Objetivo',
                'compuesto' => 'Compuesto',
                'producto' => 'OE.Producto',
            ])
            ->orderBy(['Compuesto' => SORT_ASC])
            ->asArray()
            ->all();

        return ['data' => $data];
    }

    public function actionListarIndicadores(): array
    {
        [$idLlave, $gestion, $idGestion] = $this->obtenerContextoActivo();
        return $this->withTryCatch(fn() => $this->service->listarIndicadores(
            (string)Yii::$app->request->post('idObjEspecifico', ''),
            $idLlave,
            $gestion,
            $idGestion
        ));
    }

    public function actionListarProgramacion(): array
    {
        [$idLlave, , $idGestion] = $this->obtenerContextoActivo();
        return $this->withTryCatch(fn() => $this->service->listarProgramacion(
            (string)Yii::$app->request->post('idIndicadorPoa', ''),
            $idGestion,
            $idLlave
        ));
    }

    public function actionGuardarMeta(): array
    {
        return $this->withTryCatch(fn() => $this->service->guardarMeta(
            (string)Yii::$app->request->post('idProgramacionIndicadorPoaGestion', ''),
            (int)Yii::$app->request->post('trimestre', 0),
            max(0, (int)Yii::$app->request->post('meta', 0))
        ));
    }

    private function obtenerContextoActivo(): array
    {
        $contexto = Yii::$app->userContext->contexto();
        $idLlave = (string)($contexto->IdLlavePresupuestaria ?? '');
        $gestion = (int)($contexto->Gestion ?? 0);
        $idGestion = (string)($contexto->IdGestion ?? '');

        if ($idLlave === '' || $gestion <= 0 || $idGestion === '') {
            throw new ValidationException(
                Yii::$app->params['ERROR_ENVIO_DATOS'],
                'Debe seleccionar una gestión y una llave presupuestaria.',
                400
            );
        }

        return [$idLlave, $gestion, $idGestion];
    }
}
