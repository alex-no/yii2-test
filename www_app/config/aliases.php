<?php

return [
    '@app' => dirname(__DIR__),
    '@web' => '@app/web',
    '@api' => '@app/api',
    '@webroot' => dirname(__DIR__) . '/web/public',
    '@apiroot' => dirname(__DIR__) . '/api/public',
    '@console' => dirname(__DIR__) . '/console',
    '@runtime' => dirname(__DIR__) . '/runtime',
    '@vendor' => dirname(__DIR__) . '/vendor',
    '@bower' => dirname(__DIR__) . '/vendor/bower-asset',
    '@npm' => dirname(__DIR__) . '/vendor/npm-asset',
];
