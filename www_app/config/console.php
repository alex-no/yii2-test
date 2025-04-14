<?php
$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';
$log = require __DIR__ . '/log.php';
require __DIR__ . '/aliases.php';

return yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/common.php',
    [
        'id' => 'console-app',
        'controllerNamespace' => 'console\controllers',
        'params' => $params,
        'basePath' => dirname(__DIR__),
        'bootstrap' => ['log'],
        'components' => [
            'db' => $db,
            'log' => $log,
        ],
    ]
);