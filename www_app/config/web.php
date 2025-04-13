<?php
$aliases = require __DIR__ . '/aliases.php';

return yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/common.php',
    [
        'id' => 'web-app',
        'aliases' => $aliases,
        'components' => [
            'request' => [
                'cookieValidationKey' => 'secret-key-web',
            ],
        ],
    ]
);
