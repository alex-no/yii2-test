<?php
$alias = require __DIR__ . '/aliases.php';
$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

return yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/common.php',
    [
        'id' => 'console-app',
        'controllerNamespace' => 'console\controllers',
        'basePath' => dirname(__DIR__),
        'bootstrap' => ['log'],
        'components' => [
            'log' => [
                'targets' => [
                    [
                        'class' => 'yii\log\FileTarget',
                        'levels' => ['error', 'warning'],
                    ],
                ],
            ],
            'db' => $db,
        ],
        'params' => $params,
    ]
);