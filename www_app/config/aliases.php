<?php

$aliases = [
    '@app' => dirname(__DIR__),
    '@web' => '/',
    '@api' => '/api',
    '@console' => '/console',

    '@common' => '@app/common',

    '@webmain' => '@app/web/',
    '@apimain' => '@app/api/',
    '@webroot' => '@app/web/public',
    '@apiroot' => '@app/api/public',
    '@console' => '@app/console',
    '@runtime' => '@app/runtime',

    '@vendor' => '@app/vendor',
    '@bower' => '@vendor/bower-asset',
    '@npm'   => '@vendor/npm-asset',

    '@gii'   => '@app/app/gii',
];
foreach ($aliases as $name => $path) {
    Yii::setAlias($name, $path);
}

return $aliases;
