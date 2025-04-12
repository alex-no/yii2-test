<?php
return yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/common.php',
    [
        'id' => 'web-app',
        'components' => [
            'request' => [
                'cookieValidationKey' => 'secret-key-web',
            ],
        ],
    ]
);
