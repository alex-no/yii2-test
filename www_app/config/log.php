<?php

return [
    'traceLevel' => YII_DEBUG ? 3 : 0,
    'flushInterval' => 1,
    'targets' => [
        // Standard log
        [
            'class' => 'yii\log\FileTarget',
            'levels' => ['error', 'warning', 'info'],
            'logFile' => '@runtime/logs/app.log',
            'maxFileSize' => 1024 * 2, // 2MB
        ],
        // Log for Swagger
        [
            'class' => 'yii\log\FileTarget',
            'levels' => ['error', 'warning', 'info'],
            'categories' => ['swagger'],
            'logFile' => '@runtime/logs/swagger.log',
            'maxFileSize' => 1024 * 1, // 1MB
            'logVars' => [], // without $_SERVER and other variables
            'maxLogFiles' => 5,
        ],
    ],
];
