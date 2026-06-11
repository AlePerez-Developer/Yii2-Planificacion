<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-frontend',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'frontend\controllers',
    'modules' => [
        'Planificacion' => [
            'class' => 'app\modules\Planificacion\PlanificacionModule',
        ],
        'PlanificacionCH' => [
            'class' => 'app\modules\PlanificacionCH\PlanificacionCHModule',
        ],
    ],
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-frontend',
        ],
        'user' => [
            'identityClass' => 'common\models\seguridad\Usuario',
            'enableAutoLogin' => false,
            //'authTimeout' => 1800,
            'loginUrl' => ['site/usuario-invalido'],
            'identityCookie' => ['name' => '_identity-frontend', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the frontend
            'name' => 'advanced-frontend',
            //'timeout' => 1800,
        ],
        'userContext' => [
            'class' => 'common\components\UserContext'
        ],
        'assetManager' => [
            'linkAssets' => true,
            'bundles' => [
                'yii\bootstrap4\BootstrapPluginAsset' => [
                    'js'=>[]
                ],
                'yii\bootstrap4\BootstrapAsset' => [
                    'css' => [],
                ],
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        /*
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
        */
    ],
    'params' => $params,
];
