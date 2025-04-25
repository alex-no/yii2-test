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
    'bootstrap' => ['log'],
    'components' => [
        'db' => $db,
        'log' => $log,
        'languageSelector' => [
            'class' => 'app\components\LanguageSelector',
            'paramName' => 'lang',
            'userAttribute' => 'language_code',
            'default' => 'en',
            // DB structure
            'tableName' => 'language',
            'codeField' => 'code',
            'enabledField' => 'is_enabled',
            'orderField' => 'order',
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
