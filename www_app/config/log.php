<?php

return [
    'traceLevel' => YII_DEBUG ? 3 : 0,
    'targets' => [
        [
            'class' => 'yii\log\FileTarget',
            'levels' => ['error', 'warning'],
            'logFile' => '@runtime/logs/app.log',
            'maxFileSize' => 1024 * 2, // 2MB
        ],
    ],
    'flushInterval' => 1,
];
