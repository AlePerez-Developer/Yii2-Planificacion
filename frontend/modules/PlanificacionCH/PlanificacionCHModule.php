<?php
namespace app\modules\PlanificacionCH;

use yii\filters\AccessControl;
use yii\base\Module;
use Yii;

class PlanificacionCHModule extends Module
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => [],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => [],
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->identity->esDirector || Yii::$app->user->identity->esDecano || Yii::$app->user->identity->esRector ;
                        }
                    ],
                ],
            ],
        ];
    }

    public function init()
    {
        parent::init();
    }
}



