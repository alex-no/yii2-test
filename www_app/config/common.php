<?php
// This is the common configuration for both Web and Console applications
require __DIR__ . '/aliases.php';

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
    ],
];
