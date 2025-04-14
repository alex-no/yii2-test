<?php
$common = require __DIR__ . '/common.php';
$rules = require Yii::getAlias('@apimain') . '/config/route.php';

// This is the main API application configuration
return yii\helpers\ArrayHelper::merge(
    $common,
    [
        'id' => 'api-app',
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
                'rules' => $rules,
            ],
        ],
    ]
);
