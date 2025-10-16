<?php

namespace common\components;

use Yii;
use yii\base\Component;
use yii\web\ForbiddenHttpException;

class ContextoAplicacion extends Component
{
    const CLAVE_PEI = 'peiActivo';

    public function __construct($config = [])
    {
        parent::__construct($config);

        if (!Yii::$app->session->has(self::CLAVE_PEI)) {
            Yii::$app->session->set(self::CLAVE_PEI, '');
        }
    }

    /**
     * Establece el codigo del Pei activo
     *
     * @param $id string
     */
    public function setPei(string $id): void
    {
        Yii::$app->session->set(self::CLAVE_PEI, $id);
    }

    /**
     * Regresa el codigo del Pei Activo
     *
     * @return string
     */
    public function getPei(): string
    {
        return Yii::$app->session->get(self::CLAVE_PEI, '');
    }

    /**
     * @throws ForbiddenHttpException
     */
    public function validarPeiActivo(): void
    {
        $pei = $this->getPei();
        if (empty($pei)) {
            throw new ForbiddenHttpException('No se ha definido la etapa actual. Seleccione una etapa antes de continuar.');
        }
    }



    /*public static function setEtapa($etapa)
    {
        Yii::$app->session->set('etapaActual', $etapa);
    }

    public static function getEtapa()
    {
        return Yii::$app->session->get('etapaActual');
    }

    public static function setVariable($clave, $valor)
    {
        Yii::$app->session->set($clave, $valor);
    }

    public static function getVariable($clave)
    {
        return Yii::$app->session->get($clave);
    }

    public static function clear()
    {
        Yii::$app->session->remove('etapaActual');
        // Puedes limpiar otras variables aquí
    }*/
}
