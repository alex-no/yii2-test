#!/usr/bin/env php
<?php

use app\components\SwaggerLogger;
use OpenApi\Generator;
use yii\console\Application;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/config/console.php';
new Application($config);

$logger = new SwaggerLogger();
$scanPath = Yii::getAlias('@app/api/modules/v1/controllers');

$logger->info("Starting OpenAPI scan...");
$logger->info("Scanning path: {$scanPath}\n");

$openapi = Generator::scan([$scanPath], ['logger' => $logger]);

echo $openapi->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
