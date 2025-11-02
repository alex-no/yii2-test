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
            // 'assetManager' => [
            //     'basePath' => '@webroot',
            //     'baseUrl' => '@web',
            // ],
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
            'view' => [
                'class' => 'yii\web\View',
                'defaultExtension' => 'twig',
                'renderers' => [
                    'twig' => [
                        'class' => \yii\twig\ViewRenderer::class,
                        'cachePath' => '@runtime/Twig/cache',
                        'options' => [
                            'auto_reload' => true,
                            'debug' => true,
                        ],
                        'extensions' => [
                            \Twig\Extension\DebugExtension::class,
                        ],
                        'globals' => [
                            'Yii' => \Yii::class,
                            'html' => \yii\helpers\Html::class,
                            'url' => \yii\helpers\Url::class,
                            'navBar' => ['class' => \yii\bootstrap5\NavBar::class],
                            'nav' => ['class' => \yii\bootstrap5\Nav::class],
                        ],
                        'functions' => [
                            'alias' => 'alias',
                        ],
                        'uses' => ['yii\bootstrap5'],
                    ],
                ],
            ],
            'errorHandler' => [
                'errorAction' => 'site/error',
            ],
        ],
        'layout' => 'main',
    ],
);

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'allowedIPs' => ['127.0.0.1', '::1', $_ENV['ALLOWED_IP']],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs' => ['127.0.0.1', '::1', $_ENV['ALLOWED_IP']],
        'generators' => [
            'model' => [
                'class' => \AlexNo\FieldLingoGii\ExtendedModel\ExtendedModelGenerator::class,
                'templates' => [
                    'extended' => '@vendor/alex-no/field-lingo-gii/templates/extended',
                ],
                'baseClassOptions' => [
                    'yii\db\ActiveRecord',
                    'AlexNo\FieldLingo\Adapters\Yii2\LingoActiveRecord',
                ],
                'queryBaseClassOptions' => [
                    'yii\db\ActiveQuery',
                    'AlexNo\FieldLingo\Adapters\Yii2\LingoActiveQuery',
                ],
            ],
            'route-viewer' => [
                'class' => 'app\gii\routeViewer\RouteViewerGenerator',
                'configData' => [
                    'contexts' => [
                        'web' => 'Web',
                        'api' => 'API',
                    ],
                    'defaultContext' => 'api',
                    'currentUrlManager' => 'web',
                ]
            ],
            'field-lingo-add-language' => [
                'class' => \AlexNo\FieldLingoGii\AddLanguageColumn\AddLanguageColumnGenerator::class,
                'templates' => [
                    'default' => '@vendor/alex-no/field-lingo-gii/src/AddLanguageColumn/templates/default/',
                ],
            ],
        ],
    ];
}

return $config;
