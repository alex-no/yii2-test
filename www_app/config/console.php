<?php

return yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/common.php',
    [
        'id' => 'console-app',
        'controllerNamespace' => 'console\controllers',
        'components' => [
            // можно подключить лог, базу и т.д.
        ],
    ]
);