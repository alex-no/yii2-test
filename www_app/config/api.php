<?php
$aliases = require __DIR__ . '/aliases.php';

return yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/common.php',
    [
        'id' => 'api-app',
        'aliases' => $aliases,
        'components' => [
            'request' => [
                'parsers' => [
                    'application/json' => 'yii\web\JsonParser',
                ],
                'cookieValidationKey' => 'secret-key-api',
            ],
            'response' => [
                'format' => yii\web\Response::FORMAT_JSON,
            ],
            'urlManager' => [
                'enablePrettyUrl' => true,
                'enableStrictParsing' => true,
                'showScriptName' => false,
                'rules' => [
                    'GET v1/user/<id:\d+>' => 'user/view',
                    'POST v1/user' => 'user/create',
                    // и т.д.
                ],
            ],
        ],
    ]
);
