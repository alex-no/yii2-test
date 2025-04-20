<?php

return [
    '' => 'v1/site/index',

    'POST auth/register' => 'v1/auth/register',
    'POST auth/login' => 'v1/auth/login',
    'POST user/logout' => 'v1/user/logout',

    'POST user' => 'user/create',
    'GET user/<id:\d+>' => 'user/view',
    'PUT user/<id:\d+>' => 'user/update',
    'GET user/profile' => 'v1/user/profile',

    'GET languages' => 'v1/language/index',
    'GET languages/<id:\d+>' => 'v1/language/view',

    'GET db-tables' => 'v1/site/db-tables',

    'swagger/json' => 'v1/swagger/json',
    'swagger/ui' => 'v1/swagger/ui',
];
