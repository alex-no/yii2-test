<?php
$common = require __DIR__ . '/common.php';

return yii\helpers\ArrayHelper::merge(
    $common,
    [
        'id' => 'console-app',
        'controllerNamespace' => 'console\controllers',
        // 'components' => [
        // ],
    ]
);