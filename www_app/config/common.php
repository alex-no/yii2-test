<?php
// This is the common configuration for both Web and Console applications
require __DIR__ . '/aliases.php';

if (!function_exists('app')) {
    require Yii::getAlias('@common') . '/helpers.php';;
}

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';
$log = require __DIR__ . '/log.php';

return [
    'id' => 'yii2-test',
    'name' => 'Yii2 Test Application',
    'language' => 'uk',
    'version' => '1.0.0',
    'timeZone' => 'Europe/Kiev',
    'basePath' => dirname(__DIR__),
    'vendorPath' => dirname(__DIR__) . '/vendor',
    'params' => $params,
    'bootstrap' => [
        'log',
        'languageBootstrap',
    ],
    'components' => [
        'db' => $db,
        'log' => $log,
        'languageBootstrap' => [
            'class' => \LanguageDetector\Infrastructure\Adapters\Yii2\Bootstrap::class,
            'paramName' => 'lang',
            'default' => 'en',
            'userAttribute' => 'language_code',
            'tableName' => 'language',
            'codeField' => 'code',
            'enabledField' => 'is_enabled',
        ],
        'authManager' => [
            'class' => \yii\rbac\DbManager::class,
        ],
        'serverClock' => [
            'class' => \ServerTimeClock\Yii\ServerClockComponent::class,
            'client' => 'WorldTimeApi',
            'credentials' => [
                'IpGeoLocation' => '71fba5dbb71e4e87a94cea31783d9f2a',
            ],
            'enableCache' => true,
            'cacheTtl' => 300,
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'i18n' => [
            'translations' => [
                'app*' => [
                    'class' => yii\i18n\PhpMessageSource::class,
                    'basePath' => '@app/messages',
                    'sourceLanguage' => 'en',
                    'fileMap' => [
                        'app' => 'app.php',
                    ],
                ],
            ],
        ],
    ],
];
