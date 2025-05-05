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
        'bootstrap' => [
            'setLanguage',
        ],
        'components' => [
            'setLanguage' => [
                'class' => 'app\components\SetLanguageBootstrap',
                'isApi' => false,
            ],
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
                            'html' => \yii\helpers\Html::class,
                            'url' => \yii\helpers\Url::class,
                            'navBar' => ['class' => \yii\bootstrap5\NavBar::class],
                            'nav' => ['class' => \yii\bootstrap5\Nav::class],
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
                'baseClassOptions' => [
                    'yii\db\ActiveRecord',
                    'app\components\i18n\AdvActiveRecord',
                    //'app\models\MyCustomActiveRecord',
                ],
                'queryBaseClassOptions' => [
                    'yii\db\ActiveQuery',
                    'app\components\i18n\AdvActiveQuery',
                    //'app\models\MyCustomQuery',
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
            'addLanguageColumn' => [
                'class' => \app\gii\addLanguageColumn\AddLanguageColumnGenerator::class,
                // 'templates' => [
                //     'Default (SQL Generator)' => '@gii/addLanguageColumn/default/',
                // ],
            ],
        ],
    ];
}

return $config;
