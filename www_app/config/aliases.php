<?php

$aliases = [
    '@app' => dirname(__DIR__),
    '@web' => '/',

    '@webmain' => '@app/web/',
    '@apimain' => '@app/api/',
    '@webroot' => '@app/web/public',
    '@apiroot' => '@app/api/public',
    '@console' => '@app/console',
    '@runtime' => '@app/runtime',

    '@vendor' => '@app/vendor',
    '@bower' => '@vendor/bower-asset',
    '@npm'   => '@vendor/npm-asset',
];
foreach ($aliases as $name => $path) {
    Yii::setAlias($name, $path);
}

return $aliases;