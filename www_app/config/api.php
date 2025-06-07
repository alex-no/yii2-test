<?php
$common = require __DIR__ . '/common.php';
$rules = require Yii::getAlias('@apimain') . '/config/route.php';

// This is the main API application configuration
return yii\helpers\ArrayHelper::merge(
    $common,
    [
        'id' => 'api-app',
        'modules' => [
            'v1' => [
                'class' => app\api\modules\v1\Module::class,
            ],
        ],
        'bootstrap' => [
            'setLanguage',
        ],
        'components' => [
            'setLanguage' => [
                'class' => 'app\components\SetLanguageBootstrap',
                'isApi' => true,
            ],
            'request' => [
                'parsers' => [
                    'application/json' => 'yii\web\JsonParser',
                ],
                'cookieValidationKey' => 'secret-key-api',
            ],
            'response' => [
                'format' => yii\web\Response::FORMAT_JSON,
                'charset' => 'UTF-8',
                'formatters' => [
                    yii\web\Response::FORMAT_JSON => [
                        'class' => yii\web\JsonResponseFormatter::class,
                        'prettyPrint' => YII_DEBUG, // true in debug mode
                        'encodeOptions' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
                    ],
                ],
                // Force JSON regardless of Accept header
                'on beforeSend' => function ($event) {
                    $response = $event->sender;
                    $response->format = yii\web\Response::FORMAT_JSON;
                },
            ],
            'urlManager' => [
                'enablePrettyUrl' => true,
                'enableStrictParsing' => true,
                'showScriptName' => false,
                'rules' => $rules,
            ],
            'user' => [
                'identityClass' => 'app\models\User',
                'enableAutoLogin' => false,
                'enableSession' => false,
                'loginUrl' => null,
            ],
            'mailer' => [
                'class' => \app\components\SymfonyMailer::class,
                'dsn' => $_ENV['MAILER_PROTOCOL'] . '://' . ($_ENV['MAILER_PROTOCOL'] == 'stream' ? Yii::getAlias($_ENV['MAILER_DSN']) : $_ENV['MAILER_DSN']),
                'from' => $_ENV['MAILER_USER'],
            ],
            'payment' => [
                'class' => \app\components\payment\PaymentManager::class,
            ],
        ],
    ],
    is_file(__DIR__ . '/api-secure.php') ? require __DIR__ . '/api-secure.php' : []
);
