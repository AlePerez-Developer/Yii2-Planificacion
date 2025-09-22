<?php
return [
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'contexto' => [
            'class' => 'common\components\ContextoAplicacion',
        ],
        'cache' => [
            'class' => \yii\caching\FileCache::class,
        ],
    ],
];
