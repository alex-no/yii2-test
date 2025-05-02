<?php

return [
    '' => 'v1/site/index',

    'POST auth/register' => 'v1/auth/register',
    'POST auth/login' => 'v1/auth/login',
    'GET auth/confirm-email/<token:\w+>' => 'v1/auth/confirm-email',
    'POST user/logout' => 'v1/user/logout',

    //'POST user' => 'v1/user/create',
    'GET user/<id:\d+>' => 'v1/user/view',
    'PUT user/<id:\d+>' => 'v1/user/update',
    'GET user/profile' => 'v1/user/profile',

    'GET languages' => 'v1/language/index',
    'GET languages/<code:\w+>' => 'v1/language/view',

    [
        'class' => 'yii\rest\UrlRule',
        'controller' => [
            'pet-types' => 'v1/pet-type',
        ],
        'pluralize' => true,
        // 'patterns' => [
        //     'OPTIONS pet-types' => '',
        // ],
    ],

    'GET db-tables' => 'v1/site/db-tables',
    'GET test' => 'v1/site/test',

    'swagger/json' => 'v1/swagger/json',
    //'swagger/ui' => 'v1/swagger/ui',
];
