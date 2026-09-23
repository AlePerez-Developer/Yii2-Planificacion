<?php

namespace app\modules\Planificacion\controllers;

use app\controllers\BaseController;
use app\modules\Planificacion\common\exceptions\ValidationException;
use app\modules\Planificacion\common\helpers\PoaEdicionHelper;
use app\modules\Planificacion\models\UnidadEjecutora;
use app\modules\Planificacion\services\ControlEnvioPoaService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class EnviarPoaController extends BaseController
{
    protected array $accionesSinValidacion = ['index', 'enviar'];

    public function __construct(
        $id,
        $module,
        private ControlEnvioPoaService $service,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'enviar' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        return $this->mostrar(false);
    }

    public function actionEnviar(): string
    {
        $contexto = $this->contextoONulo();
        if ($contexto === null) {
            return $this->mostrar(false, ['Debe seleccionar unidad, gestión y estado POA.']);
        }

        [$unidad, $gestion, $estadoPoa] = $contexto;
        try {
            $resultado = $this->service->enviar($unidad, $gestion, $estadoPoa);
        } catch (ValidationException $e) {
            $errores = $e->getErrors();
            $lista = is_array($errores) ? $errores : [$errores];
            $plana = [];
            array_walk_recursive($lista, static function ($valor) use (&$plana) {
                if (is_string($valor) && $valor !== '') {
                    $plana[] = $valor;
                }
            });
            return $this->mostrar(false, $plana !== [] ? $plana : [$e->getMessage()]);
        }

        return $this->mostrar($resultado['ok'], $resultado['restricciones']);
    }

    private function mostrar(bool $enviado, ?array $restricciones = null): string
    {
        $contexto = $this->contextoONulo();
        $unidadId = $contexto[0] ?? '';
        $edicion = PoaEdicionHelper::estado($unidadId ?: null);

        if ($restricciones === null) {
            if ($contexto === null) {
                $restricciones = ['Debe seleccionar unidad, gestión y estado POA.'];
            } elseif ($edicion['enviado']) {
                $restricciones = [];
            } else {
                $restricciones = $this->service->validar($contexto[0], $contexto[1], $contexto[2]);
            }
        }

        return $this->render('index', [
            'unidad' => $unidadId !== '' ? UnidadEjecutora::findOne($unidadId) : null,
            'edicion' => $edicion,
            'restricciones' => $restricciones,
            'enviado' => $enviado,
        ]);
    }

    private function contextoONulo(): ?array
    {
        $contexto = Yii::$app->userContext->contexto();
        $unidad = (string)($contexto?->IdUnidadEjecutora ?? '');
        $gestion = (string)($contexto?->IdGestion ?? '');
        $estadoPoa = (string)($contexto?->IdEstadoPoa ?? '');
        if ($unidad === '' || $gestion === '' || $estadoPoa === '') {
            return null;
        }
        return [$unidad, $gestion, $estadoPoa];
    }
}
