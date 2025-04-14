<?php
$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';
$log = require __DIR__ . '/log.php';
require __DIR__ . '/aliases.php';

// This is the main Web application configuration for the console application.
return yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/common.php',
    [
        'id' => 'web-app',
        'params' => $params,
        //'basePath' => dirname(__DIR__),
        'controllerNamespace' => 'app\web\controllers',
        'viewPath' => '@webmain/views',
        'bootstrap' => ['log'],
        'components' => [
            'request' => [
                'cookieValidationKey' => 'secret-key-web',
            ],
            'db' => $db,
            'urlManager' => require Yii::getAlias('@webmain') . '/config/route.php',
            'user' => [
                'identityClass' => 'app\models\User',
                'enableAutoLogin' => true,
                'loginUrl' => ['site/login'],
            ],
            'errorHandler' => [
                'errorAction' => 'site/error',
            ],
            'log' => $log,
        ],
    ],
);
