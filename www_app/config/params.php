<?php

return [
    'adminEmail' => 'admin@4n.com.ua',
    'senderEmail' => 'admin@4n.com.ua',
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
        // \app\models\PetType::class => [
        //     'localizedPrefixes' => '##',
        // ],
    ],

    'payment.default' => 'paypal',
    // List of available drivers for payment systems
    'payment.drivers' => [
        'paypal' => [
            'class' => \app\components\payment\drivers\PayPalDriver::class,
            'config' => [
                'clientId'    => 'your_paypal_business_email@example.com',
                'secret'      => 'your_paypal_secret_if_needed',
                'callbackUrl' => $_ENV['CURRENT_URL'] . '/api/payments/handle',
                'returnUrl'   => $_ENV['CURRENT_URL'] . '/html/payment-success',
                'cancelUrl'   => $_ENV['CURRENT_URL'] . '/html/payment-cancel',
            ],
        ],
        'liqpay' => [
            'class' => \app\components\payment\drivers\LiqPayDriver::class,
            'config' => [
                'publicKey'  => $_ENV['LIQPAY_PUBLIC_KEY'], // Set your public key here
                'privateKey' => $_ENV['LIQPAY_PRIVATE_KEY'], // Set your private key here
                'callbackUrl' => $_ENV['CURRENT_URL'] . '/api/payments/handle',
                'resultUrl' => $_ENV['CURRENT_URL'] . '/html/payment-result',
            ],
        ],
        // 'stripe' => [...],
    ],
];
