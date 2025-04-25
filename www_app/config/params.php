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
        //     'password',
        // ],
    ],
    'advActive' => [
        // Common AdvActiveRecord config
        \app\components\i18n\AdvActiveRecord::class => [
            'localizedPrefixes' => '@@',
            'isStrict' => true,
        ],
        // Common AdvActiveQuery config
        \app\components\i18n\AdvActiveQuery::class => [
            'localizedPrefixes' => '@@',
        ],
        // Personal Model PetType config
        \app\models\PetType::class => [
            'localizedPrefixes' => '##',
        ],
    ],
];
