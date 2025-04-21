<?php
$common = require __DIR__ . '/common.php';
$rules = require Yii::getAlias('@webmain') . '/config/route.php';

// This is the main Web application configuration
$config = yii\helpers\ArrayHelper::merge(
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

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'allowedIPs' => ['127.0.0.1', '::1', '172.20.0.1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs' => ['127.0.0.1', '::1', '172.20.0.1'],
        'generators' => [
            'model' => [
                'class' => 'app\gii\extendedModel\ExtendedModelGenerator', // path to the custom generator
                'templates' => [
                    'Extended Model' => '@app/app/gii/extendedModel/templates/extended', // path to the template
                ],
            ],
            'route-viewer' => [
                'class' => 'app\gii\routeViewer\RouteViewerGenerator',
            ],
        ],
    ];
}

return $config;
