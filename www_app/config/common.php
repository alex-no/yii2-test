<?php
return [
    'id' => 'yii2-test',
    'basePath' => dirname(__DIR__),
    'vendorPath' => dirname(__DIR__) . '/vendor',
    'aliases' => [
        '@common' => dirname(__DIR__) . '/common',
        '@web' => '@app/web',
        '@api' => '@app/api',
    ],
    'components' => [
        'db' => require __DIR__ . '/db.php',
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [[
                'class' => 'yii\log\FileTarget',
                'levels' => ['error', 'warning'],
            ]],
        ],
    ],
];
