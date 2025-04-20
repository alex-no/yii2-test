<?php

return [
    'adminEmail' => 'alex@4n.com.ua',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Alex mailer',
    'JwtSecret' => 'MySuperSecretKey',
    'hiddenFields' => [
        \app\models\User::class => [
            'password',
            'auth_key',
            'remember_token',
        ],
        // \app\models\Admin::class => [
        //     'password_hash',
        // ],
    ],
];
