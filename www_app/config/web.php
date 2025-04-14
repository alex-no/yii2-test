<?php
$common = require __DIR__ . '/common.php';
$rules = require Yii::getAlias('@webmain') . '/config/route.php';

// This is the main Web application configuration
return yii\helpers\ArrayHelper::merge(
    $common,
    [
        'id' => 'web-app',
        'controllerNamespace' => 'app\web\controllers',
        'viewPath' => '@webmain/views',
        'components' => [
            'request' => [
                'cookieValidationKey' => 'secret-key-web',
            ],
            'urlManager' => [
                'class' => yii\web\UrlManager::class,
                'enableStrictParsing' => true,
                'enablePrettyUrl' => true,
                'showScriptName' => false,
                'rules' => $rules,
            ],
            'user' => [
                'identityClass' => 'app\models\User',
                'enableAutoLogin' => true,
                'loginUrl' => ['site/login'],
            ],
            'errorHandler' => [
                'errorAction' => 'site/error',
            ],
        ],
    ],
);
