<?php

namespace app\controllers;

use yii\web\Controller;
use yii\filters\AccessControl;

class BaseWebController extends Controller
{
    public function behaviors(): array
    {
        return [

            'access' => [

                'class' => \yii\filters\AccessControl::class,

                'except' => [
                    'portal-login',
                    'usuario-invalido',
                    'error'
                ],

                'rules' => [

                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],

                ],

            ],

        ];
    }
}