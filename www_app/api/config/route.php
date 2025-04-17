<?php

return [
    '' => 'v1/site/index',
    'GET db-tables' => 'v1/site/db-tables',

    'GET v1/user/<id:\d+>' => 'user/view',
    'POST v1/user' => 'user/create',
    
    'swagger/json' => 'v1/swagger/json',
    'swagger/ui' => 'v1/swagger/ui',
];