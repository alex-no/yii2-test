<?php

return [
    // vue routes
    [
        'pattern' => 'html/<path:[\w\-\/]+>',
        'route' => 'site/vue',
    ],
    'html' => 'site/vue',

    // api routes
    '' => 'site/index',
    '<controller:\w+>/<id:\d+>' => '<controller>/view',
    '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
    '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
];
