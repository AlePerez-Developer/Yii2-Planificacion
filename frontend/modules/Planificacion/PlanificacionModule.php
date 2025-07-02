<?php
namespace app\modules\Planificacion;

use app\modules\Planificacion\services\PeiService;
use yii\base\Module;
use Yii;

class PlanificacionModule extends Module
{
    public function init()
    {
        parent::init();

        Yii::$container->set(PeiService::class, PeiService::class);
    }
}



