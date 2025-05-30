<?php

return [
    // vue routes
    'html/<path:.+>' => 'html/index',
    'html' => 'html/index',

    // api routes
    '' => 'site/index',
    '<controller:\w+>/<id:\d+>' => '<controller>/view',
    '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
    '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
];
